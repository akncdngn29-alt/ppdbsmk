<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\VoucherPendaftaran;
use App\Models\BerkasPendaftar;
use App\Models\Pembayaran;
use App\Models\NilaiSeleksi;
use App\Models\Pengumuman;
use App\Models\PengaturanSekolah;
use App\Models\LogAktivitas;
use App\Services\XlsxExportService;
use Carbon\Carbon;

class AdminTUController extends Controller
{
    public function index()
    {
        $totalPendaftar = Pendaftar::count();
        $pendaftarHariIni = Pendaftar::whereDate('tgl_daftar', Carbon::today())->count();
        $totalLunas = Pendaftar::where('status_pembayaran', 'Paid')->count();
        $menungguVerifikasi = Pendaftar::where('status_verifikasi', 'Menunggu')->count();
        $totalDiterima = Pendaftar::where('status_kelulusan', 'Diterima')->count();

        $jurusan = Jurusan::withCount('pendaftarPilihan1')->get();
        $recentPendaftar = Pendaftar::with(['jurusan1', 'pembayaran'])
            ->orderBy('id_pendaftar', 'desc')
            ->limit(8)
            ->get();

        $pengaturan = PengaturanSekolah::getPengaturan();

        return view('admin.dashboard', compact(
            'totalPendaftar',
            'pendaftarHariIni',
            'totalLunas',
            'menungguVerifikasi',
            'totalDiterima',
            'jurusan',
            'recentPendaftar',
            'pengaturan'
        ));
    }

    public function pendaftar(Request $request)
    {
        $query = Pendaftar::with(['jurusan1', 'jurusan2', 'pembayaran', 'terima']);

        if ($request->filled('jurusan')) {
            $query->where('id_jurusan_1', $request->jurusan);
        }
        if ($request->filled('status_bayar')) {
            $query->where('status_pembayaran', $request->status_bayar);
        }
        if ($request->filled('status_verifikasi')) {
            $query->where('status_verifikasi', $request->status_verifikasi);
        }
        if ($request->filled('status_kelulusan')) {
            $query->where('status_kelulusan', $request->status_kelulusan);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'LIKE', "%{$q}%")
                    ->orWhere('nisn', 'LIKE', "%{$q}%")
                    ->orWhere('no_pendaftaran', 'LIKE', "%{$q}%")
                    ->orWhere('asal_sekolah', 'LIKE', "%{$q}%");
            });
        }

        $pendaftar = $query->orderBy('id_pendaftar', 'desc')->paginate(15)->withQueryString();
        $jurusan = Jurusan::all();

        return view('admin.pendaftar.index', compact('pendaftar', 'jurusan'));
    }

    public function detailPendaftar($id)
    {
        $pendaftar = Pendaftar::with([
            'jurusan1',
            'jurusan2',
            'berkas',
            'pembayaran.voucher',
            'prestasi',
            'nilaiSeleksi',
            'terima',
            'user'
        ])->findOrFail($id);

        $jurusan = Jurusan::all();

        return view('admin.pendaftar.detail', compact('pendaftar', 'jurusan'));
    }

    public function verifikasiBerkas(Request $request, $id)
    {
        $request->validate([
            'status_verifikasi' => 'nullable|in:Terverifikasi,Ditolak,Menunggu',
            'catatan_verifikasi' => 'nullable|string',
            'berkas' => 'nullable|array',
            'berkas.*.status_berkas' => 'nullable|in:Valid,Terverifikasi,Tidak Valid,Perlu Perbaikan,Menunggu',
            'berkas.*.catatan' => 'nullable|string',
        ]);

        $pendaftar = Pendaftar::findOrFail($id);
        $oldStatus = $pendaftar->status_verifikasi;

        // 1. Simpan status berkas masing-masing dokumen jika dikirim dari form
        $hasRevisi = false;
        $revisiList = [];

        if ($request->has('berkas') && is_array($request->berkas)) {
            foreach ($request->berkas as $idBerkas => $bData) {
                $item = BerkasPendaftar::where('id_berkas', $idBerkas)
                    ->where('id_pendaftar', $pendaftar->id_pendaftar)
                    ->first();
                if ($item) {
                    $statusInput = $bData['status_berkas'] ?? $item->status_berkas;
                    if ($statusInput === 'Terverifikasi') {
                        $statusInput = 'Valid';
                    }

                    $dataUpdate = ['status_berkas' => $statusInput];
                    if (isset($bData['catatan'])) {
                        $catatanItem = trim($bData['catatan']);
                        $dataUpdate['catatan'] = $catatanItem ?: null;
                    }

                    $item->update($dataUpdate);

                    if (in_array($statusInput, ['Tidak Valid', 'Perlu Perbaikan'])) {
                        $hasRevisi = true;
                        $revisiList[] = strtoupper($item->jenis_berkas);
                    }
                }
            }
        }

        // 2. Tentukan status verifikasi akhir pendaftar otomatis
        $pendaftar->load('berkas');

        if ($hasRevisi) {
            $statusFinal = 'Ditolak';
        } else {
            // Cek apakah 4 dokumen wajib sudah lengkap dan valid
            $wajibKeys = ['ijazah', 'pasfoto', 'kk', 'akta'];
            $allWajibValid = true;
            foreach ($wajibKeys as $wk) {
                $bWajib = $pendaftar->berkas->where('jenis_berkas', $wk)->first();
                if (!$bWajib || !in_array($bWajib->status_berkas, ['Valid', 'Terverifikasi'])) {
                    $allWajibValid = false;
                    break;
                }
            }
            $statusFinal = $allWajibValid ? 'Terverifikasi' : 'Menunggu';
        }

        // Jika request eksplisit mengirim status_verifikasi (backward compatibility / API)
        if ($request->filled('status_verifikasi')) {
            $statusFinal = $hasRevisi ? 'Ditolak' : $request->status_verifikasi;
        }

        $catatanFinal = $request->catatan_verifikasi;
        if ($statusFinal === 'Ditolak' && empty($catatanFinal) && !empty($revisiList)) {
            $catatanFinal = 'Terdapat dokumen yang perlu diperbaiki: ' . implode(', ', $revisiList) . '. Silakan unggah kembali dokumen perbaikan.';
        }

        $pendaftar->update([
            'status_verifikasi' => $statusFinal,
            'catatan_verifikasi' => $catatanFinal,
        ]);

        $admin = auth('admin')->user();
        $namaAdmin = $admin ? $admin->nama_admin : 'Petugas TU';

        LogAktivitas::log(
            'VERIFIKASI',
            "{$namaAdmin} memperbarui status verifikasi berkas {$pendaftar->nama} menjadi {$statusFinal}",
            'pendaftar',
            $pendaftar->id_pendaftar,
            ['status' => $oldStatus],
            ['status' => $statusFinal, 'catatan' => $catatanFinal]
        );

        return back()->with('success', 'Status verifikasi berkas pendaftar berhasil diperbarui.');
    }

    /**
     * Membuka kunci formulir biodata pendaftar oleh Admin TU (SPEC_PPDB.md Bab 426).
     * Fitur ini mengembalikan status formulir menjadi terbuka (is_locked = false)
     * sehingga calon siswa dapat kembali mengedit data biodata.
     * Tidak mengubah status pembayaran, seleksi, kelulusan, atau relasi lainnya.
     */
    public function unlockFormulir(Request $request, $id)
    {
        // 1. Otorisasi ketat: Pastikan user admin terautentikasi dan memiliki role 'tu'
        $admin = auth('admin')->user();
        if (!$admin || $admin->role !== 'tu') {
            abort(403, 'Akses ditolak. Hanya Admin Tata Usaha (TU) yang berwenang membuka kunci formulir.');
        }

        // 2. Temukan record pendaftar
        $pendaftar = Pendaftar::findOrFail($id);

        // 3. Validasi status kunci saat ini
        if (!$pendaftar->is_locked) {
            return back()->with('info', "Formulir biodata calon siswa {$pendaftar->nama} sudah dalam status terbuka (tidak terkunci).");
        }

        // 4. Update HANYA is_locked menjadi false
        $pendaftar->update([
            'is_locked' => false,
        ]);

        // 5. Catat ke audit trail log_aktivitas
        LogAktivitas::log(
            'UNLOCK',
            "Admin TU ({$admin->nama_admin}) membuka kunci formulir biodata untuk calon siswa {$pendaftar->nama} (No. Reg: {$pendaftar->no_pendaftaran})",
            'pendaftar',
            $pendaftar->id_pendaftar,
            ['is_locked' => true],
            ['is_locked' => false]
        );

        // 6. Kembalikan notifikasi sukses
        return back()->with('success', "Kunci formulir calon siswa {$pendaftar->nama} berhasil dibuka. Siswa kini dapat kembali mengedit formulir biodatanya.");
    }

    public function verifikasiSingleBerkas(Request $request, $idBerkas)
    {
        $request->validate([
            'status_berkas' => 'required|in:Valid,Terverifikasi,Tidak Valid,Perlu Perbaikan,Menunggu',
            'catatan' => 'nullable|string',
        ]);

        $berkas = BerkasPendaftar::with('pendaftar')->findOrFail($idBerkas);
        $oldStatus = $berkas->status_berkas;
        $oldCatatan = $berkas->catatan;

        $inputStatus = $request->status_berkas;
        if ($inputStatus === 'Terverifikasi') {
            $inputStatus = 'Valid';
        }

        $berkas->update([
            'status_berkas' => $inputStatus,
            'catatan' => $request->catatan,
        ]);

        $admin = auth('admin')->user();
        $namaAdmin = $admin ? $admin->nama_admin : 'Petugas TU';
        $namaSiswa = $berkas->pendaftar ? $berkas->pendaftar->nama : "Pendaftar #{$berkas->id_pendaftar}";
        $jenisBerkas = strtoupper($berkas->jenis_berkas);

        LogAktivitas::log(
            'VERIFIKASI_BERKAS',
            "{$namaAdmin} memverifikasi berkas {$jenisBerkas} milik {$namaSiswa}: {$request->status_berkas}",
            'berkas_pendaftar',
            $berkas->id_berkas,
            ['status_berkas' => $oldStatus, 'catatan' => $oldCatatan],
            ['status_berkas' => $request->status_berkas, 'catatan' => $request->catatan]
        );

        return back()->with('success', 'Status item berkas berhasil diperbarui.');
    }

    public function konfirmasiBayarTunai($idPendaftar)
    {
        $pendaftar = Pendaftar::with('pembayaran.voucher')->findOrFail($idPendaftar);
        $gelombang = $pendaftar->gelombang ?: Gelombang::first();

        DB::transaction(function () use ($pendaftar, $gelombang) {
            $existing = $pendaftar->pembayaran;
            $nominal = $gelombang ? (float)$gelombang->biaya_pendaftaran : 100000.00;
            $potongan = $existing ? (float)$existing->potongan : 0.00;
            $voucherId = $existing ? $existing->id_voucher : null;
            $totalBayar = max(0.00, $nominal - $potongan);
            $kodeTransaksi = $existing && $existing->kode_transaksi ? $existing->kode_transaksi : Pembayaran::generateKodeTransaksi();

            $pembayaran = Pembayaran::updateOrCreate(
                ['id_pendaftar' => $pendaftar->id_pendaftar],
                [
                    'id_gelombang' => $gelombang ? $gelombang->id_gelombang : 1,
                    'id_voucher' => $voucherId,
                    'kode_transaksi' => $kodeTransaksi,
                    'nominal' => $nominal,
                    'potongan' => $potongan,
                    'total_bayar' => $totalBayar,
                    'tipe_bayar' => 'tunai_tu',
                    'metode_pembayaran' => 'Loket TU (Tunai Kasir)',
                    'status_transaksi' => 'settlement',
                    'waktu_transaksi' => Carbon::now(),
                    'catatan' => 'Diverifikasi langsung oleh Petugas TU di loket sekolah' . ($existing && $existing->voucher ? " (Voucher: {$existing->voucher->kode_voucher})" : '') . '.',
                ]
            );

            $pendaftar->update(['status_pembayaran' => 'Paid']);
            LogAktivitas::log('UPDATE', "Admin TU memverifikasi lunas pembayaran tunai untuk {$pendaftar->nama} (Total: Rp " . number_format($totalBayar, 0, ',', '.') . ")", 'pembayaran', $pembayaran->id_pembayaran);
        });

        return back()->with('success', "Pembayaran tunai {$pendaftar->nama} berhasil diverifikasi Lunas!");
    }

    public function seleksiPenilaian()
    {
        $pendaftar = Pendaftar::with(['jurusan1', 'nilaiSeleksi', 'prestasi'])
            ->where('status_pembayaran', 'Paid')
            ->orderBy('id_pendaftar', 'desc')
            ->paginate(15);

        return view('admin.seleksi.index', compact('pendaftar'));
    }

    public function simpanPenilaian(Request $request, $id)
    {
        $request->validate([
            'nilai_rapor' => 'required|numeric|min:0|max:100',
            'nilai_wawancara' => 'required|numeric|min:0|max:100',
            'tes_buta_warna' => 'required|in:Normal,Parsial,Total',
            'catatan_tes' => 'nullable|string',
            'rekomendasi_jurusan' => 'nullable|string',
        ]);

        $pendaftar = Pendaftar::findOrFail($id);
        $totalNilai = ($request->nilai_rapor * 0.5) + ($request->nilai_wawancara * 0.5);

        NilaiSeleksi::updateOrCreate(
            ['id_pendaftar' => $pendaftar->id_pendaftar],
            [
                'nilai_rapor' => $request->nilai_rapor,
                'nilai_wawancara' => $request->nilai_wawancara,
                'tes_buta_warna' => $request->tes_buta_warna,
                'total_nilai' => $totalNilai,
                'catatan_tes' => $request->catatan_tes,
                'rekomendasi_jurusan' => $request->rekomendasi_jurusan,
            ]
        );

        LogAktivitas::log('UPDATE', "Admin menginput nilai seleksi untuk {$pendaftar->nama}: Skor {$totalNilai}", 'nilai_seleksi', $pendaftar->id_pendaftar);

        return back()->with('success', "Nilai tes seleksi untuk {$pendaftar->nama} berhasil disimpan.");
    }

    public function alihkanJurusan(Request $request, $id)
    {
        $pendaftar = Pendaftar::findOrFail($id);
        if (!$pendaftar->id_jurusan_2) {
            return back()->with('error', 'Siswa tidak memiliki pilihan jurusan kedua.');
        }

        $oldJurusan1 = $pendaftar->id_jurusan_1;
        $newJurusan = $pendaftar->id_jurusan_2;

        $pendaftar->update([
            'id_jurusan_1' => $newJurusan,
            'id_jurusan_2' => $oldJurusan1,
        ]);

        LogAktivitas::log('UPDATE', "Limpahan kuota: {$pendaftar->nama} dialihkan ke jurusan pilihan ke-2", 'pendaftar', $pendaftar->id_pendaftar);

        return back()->with('success', "Calon siswa {$pendaftar->nama} berhasil dialihkan ke Jurusan Pilihan 2.");
    }

    public function masterJurusan()
    {
        $jurusan = Jurusan::withCount(['pendaftarPilihan1', 'pendaftarPilihan2', 'siswaDiterima'])->get();
        return view('admin.master.jurusan', compact('jurusan'));
    }

    public function simpanJurusan(Request $request)
    {
        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:20|alpha_dash|unique:jurusan,kode_jurusan',
            'nama_jurusan' => 'required|string|max:100',
            'kuota' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'prospek_kerja' => 'nullable|string',
            'status_aktif' => 'nullable|boolean',
        ], [
            'kode_jurusan.required' => 'Kode jurusan wajib diisi.',
            'kode_jurusan.unique' => 'Kode jurusan sudah terdaftar. Silakan gunakan kode unik lain.',
            'kode_jurusan.alpha_dash' => 'Kode jurusan hanya boleh berisi huruf, angka, garis bawah, dan strip.',
            'nama_jurusan.required' => 'Nama program keahlian wajib diisi.',
            'kuota.required' => 'Kapasitas kuota siswa wajib ditentukan.',
            'kuota.integer' => 'Kapasitas kuota harus berupa angka.',
            'kuota.min' => 'Kapasitas kuota tidak boleh bernilai negatif.',
        ]);

        $validated['kode_jurusan'] = strtoupper(trim($validated['kode_jurusan']));
        $validated['status_aktif'] = $request->has('status_aktif') ? (bool) $request->status_aktif : true;

        $j = Jurusan::create($validated);

        LogAktivitas::log('CREATE', "Petugas TU menambahkan program keahlian baru: {$j->nama_jurusan} ({$j->kode_jurusan}) dengan kuota {$j->kuota}", 'jurusan', $j->id_jurusan);

        return redirect()->route('admin.master.jurusan')->with('success', "Program keahlian '{$j->nama_jurusan}' ({$j->kode_jurusan}) berhasil ditambahkan.");
    }

    public function detailJurusan($id)
    {
        $jurusan = Jurusan::withCount(['pendaftarPilihan1', 'pendaftarPilihan2', 'siswaDiterima'])->findOrFail($id);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id_jurusan' => $jurusan->id_jurusan,
                    'kode_jurusan' => $jurusan->kode_jurusan,
                    'nama_jurusan' => $jurusan->nama_jurusan,
                    'kuota' => $jurusan->kuota,
                    'pendaftar_count' => $jurusan->pendaftar_pilihan1_count,
                    'pendaftar_pilihan2_count' => $jurusan->pendaftar_pilihan2_count,
                    'siswa_diterima_count' => $jurusan->siswa_diterima_count,
                    'sisa_kuota' => $jurusan->sisa_kuota,
                    'deskripsi' => $jurusan->deskripsi,
                    'prospek_kerja' => $jurusan->prospek_kerja,
                    'status_aktif' => (bool)$jurusan->status_aktif,
                    'status_label' => $jurusan->status_label,
                    'has_pendaftar' => $jurusan->pendaftar_pilihan1_count > 0 || $jurusan->pendaftar_pilihan2_count > 0 || $jurusan->siswa_diterima_count > 0,
                ],
            ]);
        }

        return redirect()->route('admin.master.jurusan');
    }

    public function updateJurusan(Request $request, $id)
    {
        $j = Jurusan::findOrFail($id);

        $validated = $request->validate([
            'kode_jurusan' => 'required|string|max:20|alpha_dash|unique:jurusan,kode_jurusan,' . $id . ',id_jurusan',
            'nama_jurusan' => 'required|string|max:100',
            'kuota' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'prospek_kerja' => 'nullable|string',
            'status_aktif' => 'nullable|boolean',
        ], [
            'kode_jurusan.required' => 'Kode jurusan wajib diisi.',
            'kode_jurusan.unique' => 'Kode jurusan sudah terdaftar. Silakan gunakan kode unik lain.',
            'kode_jurusan.alpha_dash' => 'Kode jurusan hanya boleh berisi huruf, angka, garis bawah, dan strip.',
            'nama_jurusan.required' => 'Nama program keahlian wajib diisi.',
            'kuota.required' => 'Kapasitas kuota siswa wajib ditentukan.',
            'kuota.integer' => 'Kapasitas kuota harus berupa angka.',
            'kuota.min' => 'Kapasitas kuota tidak boleh bernilai negatif.',
        ]);

        $validated['kode_jurusan'] = strtoupper(trim($validated['kode_jurusan']));
        $validated['status_aktif'] = $request->has('status_aktif') ? (bool) $request->status_aktif : false;

        $oldKuota = $j->kuota;
        $j->update($validated);

        $kuotaNote = ($oldKuota != $j->kuota) ? " (kuota diubah dari {$oldKuota} menjadi {$j->kuota})" : "";
        LogAktivitas::log('UPDATE', "Petugas TU memperbarui data program keahlian: {$j->nama_jurusan} ({$j->kode_jurusan}){$kuotaNote}", 'jurusan', $j->id_jurusan);

        return redirect()->route('admin.master.jurusan')->with('success', "Program keahlian '{$j->nama_jurusan}' ({$j->kode_jurusan}) berhasil diperbarui.");
    }

    public function ubahKuota(Request $request, $id)
    {
        $request->validate([
            'kuota' => 'required|integer|min:0',
        ], [
            'kuota.required' => 'Kapasitas kuota siswa wajib ditentukan.',
            'kuota.integer' => 'Kapasitas kuota harus berupa angka.',
            'kuota.min' => 'Kapasitas kuota tidak boleh bernilai negatif.',
        ]);

        $j = Jurusan::findOrFail($id);
        $oldKuota = $j->kuota;
        $j->kuota = $request->kuota;
        $j->save();

        LogAktivitas::log('UPDATE', "Petugas TU mengubah kuota jurusan {$j->nama_jurusan} dari {$oldKuota} menjadi {$j->kuota}", 'jurusan', $j->id_jurusan);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'kuota' => $j->kuota,
                'sisa_kuota' => $j->sisa_kuota,
                'message' => "Kuota {$j->nama_jurusan} berhasil diubah menjadi {$j->kuota}.",
            ]);
        }

        return redirect()->route('admin.master.jurusan')->with('success', "Kuota jurusan {$j->nama_jurusan} berhasil diubah menjadi {$j->kuota}.");
    }

    public function toggleStatusJurusan($id)
    {
        $j = Jurusan::findOrFail($id);
        $j->status_aktif = !$j->status_aktif;
        $j->save();

        $statusText = $j->status_aktif ? 'diaktifkan' : 'dinonaktifkan';
        LogAktivitas::log('UPDATE', "Petugas TU mengubah status program keahlian {$j->nama_jurusan} ({$j->kode_jurusan}) menjadi {$statusText}", 'jurusan', $j->id_jurusan);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'status_aktif' => $j->status_aktif,
                'status_label' => $j->status_label,
                'message' => "Program keahlian '{$j->nama_jurusan}' berhasil {$statusText}.",
            ]);
        }

        return redirect()->route('admin.master.jurusan')->with('success', "Program keahlian '{$j->nama_jurusan}' berhasil {$statusText}.");
    }

    public function hapusJurusan($id)
    {
        $j = Jurusan::withCount(['pendaftarPilihan1', 'pendaftarPilihan2', 'siswaDiterima'])->findOrFail($id);

        if ($j->pendaftar_pilihan1_count > 0 || $j->pendaftar_pilihan2_count > 0 || $j->siswa_diterima_count > 0) {
            return redirect()->route('admin.master.jurusan')->with('error', "Jurusan '{$j->nama_jurusan}' ({$j->kode_jurusan}) tidak dapat dihapus permanen karena sudah memiliki data calon siswa pendaftar. Anda dapat menonaktifkan statusnya agar tidak menerima pendaftar baru.");
        }

        $nama = $j->nama_jurusan;
        $kode = $j->kode_jurusan;
        $j->delete();

        LogAktivitas::log('DELETE', "Petugas TU menghapus program keahlian: {$nama} ({$kode})", 'jurusan', $id);

        return redirect()->route('admin.master.jurusan')->with('success', "Program keahlian '{$nama}' ({$kode}) berhasil dihapus.");
    }

    public function masterGelombang()
    {
        if (auth('admin')->check() && auth('admin')->user()->role === 'kepala_sekolah') {
            return redirect()->route('admin.pengaturan', ['tab' => 'gelombang']);
        }
        $gelombang = Gelombang::withCount('pendaftar')->get();
        return view('admin.master.gelombang', compact('gelombang'));
    }

    public function simpanGelombang(Request $request)
    {
        $validated = $request->validate([
            'nama_gelombang' => 'required|string|max:100',
            'tahun_ajaran' => 'required|string|max:20',
            'tgl_buka' => 'required|date',
            'tgl_tutup' => 'required|date|after_or_equal:tgl_buka',
            'biaya_pendaftaran' => 'required|numeric|min:0',
            'status_aktif' => 'required|boolean',
        ]);

        Gelombang::create($validated);
        return redirect()->route('admin.pengaturan', ['tab' => 'gelombang'])->with('success', 'Gelombang pendaftaran baru berhasil ditambahkan.');
    }

    public function updateGelombang(Request $request, $id)
    {
        $gelombang = Gelombang::findOrFail($id);

        $validated = $request->validate([
            'nama_gelombang'   => 'required|string|max:100',
            'tahun_ajaran'     => 'required|string|max:20',
            'tgl_buka'         => 'required|date',
            'tgl_tutup'        => 'required|date|after_or_equal:tgl_buka',
            'biaya_pendaftaran'=> 'required|numeric|min:0',
            'status_aktif'     => 'required|boolean',
        ]);

        $gelombang->update($validated);
        LogAktivitas::log('UPDATE', 'Admin mengubah data gelombang: ' . $gelombang->nama_gelombang, 'gelombang', null, null, null, 'SUCCESS');
        return redirect()->route('admin.pengaturan', ['tab' => 'gelombang'])->with('success', 'Data gelombang "' . $gelombang->nama_gelombang . '" berhasil diperbarui.');
    }

    public function toggleGelombang($id)
    {
        $gelombang = Gelombang::findOrFail($id);
        $gelombang->update(['status_aktif' => !$gelombang->status_aktif]);
        $status = $gelombang->status_aktif ? 'AKTIF' : 'NONAKTIF';
        LogAktivitas::log('UPDATE', "Admin mengubah status gelombang '{$gelombang->nama_gelombang}' menjadi {$status}", 'gelombang', null, null, null, 'SUCCESS');
        return redirect()->route('admin.pengaturan', ['tab' => 'gelombang'])->with('success', "Status gelombang \"{$gelombang->nama_gelombang}\" berhasil diubah menjadi {$status}.");
    }

    public function hapusGelombang($id)
    {
        $gelombang = Gelombang::withCount('pendaftar')->findOrFail($id);

        if ($gelombang->pendaftar_count > 0) {
            return redirect()->route('admin.pengaturan', ['tab' => 'gelombang'])->with('error', "Gelombang \"{$gelombang->nama_gelombang}\" tidak dapat dihapus karena masih memiliki {$gelombang->pendaftar_count} data pendaftar.");
        }

        $nama = $gelombang->nama_gelombang;
        $gelombang->delete();
        LogAktivitas::log('DELETE', "Admin menghapus gelombang: {$nama}", 'gelombang', null, null, null, 'SUCCESS');
        return redirect()->route('admin.pengaturan', ['tab' => 'gelombang'])->with('success', "Gelombang \"{$nama}\" berhasil dihapus.");
    }

    public function masterVoucher(Request $request)
    {
        $request->merge(['tab' => 'voucher']);
        return $this->pengaturanSekolah();
    }

    public function simpanVoucher(Request $request)
    {
        $validated = $request->validate([
            'kode_voucher' => 'required|string|max:50|unique:voucher_pendaftaran,kode_voucher',
            'nama_voucher' => 'nullable|string|max:100',
            'tipe' => 'required|in:nominal,persen',
            'nilai_potongan' => 'required|numeric|min:1',
            'kuota_voucher' => 'required|integer|min:1',
            'berlaku_mulai' => 'nullable|date',
            'berlaku_sampai' => 'nullable|date|after_or_equal:berlaku_mulai',
            'deskripsi' => 'nullable|string|max:1000',
            'status_aktif' => 'nullable|boolean',
        ]);

        if ($validated['tipe'] === 'persen' && $validated['nilai_potongan'] > 100) {
            return back()->withErrors(['nilai_potongan' => 'Nilai potongan persentase tidak boleh melebihi 100%.'])->withInput();
        }

        $validated['kode_voucher'] = strtoupper(trim($validated['kode_voucher']));
        $validated['status_aktif'] = $request->has('status_aktif') ? (bool)$request->status_aktif : true;

        $voucher = VoucherPendaftaran::create($validated);

        LogAktivitas::log('CREATE', "Petugas TU membuat voucher diskon baru: {$voucher->kode_voucher}", 'voucher_pendaftaran', $voucher->id_voucher);

        return redirect()->route('admin.pengaturan', ['tab' => 'voucher'])->with('success', "Voucher diskon '{$voucher->kode_voucher}' berhasil dibuat.");
    }

    public function updateVoucher(Request $request, $id)
    {
        $voucher = VoucherPendaftaran::findOrFail($id);

        $validated = $request->validate([
            'kode_voucher' => 'required|string|max:50|unique:voucher_pendaftaran,kode_voucher,' . $id . ',id_voucher',
            'nama_voucher' => 'nullable|string|max:100',
            'tipe' => 'required|in:nominal,persen',
            'nilai_potongan' => 'required|numeric|min:1',
            'kuota_voucher' => 'required|integer|min:' . max(1, (int)$voucher->kuota_terpakai),
            'berlaku_mulai' => 'nullable|date',
            'berlaku_sampai' => 'nullable|date|after_or_equal:berlaku_mulai',
            'deskripsi' => 'nullable|string|max:1000',
            'status_aktif' => 'nullable|boolean',
        ], [
            'kuota_voucher.min' => "Kuota tidak boleh lebih kecil dari jumlah kuota yang sudah terpakai ({$voucher->kuota_terpakai} pemakaian).",
        ]);

        if ($validated['tipe'] === 'persen' && $validated['nilai_potongan'] > 100) {
            return back()->withErrors(['nilai_potongan' => 'Nilai potongan persentase tidak boleh melebihi 100%.'])->withInput();
        }

        $validated['kode_voucher'] = strtoupper(trim($validated['kode_voucher']));
        $validated['status_aktif'] = $request->has('status_aktif') ? (bool)$request->status_aktif : false;

        $voucher->update($validated);

        LogAktivitas::log('UPDATE', "Petugas TU memperbarui data voucher diskon: {$voucher->kode_voucher}", 'voucher_pendaftaran', $voucher->id_voucher);

        return redirect()->route('admin.pengaturan', ['tab' => 'voucher'])->with('success', "Voucher diskon '{$voucher->kode_voucher}' berhasil diperbarui.");
    }

    public function toggleStatusVoucher($id)
    {
        $voucher = VoucherPendaftaran::findOrFail($id);
        $voucher->status_aktif = !$voucher->status_aktif;
        $voucher->save();

        $statusText = $voucher->status_aktif ? 'diaktifkan' : 'dinonaktifkan';

        LogAktivitas::log('UPDATE', "Petugas TU {$statusText} voucher diskon: {$voucher->kode_voucher}", 'voucher_pendaftaran', $voucher->id_voucher);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'status_aktif' => $voucher->status_aktif,
                'message' => "Voucher {$voucher->kode_voucher} berhasil {$statusText}.",
            ]);
        }

        return redirect()->route('admin.pengaturan', ['tab' => 'voucher'])->with('success', "Voucher '{$voucher->kode_voucher}' berhasil {$statusText}.");
    }

    public function pengumuman()
    {
        return redirect()->route('admin.pengaturan', ['tab' => 'pengumuman']);
    }

    public function simpanPengumuman(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:150',
            'isi_pengumuman' => 'required|string',
            'tipe' => 'required|in:info,peringatan,penting,maintenance',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date',
            'tampil_di_banner' => 'nullable|boolean',
            'status_aktif' => 'nullable|boolean',
        ]);

        Pengumuman::create(array_merge($validated, [
            'id_admin' => auth('admin')->id(),
            'tampil_di_banner' => $request->has('tampil_di_banner'),
            'status_aktif' => $request->has('status_aktif'),
        ]));

        return redirect()->route('admin.pengaturan', ['tab' => 'pengumuman'])->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function updatePengumuman(Request $request, $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $validated = $request->validate([
            'judul'           => 'required|string|max:150',
            'isi_pengumuman'  => 'required|string',
            'tipe'            => 'required|in:info,peringatan,penting,maintenance',
            'tgl_mulai'       => 'required|date',
            'tgl_selesai'     => 'required|date',
            'tampil_di_banner'=> 'nullable|boolean',
            'status_aktif'    => 'nullable|boolean',
        ]);

        $pengumuman->update(array_merge($validated, [
            'tampil_di_banner' => $request->has('tampil_di_banner'),
            'status_aktif'     => $request->has('status_aktif'),
        ]));

        LogAktivitas::log('UPDATE', 'Admin memperbarui pengumuman: ' . $pengumuman->judul, 'pengumuman', $pengumuman->id_pengumuman);
        return redirect()->route('admin.pengaturan', ['tab' => 'pengumuman'])->with('success', 'Pengumuman "' . $pengumuman->judul . '" berhasil diperbarui.');
    }

    public function togglePengumuman($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $pengumuman->update(['status_aktif' => !$pengumuman->status_aktif]);
        $status = $pengumuman->status_aktif ? 'diaktifkan' : 'dinonaktifkan';
        LogAktivitas::log('UPDATE', "Admin {$status} pengumuman: {$pengumuman->judul}", 'pengumuman', $pengumuman->id_pengumuman);
        return redirect()->route('admin.pengaturan', ['tab' => 'pengumuman'])->with('success', "Pengumuman \"{$pengumuman->judul}\" berhasil {$status}.");
    }

    public function hapusPengumuman($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $judul = $pengumuman->judul;
        $pengumuman->delete();
        LogAktivitas::log('DELETE', 'Admin menghapus pengumuman: ' . $judul, 'pengumuman', $id);
        return redirect()->route('admin.pengaturan', ['tab' => 'pengumuman'])->with('success', "Pengumuman \"{$judul}\" berhasil dihapus.");
    }

    public function toggleMaintenance(Request $request)
    {
        $pengaturan = PengaturanSekolah::getPengaturan();
        $newStatus = !$pengaturan->mode_maintenance;
        $pengaturan->update(['mode_maintenance' => $newStatus]);

        $statusText = $newStatus ? 'DIAKTIFKAN (Pengunjung diarahkan ke halaman pemeliharaan)' : 'DINONAKTIFKAN (Website normal)';
        LogAktivitas::log('UPDATE', "Admin mengubah status Mode Maintenance: {$statusText}");

        return redirect()->route('admin.pengaturan', ['tab' => 'pengumuman'])->with('success', "Mode Pemeliharaan berhasil {$statusText}.");
    }

    public function pengaturanSekolah()
    {
        $pengaturan = PengaturanSekolah::getPengaturan();
        $gelombangs = Gelombang::withCount('pendaftar')->orderBy('id_gelombang')->get();
        $vouchers = VoucherPendaftaran::orderBy('id_voucher', 'desc')->get();
        $voucherStats = [
            'total'          => $vouchers->count(),
            'aktif'          => $vouchers->where('status_aktif', true)->count(),
            'terpakai'       => $vouchers->sum('kuota_terpakai'),
            'total_potongan' => Pembayaran::where('status_transaksi', 'settlement')->sum('potongan'),
        ];
        $pengumuman = Pengumuman::orderBy('id_pengumuman', 'desc')->get();
        return view('admin.pengaturan.index', compact('pengaturan', 'gelombangs', 'vouchers', 'voucherStats', 'pengumuman'));
    }

    public function simpanPengaturanSekolah(Request $request)
    {
        $pengaturan = PengaturanSekolah::getPengaturan();
        $validated = $request->validate([
            'nama_sekolah' => 'required|string|max:150',
            'npsn' => 'required|string|max:20',
            'ijin_operasional' => 'required|string|max:100',
            'alamat' => 'required|string',
            'kota' => 'required|string|max:50',
            'provinsi' => 'required|string|max:50',
            'no_telp' => 'required|string|max:30',
            'email' => 'required|email|max:100',
            'website' => 'required|string|max:100',
            'nama_kepsek' => 'required|string|max:100',
            'nip_kepsek' => 'nullable|string|max:50',
        ]);

        $pengaturan->update($validated);
        return back()->with('success', 'Pengaturan profil sekolah berhasil diperbarui.');
    }

    /**
     * Simpan pengaturan Tab Biaya Pendaftaran Formulir per Gelombang.
     */
    public function simpanBiayaPendaftaran(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->role !== 'kepala_sekolah') {
            abort(403, 'Akses ditolak. Pengubahan kebijakan biaya pendaftaran hanya dapat dilakukan oleh Kepala Sekolah.');
        }

        $validated = $request->validate([
            'gelombang' => 'required|array',
            'gelombang.*.biaya_pendaftaran' => 'required|numeric|min:0',
        ]);

        foreach ($validated['gelombang'] as $idGelombang => $item) {
            $gel = Gelombang::find($idGelombang);
            if ($gel) {
                $gel->update([
                    'biaya_pendaftaran' => $item['biaya_pendaftaran'],
                ]);
            }
        }

        LogAktivitas::log(
            'UPDATE',
            'Kepala Sekolah (' . $admin->nama_admin . ') memperbarui pengaturan Biaya Pendaftaran Formulir',
            'gelombang',
            1
        );

        return redirect()->route('admin.pengaturan', ['tab' => 'biaya_pendaftaran'])
            ->with('success', 'Pengaturan Biaya Formulir Pendaftaran berhasil diperbarui oleh Kepala Sekolah.');
    }

    /**
     * Simpan pengaturan Tab Biaya Daftar Ulang (Siswa Diterima).
     * Otorisasi khusus: Hanya Kepala Sekolah.
     */
    public function simpanBiayaDaftarUlang(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->role !== 'kepala_sekolah') {
            abort(403, 'Akses ditolak. Pengubahan kebijakan biaya daftar ulang hanya dapat dilakukan oleh Kepala Sekolah.');
        }

        $pengaturan = PengaturanSekolah::getPengaturan();

        $validated = $request->validate([
            'biaya_daftar_ulang' => 'required|numeric|min:0',
            'keterangan_daftar_ulang' => 'nullable|string',
            'instruksi_daftar_ulang' => 'nullable|string',
        ]);

        $pengaturan->update($validated);

        LogAktivitas::log(
            'UPDATE',
            'Kepala Sekolah (' . $admin->nama_admin . ') memperbarui pengaturan Biaya Daftar Ulang',
            'pengaturan_sekolah',
            $pengaturan->id_pengaturan
        );

        return redirect()->route('admin.pengaturan', ['tab' => 'biaya_daftar_ulang'])
            ->with('success', 'Pengaturan Biaya Daftar Ulang berhasil disimpan oleh Kepala Sekolah.');
    }

    /**
     * Simpan konfigurasi Tab Payment Gateway (Midtrans).
     */
    public function simpanPaymentGateway(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->role !== 'kepala_sekolah') {
            abort(403, 'Akses ditolak. Pengubahan konfigurasi Payment Gateway hanya dapat dilakukan oleh Kepala Sekolah.');
        }

        $pengaturan = PengaturanSekolah::getPengaturan();

        $validated = $request->validate([
            'midtrans_server_key' => 'nullable|string|max:255',
            'midtrans_client_key' => 'nullable|string|max:255',
            'midtrans_merchant_id' => 'nullable|string|max:100',
            'midtrans_environment' => 'required|in:sandbox,production',
            'midtrans_is_active' => 'nullable|boolean',
        ]);

        $data = [
            'midtrans_client_key' => $validated['midtrans_client_key'] ?? null,
            'midtrans_merchant_id' => $validated['midtrans_merchant_id'] ?? null,
            'midtrans_is_production' => $validated['midtrans_environment'] === 'production',
            'midtrans_is_active' => $request->has('midtrans_is_active'),
        ];

        // Jangan timpa jika user tidak mengubah input sensor Server Key
        $serverKey = trim((string) $request->input('midtrans_server_key', ''));
        if (!empty($serverKey) && !str_contains($serverKey, '•')) {
            $data['midtrans_server_key'] = $serverKey;
        }

        $pengaturan->update($data);

        LogAktivitas::log(
            'UPDATE',
            'Kepala Sekolah (' . $admin->nama_admin . ') memperbarui konfigurasi Payment Gateway (Midtrans)',
            'pengaturan_sekolah',
            $pengaturan->id_pengaturan,
            null,
            ['is_active' => $data['midtrans_is_active'], 'environment' => $validated['midtrans_environment']]
        );

        return redirect()->route('admin.pengaturan', ['tab' => 'gateway'])
            ->with('success', 'Konfigurasi Payment Gateway Midtrans berhasil disimpan dengan aman.');
    }

    /**
     * AJAX endpoint untuk uji koneksi Server Key ke Midtrans API.
     */
    public function testMidtransConnection(Request $request)
    {
        $pengaturan = PengaturanSekolah::getPengaturan();
        $inputKey = trim((string) $request->input('server_key', ''));
        $serverKey = (!empty($inputKey) && !str_contains($inputKey, '•')) ? $inputKey : $pengaturan->midtrans_server_key;
        $isProd = $request->input('environment') === 'production';

        if (empty($serverKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Midtrans Server Key belum diisi atau disimpan di database.',
            ], 422);
        }

        $baseUrl = $isProd ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2';

        try {
            $http = \Illuminate\Support\Facades\Http::withBasicAuth($serverKey, '')->timeout(10);
            if (!$isProd) {
                $http = $http->withoutVerifying();
            }
            $response = $http->get($baseUrl . '/dummy-connection-test-check/status');

            if ($response->status() === 401) {
                return response()->json([
                    'success' => false,
                    'message' => 'Autentikasi gagal (HTTP 401 Unauthorized). Server Key tidak valid untuk environment ' . ($isProd ? 'Production' : 'Sandbox') . '.',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Koneksi ke server Midtrans API berhasil! Kunci Server Key terautentikasi resmi pada mode ' . ($isProd ? 'Production (Live)' : 'Sandbox (Testing)') . '.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungi server Midtrans: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Menu khusus Verifikasi Pembayaran Tunai di Loket TU.
     */
    public function verifikasiPembayaranTunai(Request $request)
    {
        $search = trim((string) $request->input('q', ''));

        // 1. Antrean Pendaftar Menunggu Verifikasi Tunai
        $pendingQuery = Pendaftar::with(['jurusan1', 'pembayaran.voucher', 'gelombang'])
            ->where('status_pembayaran', '!=', 'Paid');

        if (!empty($search)) {
            $pendingQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('no_pendaftaran', 'like', "%{$search}%");
            });
        }

        $antreanTunai = $pendingQuery->latest('id_pendaftar')->paginate(15)->withQueryString();

        // 1b. Antrean Daftar Ulang Siswa Diterima yang Belum Selesai Daftar Ulang
        $antreanDaftarUlang = Pendaftar::with(['jurusan1', 'pembayaranDaftarUlang', 'gelombang'])
            ->where('status_kelulusan', 'Diterima')
            ->where('status_daftar_ulang', '!=', 'Selesai')
            ->latest('id_pendaftar')
            ->get();

        // 2. Riwayat Transaksi Tunai yang Sudah Dikonfirmasi Lunas
        $riwayatTunai = Pembayaran::with(['pendaftar.jurusan1', 'adminVerifikator', 'gelombang'])
            ->where('tipe_bayar', 'tunai_tu')
            ->where('status_transaksi', 'settlement')
            ->latest('waktu_konfirmasi_tu')
            ->take(20)
            ->get();

        return view('admin.pembayaran.tunai', compact('antreanTunai', 'riwayatTunai', 'search', 'antreanDaftarUlang'));
    }

    /**
     * Proses Konfirmasi Pembayaran Tunai oleh Petugas TU.
     */
    public function prosesKonfirmasiBayarTunai(Request $request, $id)
    {
        $pendaftar = Pendaftar::with(['pembayaran.voucher', 'gelombang'])->findOrFail($id);

        if ($pendaftar->status_pembayaran === 'Paid') {
            return back()->with('info', "Pembayaran calon siswa {$pendaftar->nama} sudah berstatus LUNAS sebelumnya.");
        }

        $admin = auth('admin')->user();
        $adminId = $admin ? $admin->id_admin : null;
        $adminNama = $admin ? $admin->nama_admin : 'Petugas TU';

        $gelombang = $pendaftar->gelombang ?? Gelombang::where('status_aktif', true)->first();
        $nominalAsli = $gelombang ? (float) $gelombang->biaya_pendaftaran : 100000.00;

        $existing = $pendaftar->pembayaran;
        $potongan = $existing ? (float) $existing->potongan : 0.00;
        $totalBayar = max(0.00, $nominalAsli - $potongan);
        $kodeTransaksi = $existing && $existing->kode_transaksi ? $existing->kode_transaksi : Pembayaran::generateKodeTransaksi('pendaftaran');

        DB::transaction(function () use ($pendaftar, $existing, $gelombang, $nominalAsli, $potongan, $totalBayar, $kodeTransaksi, $adminId, $adminNama) {
            $pembayaran = Pembayaran::updateOrCreate(
                [
                    'id_pendaftar' => $pendaftar->id_pendaftar,
                    'jenis_pembayaran' => 'pendaftaran',
                ],
                [
                    'id_gelombang' => $gelombang ? $gelombang->id_gelombang : ($existing ? $existing->id_gelombang : null),
                    'id_admin_verifikator' => $adminId,
                    'kode_transaksi' => $kodeTransaksi,
                    'nominal' => $nominalAsli,
                    'potongan' => $potongan,
                    'total_bayar' => $totalBayar,
                    'tipe_bayar' => 'tunai_tu',
                    'metode_pembayaran' => 'Bayar Tunai di Loket TU',
                    'status_transaksi' => 'settlement',
                    'waktu_transaksi' => Carbon::now(),
                    'waktu_konfirmasi_tu' => Carbon::now(),
                    'catatan' => "Dikonfirmasi lunas oleh Petugas TU ({$adminNama}) di loket sekolah.",
                ]
            );

            $pendaftar->update(['status_pembayaran' => 'Paid']);

            LogAktivitas::log(
                'VERIFIKASI_PEMBAYARAN_TUNAI',
                "Petugas TU {$adminNama} mengonfirmasi lunas pembayaran tunai untuk {$pendaftar->nama} ({$pendaftar->no_pendaftaran}) senilai Rp " . number_format($totalBayar, 0, ',', '.'),
                'pembayaran',
                $pembayaran->id_pembayaran,
                null,
                ['status' => 'Paid', 'total_bayar' => $totalBayar, 'petugas' => $adminNama],
                'SUCCESS',
                null,
                $adminId,
                'tu'
            );
        });

        return back()->with('success', "Alhamdulillah, pembayaran tunai formulir {$pendaftar->nama} ({$pendaftar->no_pendaftaran}) berhasil diverifikasi LUNAS!");
    }

    /**
     * Proses Konfirmasi Pembayaran Tunai Daftar Ulang oleh Petugas TU.
     */
    public function prosesKonfirmasiBayarDaftarUlangTunai(Request $request, $id)
    {
        $pendaftar = Pendaftar::with(['pembayaranDaftarUlang', 'gelombang'])->findOrFail($id);

        if ($pendaftar->status_daftar_ulang === 'Selesai') {
            return back()->with('info', "Daftar ulang calon siswa {$pendaftar->nama} sudah berstatus SELESAI sebelumnya.");
        }

        $admin = auth('admin')->user();
        $adminId = $admin ? $admin->id_admin : null;
        $adminNama = $admin ? $admin->nama_admin : 'Petugas TU';

        $pengaturan = PengaturanSekolah::getPengaturan();
        $nominalDaftarUlang = (float) ($pengaturan->biaya_daftar_ulang ?? 1500000.00);

        $existing = $pendaftar->pembayaranDaftarUlang;
        $kodeTransaksi = $existing && $existing->kode_transaksi ? $existing->kode_transaksi : Pembayaran::generateKodeTransaksi('daftar_ulang');

        DB::transaction(function () use ($pendaftar, $existing, $nominalDaftarUlang, $kodeTransaksi, $adminId, $adminNama) {
            $pembayaran = Pembayaran::updateOrCreate(
                [
                    'id_pendaftar' => $pendaftar->id_pendaftar,
                    'jenis_pembayaran' => 'daftar_ulang',
                ],
                [
                    'id_gelombang' => $pendaftar->id_gelombang,
                    'id_admin_verifikator' => $adminId,
                    'kode_transaksi' => $kodeTransaksi,
                    'nominal' => $nominalDaftarUlang,
                    'potongan' => 0.00,
                    'total_bayar' => $nominalDaftarUlang,
                    'tipe_bayar' => 'tunai_tu',
                    'metode_pembayaran' => 'Bayar Tunai Daftar Ulang di Loket TU',
                    'status_transaksi' => 'settlement',
                    'waktu_transaksi' => Carbon::now(),
                    'waktu_konfirmasi_tu' => Carbon::now(),
                    'catatan' => "Daftar ulang dikonfirmasi lunas oleh Petugas TU ({$adminNama}) di loket sekolah.",
                ]
            );

            $pendaftar->update(['status_daftar_ulang' => 'Selesai']);

            LogAktivitas::log(
                'VERIFIKASI_DAFTAR_ULANG_TUNAI',
                "Petugas TU {$adminNama} mengonfirmasi lunas pembayaran tunai daftar ulang untuk {$pendaftar->nama} ({$pendaftar->no_pendaftaran}) senilai Rp " . number_format($nominalDaftarUlang, 0, ',', '.'),
                'pembayaran',
                $pembayaran->id_pembayaran,
                null,
                ['status' => 'Selesai', 'nominal' => $nominalDaftarUlang, 'petugas' => $adminNama],
                'SUCCESS',
                null,
                $adminId,
                'tu'
            );
        });

        return back()->with('success', "Alhamdulillah, pembayaran daftar ulang {$pendaftar->nama} ({$pendaftar->no_pendaftaran}) berhasil diverifikasi dan RESMI SELESAI!");
    }

    // === EKSPOR DATA & LAPORAN CETAK ===

    /**
     * Sanitasi nilai sel untuk mencegah CSV Formula Injection (CWE-1236).
     * Sel yang berpotensi diawali '=', '+', '-', '@', tab, atau newline dinetralisir dengan awalan tanda petik tunggal.
     */
    protected function sanitizeCsvField($val): string
    {
        if ($val === null) {
            return '';
        }
        $str = (string) $val;
        if (preg_match('/^[=\+\-@\t\r]/', $str)) {
            return "'" . $str;
        }
        return $str;
    }

    public function eksporExcel(Request $request)
    {
        $pendaftar = Pendaftar::with(['jurusan1', 'jurusan2', 'pembayaran', 'terima'])->get();
        $filename = "Rekap_PPDB_SMK_IB_Khalifah_Bangsa_" . date('Ymd_His') . ".csv";

        $admin = auth('admin')->user();
        $namaAdmin = $admin ? $admin->nama_admin : 'Administrator';
        $roleTitle = ($admin && $admin->role === 'kepala_sekolah') ? 'Kepala Sekolah' : 'Petugas TU';

        LogAktivitas::log(
            'EKSPOR_CSV',
            "{$namaAdmin} ({$roleTitle}) mengunduh laporan rekapitulasi pendaftar format CSV ({$pendaftar->count()} data)",
            'pendaftar',
            null,
            null,
            [
                'format' => 'csv',
                'total_data' => $pendaftar->count(),
                'file' => $filename,
            ]
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pendaftar) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'No. Pendaftaran', 'NISN', 'Nama Lengkap', 'Tempat Lahir', 'Tanggal Lahir',
                'Jenis Kelamin', 'Agama', 'Pilihan Jurusan 1', 'Pilihan Jurusan 2', 'Asal Sekolah',
                'No HP Siswa', 'Nama Ayah', 'Nama Ibu', 'No HP Ortu', 'Pekerjaan Ayah',
                'Jalur Pendaftaran', 'Ukuran Seragam', 'Ukuran Wearpack', 'Status Pembayaran',
                'Status Verifikasi', 'Status Kelulusan', 'Tanggal Daftar'
            ]);

            foreach ($pendaftar as $p) {
                fputcsv($file, [
                    $this->sanitizeCsvField($p->no_pendaftaran),
                    $this->sanitizeCsvField($p->nisn),
                    $this->sanitizeCsvField($p->nama),
                    $this->sanitizeCsvField($p->tempat_lahir),
                    $p->tanggal_lahir ? $p->tanggal_lahir->format('d/m/Y') : '-',
                    $this->sanitizeCsvField($p->jenis_kelamin),
                    $this->sanitizeCsvField($p->agama),
                    $this->sanitizeCsvField($p->jurusan1 ? $p->jurusan1->nama_jurusan : '-'),
                    $this->sanitizeCsvField($p->jurusan2 ? $p->jurusan2->nama_jurusan : '-'),
                    $this->sanitizeCsvField($p->asal_sekolah),
                    $this->sanitizeCsvField($p->hp_siswa),
                    $this->sanitizeCsvField($p->nama_ayah),
                    $this->sanitizeCsvField($p->nama_ibu),
                    $this->sanitizeCsvField($p->hp_ortu),
                    $this->sanitizeCsvField($p->kerja_ayah),
                    $this->sanitizeCsvField($p->jalur_pendaftaran),
                    $this->sanitizeCsvField($p->ukuran_seragam),
                    $this->sanitizeCsvField($p->ukuran_wearpack),
                    $this->sanitizeCsvField($p->status_pembayaran),
                    $this->sanitizeCsvField($p->status_verifikasi),
                    $this->sanitizeCsvField($p->status_kelulusan),
                    $p->tgl_daftar ? $p->tgl_daftar->format('d/m/Y H:i') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function eksporDapodik()
    {
        $pendaftar = Pendaftar::where('status_kelulusan', 'Diterima')->get();
        $filename = "Template_Import_Dapodik_SMK_IB_" . date('Ymd') . ".csv";

        $admin = auth('admin')->user();
        $namaAdmin = $admin ? $admin->nama_admin : 'Administrator';
        $roleTitle = ($admin && $admin->role === 'kepala_sekolah') ? 'Kepala Sekolah' : 'Petugas TU';

        LogAktivitas::log(
            'EKSPOR_DAPODIK',
            "{$namaAdmin} ({$roleTitle}) mengunduh template ekspor Dapodik siswa diterima ({$pendaftar->count()} data)",
            'pendaftar',
            null,
            null,
            [
                'format' => 'csv_dapodik',
                'total_data' => $pendaftar->count(),
                'file' => $filename,
            ]
        );

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pendaftar) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Nama Peserta Didik', 'Jenis Kelamin', 'NISN', 'Tempat Lahir', 'Tanggal Lahir',
                'Agama', 'Alamat Tempat Tinggal', 'Nomor Telepon Seluler', 'Asal Sekolah',
                'Nama Ayah Kandung', 'Pekerjaan Ayah', 'Nama Ibu Kandung', 'Pekerjaan Ibu'
            ]);

            foreach ($pendaftar as $p) {
                fputcsv($file, [
                    $this->sanitizeCsvField($p->nama),
                    $p->jenis_kelamin === 'Laki-laki' ? 'L' : 'P',
                    $this->sanitizeCsvField($p->nisn),
                    $this->sanitizeCsvField($p->tempat_lahir),
                    $p->tanggal_lahir ? $p->tanggal_lahir->format('Y-m-d') : '-',
                    $this->sanitizeCsvField($p->agama),
                    $this->sanitizeCsvField($p->almt_siswa),
                    $this->sanitizeCsvField($p->hp_siswa),
                    $this->sanitizeCsvField($p->asal_sekolah),
                    $this->sanitizeCsvField($p->nama_ayah),
                    $this->sanitizeCsvField($p->kerja_ayah),
                    $this->sanitizeCsvField($p->nama_ibu),
                    $this->sanitizeCsvField($p->kerja_ibu),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function eksporXlsx(Request $request, XlsxExportService $xlsxService)
    {
        $query = Pendaftar::with(['jurusan1', 'jurusan2', 'pembayaran', 'terima']);

        if ($request->filled('jurusan')) {
            $query->where('id_jurusan_1', $request->jurusan);
        }
        if ($request->filled('status_bayar')) {
            $query->where('status_pembayaran', $request->status_bayar);
        }
        if ($request->filled('status_kelulusan')) {
            $query->where('status_kelulusan', $request->status_kelulusan);
        }

        $pendaftar = $query->orderBy('id_pendaftar', 'asc')->get();

        $headers = [
            'No. Pendaftaran', 'NISN', 'Nama Lengkap', 'Tempat Lahir', 'Tanggal Lahir',
            'Jenis Kelamin', 'Agama', 'Pilihan Jurusan 1', 'Pilihan Jurusan 2', 'Asal Sekolah',
            'No HP Siswa', 'Nama Ayah', 'Nama Ibu', 'No HP Ortu', 'Pekerjaan Ayah',
            'Jalur Pendaftaran', 'Ukuran Seragam', 'Ukuran Wearpack', 'Status Pembayaran',
            'Status Verifikasi', 'Status Kelulusan', 'Tanggal Daftar'
        ];

        $rows = [];
        foreach ($pendaftar as $p) {
            $rows[] = [
                $p->no_pendaftaran,
                $p->nisn,
                $p->nama,
                $p->tempat_lahir,
                $p->tanggal_lahir ? $p->tanggal_lahir->format('d/m/Y') : '-',
                $p->jenis_kelamin,
                $p->agama,
                $p->jurusan1 ? $p->jurusan1->nama_jurusan : '-',
                $p->jurusan2 ? $p->jurusan2->nama_jurusan : '-',
                $p->asal_sekolah,
                $p->hp_siswa,
                $p->nama_ayah,
                $p->nama_ibu,
                $p->hp_ortu,
                $p->kerja_ayah,
                $p->jalur_pendaftaran,
                $p->ukuran_seragam,
                $p->ukuran_wearpack,
                $p->status_pembayaran,
                $p->status_verifikasi,
                $p->status_kelulusan,
                $p->tgl_daftar ? $p->tgl_daftar->format('d/m/Y H:i') : '-',
            ];
        }

        $filename = "Rekap_PPDB_SMK_IB_Khalifah_Bangsa_" . date('Ymd_His') . ".xlsx";

        $admin = auth('admin')->user();
        $namaAdmin = $admin ? $admin->nama_admin : 'Administrator';
        $roleTitle = ($admin && $admin->role === 'kepala_sekolah') ? 'Kepala Sekolah' : 'Petugas TU';

        LogAktivitas::log(
            'EKSPOR_XLSX',
            "{$namaAdmin} ({$roleTitle}) mengunduh laporan rekapitulasi pendaftar format Excel .xlsx ({$pendaftar->count()} data)",
            'pendaftar',
            null,
            null,
            [
                'format' => 'xlsx',
                'total_data' => $pendaftar->count(),
                'file' => $filename,
                'filters' => array_filter($request->only(['jurusan', 'status_bayar', 'status_kelulusan'])),
            ]
        );

        return $xlsxService->export($headers, $rows, $filename);
    }

    public function cetakRekapPDF(Request $request)
    {
        $query = Pendaftar::with(['jurusan1', 'pembayaran', 'terima']);

        if ($request->filled('jurusan')) {
            $query->where('id_jurusan_1', $request->jurusan);
        }

        $pendaftar = $query->orderBy('nama', 'asc')->get();
        $pengaturan = PengaturanSekolah::getPengaturan();
        $selectedJurusan = $request->filled('jurusan') ? Jurusan::find($request->jurusan) : null;

        $admin = auth('admin')->user();
        $namaAdmin = $admin ? $admin->nama_admin : 'Administrator';
        $roleTitle = ($admin && $admin->role === 'kepala_sekolah') ? 'Kepala Sekolah' : 'Petugas TU';
        $jurusanInfo = $selectedJurusan ? "Jurusan {$selectedJurusan->nama_jurusan}" : "Semua Jurusan";

        LogAktivitas::log(
            'UNDUH_REKAP_PDF',
            "{$namaAdmin} ({$roleTitle}) mengunduh/mencetak rekapitulasi PDF ({$jurusanInfo}, {$pendaftar->count()} data)",
            'pendaftar',
            $selectedJurusan ? $selectedJurusan->id_jurusan : null,
            null,
            [
                'format' => 'pdf',
                'jurusan' => $selectedJurusan ? $selectedJurusan->nama_jurusan : 'Semua',
                'total_data' => $pendaftar->count(),
            ]
        );

        return view('admin.laporan.rekap_pdf', compact('pendaftar', 'pengaturan', 'selectedJurusan'));
    }

    // === CETAK DOKUMEN PENDAFTAR OLEH ADMIN TU ===

    /**
     * Cetak formulir pendaftar oleh admin TU.
     * Mengambil data berdasarkan id_pendaftar, bukan sesi siswa.
     */
    public function cetakFormulirPendaftar($id)
    {
        $pendaftar = Pendaftar::with(['jurusan1', 'jurusan2', 'berkas', 'pembayaran', 'gelombang'])
            ->findOrFail($id);

        $admin = auth('admin')->user();
        LogAktivitas::log(
            'CETAK_DOKUMEN_SISWA',
            "Admin TU ({$admin->nama_admin}) mencetak formulir biodata pendaftar: {$pendaftar->nama} ({$pendaftar->no_pendaftaran})",
            'pendaftar',
            $pendaftar->id_pendaftar,
            null,
            null,
            'SUCCESS',
            $pendaftar->id_user,
            $admin->id_admin,
            'tu'
        );

        $pengaturan = PengaturanSekolah::getPengaturan();

        return view('siswa.cetak_formulir', compact('pendaftar', 'pengaturan'))
            ->with('adminMode', true);
    }

    /**
     * Cetak kartu ujian pendaftar oleh admin TU.
     */
    public function cetakKartuUjianPendaftar($id)
    {
        $pendaftar = Pendaftar::with(['jurusan1', 'jurusan2', 'berkas', 'gelombang'])
            ->findOrFail($id);

        if ($pendaftar->status_pembayaran !== 'Paid') {
            return back()->with('error', 'Kartu ujian hanya dapat dicetak untuk pendaftar yang sudah Lunas.');
        }

        $admin = auth('admin')->user();
        LogAktivitas::log(
            'CETAK_DOKUMEN_SISWA',
            "Admin TU ({$admin->nama_admin}) mencetak kartu ujian pendaftar: {$pendaftar->nama} ({$pendaftar->no_pendaftaran})",
            'pendaftar',
            $pendaftar->id_pendaftar,
            null,
            null,
            'SUCCESS',
            $pendaftar->id_user,
            $admin->id_admin,
            'tu'
        );

        $pengaturan = PengaturanSekolah::getPengaturan();
        $penempatan = \App\Models\PenempatanPeserta::with(['jadwal', 'ruang'])
            ->where('id_pendaftar', $pendaftar->id_pendaftar)
            ->first();

        return view('siswa.cetak_kartu_ujian', compact('pendaftar', 'pengaturan', 'penempatan'))
            ->with('adminMode', true);
    }

    /**
     * Cetak SKL pendaftar oleh admin TU.
     */
    public function cetakSKLPendaftar($id)
    {
        $pendaftar = Pendaftar::with(['jurusan1', 'jurusan2', 'berkas', 'terima.jurusan'])
            ->findOrFail($id);

        if (!$pendaftar->terima) {
            return back()->with('error', 'SKL hanya tersedia untuk pendaftar yang telah dinyatakan Diterima.');
        }

        $admin = auth('admin')->user();
        LogAktivitas::log(
            'CETAK_DOKUMEN_SISWA',
            "Admin TU ({$admin->nama_admin}) mencetak Surat Keterangan Lulus (SKL): {$pendaftar->nama} ({$pendaftar->no_pendaftaran})",
            'pendaftar',
            $pendaftar->id_pendaftar,
            null,
            null,
            'SUCCESS',
            $pendaftar->id_user,
            $admin->id_admin,
            'tu'
        );

        $pengaturan = PengaturanSekolah::getPengaturan();

        return view('siswa.cetak_skl', compact('pendaftar', 'pengaturan'))
            ->with('adminMode', true);
    }

    /**
     * Halaman Riwayat Aktivitas untuk Petugas TU (Tahap 2).
     * Menampilkan rekam jejak aktivitas Calon Siswa dan Aktivitas Operasional TU.
     */
    public function riwayatAktivitas(Request $request)
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->role !== 'tu') {
            abort(403, 'Akses khusus Petugas Tata Usaha (TU).');
        }

        $query = LogAktivitas::with(['admin', 'user.pendaftar']);

        // Tab Kategori: Siswa vs Petugas TU vs Semua
        $tab = $request->get('tab', 'all');
        if ($tab === 'siswa') {
            $query->where(function ($q) {
                $q->where('role', 'calon_siswa')
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('id_user')->whereNull('id_admin');
                  });
            });
        } elseif ($tab === 'tu') {
            $query->where(function ($q) {
                $q->where('role', 'tu')
                  ->orWhere(function ($sub) {
                      $sub->whereNotNull('id_admin')
                          ->whereHas('admin', fn($a) => $a->where('role', 'tu'));
                  });
            });
        } else {
            // Default: aktivitas yang relevan dengan TU & Siswa (kecuali internal pengawasan murni kepsek)
            $query->where(function ($q) {
                $q->where('role', '!=', 'kepala_sekolah')
                  ->orWhereNull('role');
            });
        }

        // 1. Pencarian Teks Bebas (Nama siswa, No pendaftaran, jenis aktivitas, deskripsi)
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('deskripsi', 'LIKE', "%{$q}%")
                    ->orWhere('aksi', 'LIKE', "%{$q}%")
                    ->orWhere('ip_address', 'LIKE', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('nama', 'LIKE', "%{$q}%")
                          ->orWhere('nisn', 'LIKE', "%{$q}%")
                          ->orWhere('username', 'LIKE', "%{$q}%")
                          ->orWhereHas('pendaftar', function ($p) use ($q) {
                              $p->where('no_pendaftaran', 'LIKE', "%{$q}%")
                                ->orWhere('nama', 'LIKE', "%{$q}%");
                          });
                    })
                    ->orWhereHas('admin', function ($a) use ($q) {
                        $a->where('nama_admin', 'LIKE', "%{$q}%")
                          ->orWhere('username', 'LIKE', "%{$q}%");
                    });
            });
        }

        // 2. Filter Role Aktor
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // 3. Filter Jenis Aktivitas
        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }

        // 4. Filter Status
        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        // 5. Filter Rentang Tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('created_at', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->tanggal_akhir);
        }

        $logs = $query->orderBy('id_log', 'desc')->paginate(20)->withQueryString();

        // Statistik ringkas untuk TU
        $stats = [
            'total' => LogAktivitas::where('role', '!=', 'kepala_sekolah')->count(),
            'siswa' => LogAktivitas::where('role', 'calon_siswa')->count(),
            'tu' => LogAktivitas::where('role', 'tu')->count(),
            'gagal' => LogAktivitas::where('status', 'FAILED')->count(),
        ];

        // Daftar jenis aksi unik untuk filter
        $daftarAksi = LogAktivitas::where('role', '!=', 'kepala_sekolah')
            ->distinct()
            ->pluck('aksi')
            ->filter()
            ->values();

        return view('admin.riwayat_aktivitas', compact('logs', 'stats', 'daftarAksi', 'tab'));
    }
}

