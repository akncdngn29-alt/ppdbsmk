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
            'pembayaran',
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
            'status_verifikasi' => 'required|in:Terverifikasi,Ditolak,Menunggu',
            'catatan_verifikasi' => 'nullable|string',
        ]);

        $pendaftar = Pendaftar::findOrFail($id);
        $oldStatus = $pendaftar->status_verifikasi;
        
        $pendaftar->update([
            'status_verifikasi' => $request->status_verifikasi,
            'catatan_verifikasi' => $request->catatan_verifikasi,
        ]);

        LogAktivitas::log(
            'VERIFIKASI',
            "Admin TU mengubah status verifikasi {$pendaftar->nama} dari {$oldStatus} menjadi {$request->status_verifikasi}",
            'pendaftar',
            $pendaftar->id_pendaftar,
            ['status' => $oldStatus],
            ['status' => $request->status_verifikasi, 'catatan' => $request->catatan_verifikasi]
        );

        return back()->with('success', 'Status verifikasi berkas pendaftar berhasil diperbarui.');
    }

    public function verifikasiSingleBerkas(Request $request, $idBerkas)
    {
        $request->validate([
            'status_berkas' => 'required|in:Valid,Tidak Valid,Menunggu',
            'catatan' => 'nullable|string',
        ]);

        $berkas = BerkasPendaftar::findOrFail($idBerkas);
        $berkas->update([
            'status_berkas' => $request->status_berkas,
            'catatan' => $request->catatan,
        ]);

        return back()->with('success', 'Status item berkas berhasil diperbarui.');
    }

    public function konfirmasiBayarTunai($idPendaftar)
    {
        $pendaftar = Pendaftar::findOrFail($idPendaftar);
        $gelombang = $pendaftar->gelombang ?: Gelombang::first();

        $pembayaran = Pembayaran::updateOrCreate(
            ['id_pendaftar' => $pendaftar->id_pendaftar],
            [
                'id_gelombang' => $gelombang->id_gelombang,
                'kode_transaksi' => Pembayaran::generateKodeTransaksi(),
                'nominal' => $gelombang->biaya_pendaftaran,
                'potongan' => 0.00,
                'total_bayar' => $gelombang->biaya_pendaftaran,
                'tipe_bayar' => 'tunai_tu',
                'metode_pembayaran' => 'Loket TU (Tunai Kasir)',
                'status_transaksi' => 'settlement',
                'waktu_transaksi' => Carbon::now(),
                'catatan' => 'Diverifikasi langsung oleh Petugas TU di loket sekolah.',
            ]
        );

        $pendaftar->update(['status_pembayaran' => 'Paid']);

        LogAktivitas::log('UPDATE', "Admin TU memverifikasi lunas pembayaran tunai untuk {$pendaftar->nama}", 'pembayaran', $pembayaran->id_pembayaran);

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
        $jurusan = Jurusan::withCount('pendaftarPilihan1')->get();
        return view('admin.master.jurusan', compact('jurusan'));
    }

    public function updateJurusan(Request $request, $id)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:100',
            'kuota' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string',
        ]);

        $j = Jurusan::findOrFail($id);
        $j->update($request->only('nama_jurusan', 'kuota', 'deskripsi'));

        return back()->with('success', 'Data jurusan berhasil diperbarui.');
    }

    public function masterGelombang()
    {
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
        return back()->with('success', 'Gelombang pendaftaran baru berhasil ditambahkan.');
    }

    public function masterVoucher()
    {
        $vouchers = VoucherPendaftaran::all();
        return view('admin.master.voucher', compact('vouchers'));
    }

    public function simpanVoucher(Request $request)
    {
        $validated = $request->validate([
            'kode_voucher' => 'required|string|max:50|unique:voucher_pendaftaran,kode_voucher',
            'tipe' => 'required|in:nominal,persen',
            'nilai_potongan' => 'required|numeric|min:1',
            'kuota_voucher' => 'required|integer|min:1',
        ]);

        VoucherPendaftaran::create(array_merge($validated, ['status_aktif' => true]));
        return back()->with('success', 'Voucher diskon berhasil dibuat.');
    }

    public function pengumuman()
    {
        $pengumuman = Pengumuman::orderBy('id_pengumuman', 'desc')->get();
        $pengaturan = PengaturanSekolah::getPengaturan();
        return view('admin.pengumuman.index', compact('pengumuman', 'pengaturan'));
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

        return back()->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function toggleMaintenance(Request $request)
    {
        $pengaturan = PengaturanSekolah::getPengaturan();
        $newStatus = !$pengaturan->mode_maintenance;
        $pengaturan->update(['mode_maintenance' => $newStatus]);

        $statusText = $newStatus ? 'DIAKTIFKAN (Pengunjung diarahkan ke halaman pemeliharaan)' : 'DINONAKTIFKAN (Website normal)';
        LogAktivitas::log('UPDATE', "Admin mengubah status Mode Maintenance: {$statusText}");

        return back()->with('success', "Mode Pemeliharaan berhasil {$statusText}.");
    }

    public function pengaturanSekolah()
    {
        $pengaturan = PengaturanSekolah::getPengaturan();
        return view('admin.pengaturan.index', compact('pengaturan'));
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
            'midtrans_server_key' => 'nullable|string|max:255',
            'midtrans_client_key' => 'nullable|string|max:255',
            'midtrans_is_production' => 'nullable|boolean',
            'whatsapp_api_token' => 'nullable|string|max:255',
        ]);

        $validated['midtrans_is_production'] = $request->has('midtrans_is_production');

        $pengaturan->update($validated);
        return back()->with('success', 'Pengaturan profil sekolah berhasil diperbarui.');
    }

    // === EKSPOR DATA & LAPORAN CETAK ===

    public function eksporExcel(Request $request)
    {
        $pendaftar = Pendaftar::with(['jurusan1', 'jurusan2', 'pembayaran', 'terima'])->get();
        $filename = "Rekap_PPDB_SMK_IB_Khalifah_Bangsa_" . date('Ymd_His') . ".csv";

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
                    $p->no_pendaftaran,
                    $p->nisn,
                    $p->nama,
                    $p->tempat_lahir,
                    $p->tanggal_lahir->format('d/m/Y'),
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
                    $p->tgl_daftar->format('d/m/Y H:i'),
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
                    $p->nama,
                    $p->jenis_kelamin === 'Laki-laki' ? 'L' : 'P',
                    $p->nisn,
                    $p->tempat_lahir,
                    $p->tanggal_lahir->format('Y-m-d'),
                    $p->agama,
                    $p->almt_siswa,
                    $p->hp_siswa,
                    $p->asal_sekolah,
                    $p->nama_ayah,
                    $p->kerja_ayah,
                    $p->nama_ibu,
                    $p->kerja_ibu,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
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

        return view('admin.laporan.rekap_pdf', compact('pendaftar', 'pengaturan', 'selectedJurusan'));
    }
}

