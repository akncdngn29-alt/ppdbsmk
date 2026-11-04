<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\BerkasPendaftar;
use App\Models\Pembayaran;
use App\Models\PrestasiPendaftar;
use App\Models\PengaturanSekolah;
use App\Models\LogAktivitas;
use Carbon\Carbon;

class SiswaDashboardController extends Controller
{
    private function getUserPendaftar()
    {
        $user = Auth::user();
        return Pendaftar::with(['jurusan1', 'jurusan2', 'berkas', 'pembayaran', 'prestasi', 'nilaiSeleksi', 'terima.jurusan'])
            ->where('id_user', $user->id_user)
            ->first();
    }

    public function index()
    {
        $user = Auth::user();
        $pendaftar = $this->getUserPendaftar();
        $pengaturan = PengaturanSekolah::getPengaturan();

        // Calculate progress stage (1 to 6)
        $stage = 1;
        if ($pendaftar) {
            $stage = 2; // Form completed
            if ($pendaftar->status_pembayaran === 'Paid') {
                $stage = 3; // Paid
                if ($pendaftar->status_verifikasi === 'Terverifikasi') {
                    $stage = 4; // Verified
                    if ($pendaftar->status_kelulusan !== 'Diproses') {
                        $stage = 5; // Result announced
                        if ($pendaftar->status_daftar_ulang === 'Selesai') {
                            $stage = 6; // Enrolled
                        }
                    }
                }
            }
        }

        return view('siswa.dashboard', compact('user', 'pendaftar', 'pengaturan', 'stage'));
    }

    public function formulir()
    {
        $user = Auth::user();
        $pendaftar = $this->getUserPendaftar();
        $jurusan = Jurusan::all();
        $gelombang = Gelombang::where('status_aktif', true)->first();

        if ($pendaftar && $pendaftar->is_locked) {
            return redirect()->route('siswa.dashboard')->with('info', 'Formulir Anda sudah difinalisasi dan terkunci.');
        }

        return view('siswa.formulir', compact('user', 'pendaftar', 'jurusan', 'gelombang'));
    }

    public function simpanFormulir(Request $request)
    {
        $user = Auth::user();
        $pendaftar = $this->getUserPendaftar();

        if ($pendaftar && $pendaftar->is_locked) {
            return back()->with('error', 'Formulir Anda telah dikunci.');
        }

        $validated = $request->validate([
            'id_jurusan_1' => 'required|exists:jurusan,id_jurusan',
            'id_jurusan_2' => 'nullable|different:id_jurusan_1|exists:jurusan,id_jurusan',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|string|max:50',
            'anak_ke' => 'required|integer|min:1',
            'jml_saudara' => 'required|integer|min:0',
            'hp_siswa' => 'required|string|max:20',
            'almt_siswa' => 'required|string',
            'berat_badan' => 'required|string|max:10',
            'tinggi_badan' => 'required|string|max:10',
            'golongan_darah' => 'required|string|max:5',
            'asal_sekolah' => 'required|string|max:100',
            'alamat_sekolah' => 'nullable|string',
            'nama_ayah' => 'required|string|max:100',
            'nama_ibu' => 'required|string|max:100',
            'nama_wali' => 'nullable|string|max:100',
            'almt_ortu' => 'required|string',
            'hp_ortu' => 'required|string|max:20',
            'kerja_ayah' => 'required|string|max:100',
            'kerja_ibu' => 'required|string|max:100',
            'kerja_wali' => 'nullable|string|max:100',
            'penghasilan_ortu' => 'required|string|max:50',
            'tanggungan_anak' => 'required|integer|min:1',
            'jalur_pendaftaran' => 'required|in:Reguler,Prestasi,Afirmasi,Zonasi',
            'ukuran_seragam' => 'required|in:S,M,L,XL,XXL,Custom',
            'ukuran_wearpack' => 'required|in:S,M,L,XL,XXL,Custom',
            'pilihan_ekskul' => 'nullable|array',
        ], [
            'id_jurusan_2.different' => 'Jurusan Pilihan 2 tidak boleh sama dengan Pilihan 1.',
        ]);

        $gelombang = Gelombang::where('status_aktif', true)->first();
        if (!$gelombang) {
            $gelombang = Gelombang::first();
        }

        $jurusan1 = Jurusan::find($validated['id_jurusan_1']);
        $pilihanEkskulStr = !empty($validated['pilihan_ekskul']) ? implode(',', $validated['pilihan_ekskul']) : null;

        if (!$pendaftar) {
            $noPendaftaran = Pendaftar::generateNoPendaftaran($jurusan1->kode_jurusan);
            $pendaftar = Pendaftar::create(array_merge($validated, [
                'id_user' => $user->id_user,
                'id_gelombang' => $gelombang->id_gelombang,
                'no_pendaftaran' => $noPendaftaran,
                'nisn' => $user->nisn,
                'nama' => $user->nama,
                'pilihan_ekskul' => $pilihanEkskulStr,
                'status_pembayaran' => 'Pending',
                'status_verifikasi' => 'Menunggu',
                'status_kelulusan' => 'Diproses',
                'status_daftar_ulang' => 'Belum',
                'is_locked' => false,
                'tgl_daftar' => Carbon::now(),
            ]));
            LogAktivitas::log('INSERT', 'Siswa mengisi formulir pendaftaran: ' . $noPendaftaran, 'pendaftar', $pendaftar->id_pendaftar);
        } else {
            $pendaftar->update(array_merge($validated, [
                'pilihan_ekskul' => $pilihanEkskulStr,
            ]));
            LogAktivitas::log('UPDATE', 'Siswa memperbarui formulir pendaftaran', 'pendaftar', $pendaftar->id_pendaftar);
        }

        return redirect()->route('siswa.dashboard')->with('success', 'Formulir berhasil disimpan. Anda dapat memeriksa berkas atau memfinalisasi pendaftaran.');
    }

    public function finalisasi()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return redirect()->route('siswa.formulir')->with('error', 'Silakan lengkapi formulir terlebih dahulu.');
        }

        $pendaftar->update(['is_locked' => true]);
        LogAktivitas::log('UPDATE', 'Siswa mengunci dan memfinalisasi data formulir', 'pendaftar', $pendaftar->id_pendaftar);

        return redirect()->route('siswa.dashboard')->with('success', 'Data formulir berhasil difinalisasi! Anda sekarang dapat melakukan pembayaran dan mengunggah berkas.');
    }

    public function berkas()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return redirect()->route('siswa.formulir')->with('error', 'Silakan lengkapi formulir terlebih dahulu sebelum mengunggah berkas.');
        }

        $berkas = $pendaftar->berkas;
        $prestasi = $pendaftar->prestasi;

        return view('siswa.berkas', compact('pendaftar', 'berkas', 'prestasi'));
    }

    public function uploadBerkas(Request $request)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return back()->with('error', 'Formulir belum dibuat.');
        }

        $request->validate([
            'jenis_berkas' => 'required|in:ijazah,kk,pasfoto,akta,lainnya',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:3072',
        ], [
            'file.max' => 'Ukuran file maksimal adalah 3 MB.',
            'file.mimes' => 'Format file yang diperbolehkan hanya JPG, PNG, atau PDF.',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $request->jenis_berkas . '_' . $pendaftar->nisn . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('berkas_pendaftar', $filename, 'public');

        // Check if existing file of this type exists
        $existing = BerkasPendaftar::where('id_pendaftar', $pendaftar->id_pendaftar)
            ->where('jenis_berkas', $request->jenis_berkas)
            ->first();

        if ($existing) {
            Storage::disk('public')->delete($existing->path_file);
            $existing->update([
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'status_berkas' => 'Menunggu',
                'catatan' => null,
            ]);
        } else {
            BerkasPendaftar::create([
                'id_pendaftar' => $pendaftar->id_pendaftar,
                'jenis_berkas' => $request->jenis_berkas,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'status_berkas' => 'Menunggu',
            ]);
        }

        LogAktivitas::log('INSERT', 'Siswa mengunggah berkas: ' . $request->jenis_berkas, 'berkas_pendaftar', $pendaftar->id_pendaftar);

        return back()->with('success', 'Berkas ' . strtoupper($request->jenis_berkas) . ' berhasil diunggah.');
    }

    public function uploadPrestasi(Request $request)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return back()->with('error', 'Formulir belum dibuat.');
        }

        $validated = $request->validate([
            'nama_prestasi' => 'required|string|max:150',
            'tingkat' => 'required|in:Kecamatan,Kabupaten/Kota,Provinsi,Nasional,Internasional',
            'kategori' => 'required|in:Keagamaan/Tahfiz,Akademik,Olahraga,Seni/Budaya',
            'peringkat' => 'required|string|max:50',
            'tahun' => 'required|integer|min:2020|max:' . date('Y'),
            'penyelenggara' => 'required|string|max:150',
            'file_sertifikat' => 'required|file|mimes:jpg,jpeg,png,pdf|max:3072',
        ]);

        $file = $request->file('file_sertifikat');
        $filename = time() . '_prestasi_' . $pendaftar->nisn . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('prestasi_pendaftar', $filename, 'public');

        // Score estimation
        $skor = 10;
        if ($validated['tingkat'] === 'Nasional' || $validated['tingkat'] === 'Internasional') {
            $skor = 30;
        } elseif ($validated['tingkat'] === 'Provinsi') {
            $skor = 20;
        }

        PrestasiPendaftar::create([
            'id_pendaftar' => $pendaftar->id_pendaftar,
            'nama_prestasi' => $validated['nama_prestasi'],
            'tingkat' => $validated['tingkat'],
            'kategori' => $validated['kategori'],
            'peringkat' => $validated['peringkat'],
            'tahun' => $validated['tahun'],
            'penyelenggara' => $validated['penyelenggara'],
            'berkas_sertifikat' => $path,
            'skor_bobot' => $skor,
            'status_verifikasi' => 'Menunggu',
        ]);

        LogAktivitas::log('INSERT', 'Siswa menambahkan sertifikat prestasi: ' . $validated['nama_prestasi'], 'prestasi_pendaftar', $pendaftar->id_pendaftar);

        return back()->with('success', 'Sertifikat prestasi berhasil ditambahkan dan menunggu verifikasi panitia.');
    }

    public function pembayaran()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return redirect()->route('siswa.formulir')->with('error', 'Silakan lengkapi formulir terlebih dahulu.');
        }

        $gelombang = $pendaftar->gelombang ?: Gelombang::where('status_aktif', true)->first();
        $pembayaran = $pendaftar->pembayaran;

        return view('siswa.pembayaran', compact('pendaftar', 'gelombang', 'pembayaran'));
    }

    public function prosesBayarTunai(Request $request)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return back()->with('error', 'Data pendaftar tidak ditemukan.');
        }

        $gelombang = $pendaftar->gelombang ?: Gelombang::first();
        $kodeTransaksi = Pembayaran::generateKodeTransaksi();

        $pembayaran = Pembayaran::updateOrCreate(
            ['id_pendaftar' => $pendaftar->id_pendaftar],
            [
                'id_gelombang' => $gelombang->id_gelombang,
                'kode_transaksi' => $kodeTransaksi,
                'nominal' => $gelombang->biaya_pendaftaran,
                'potongan' => 0.00,
                'total_bayar' => $gelombang->biaya_pendaftaran,
                'tipe_bayar' => 'tunai_tu',
                'metode_pembayaran' => 'Loket TU',
                'status_transaksi' => 'pending',
                'catatan' => 'Siswa memilih pembayaran tunai di loket TU sekolah.',
            ]
        );

        LogAktivitas::log('INSERT', 'Siswa memilih opsi bayar tunai di loket TU: ' . $kodeTransaksi, 'pembayaran', $pembayaran->id_pembayaran);

        return back()->with('success', 'Nomor tagihan tunai telah dibuat: ' . $kodeTransaksi . '. Silakan tunjukkan ke Petugas TU di sekolah.');
    }

    public function prosesBayarMidtransSimulasi(Request $request)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return back()->with('error', 'Data pendaftar tidak ditemukan.');
        }

        $gelombang = $pendaftar->gelombang ?: Gelombang::first();
        $nominalAsli = $gelombang->biaya_pendaftaran;
        $potongan = 0.00;
        $metode = $request->input('metode_pembayaran', 'QRIS (Simulasi Midtrans)');

        // Validasi dan terapkan voucher jika disertakan
        if ($request->filled('kode_voucher')) {
            $voucher = \App\Models\VoucherPendaftaran::where('kode_voucher', strtoupper(trim($request->kode_voucher)))
                ->where('status_aktif', true)
                ->first();
            if ($voucher && $voucher->kuota_terpakai < $voucher->kuota_voucher) {
                if ($voucher->tipe === 'persen') {
                    $potongan = $nominalAsli * ($voucher->nilai_potongan / 100);
                } else {
                    $potongan = min($nominalAsli, $voucher->nilai_potongan);
                }
                $voucher->increment('kuota_terpakai');
            }
        }

        $totalBayar = max(0, $nominalAsli - $potongan);
        $kodeTransaksi = Pembayaran::generateKodeTransaksi();

        $pembayaran = Pembayaran::updateOrCreate(
            ['id_pendaftar' => $pendaftar->id_pendaftar],
            [
                'id_gelombang' => $gelombang->id_gelombang,
                'kode_transaksi' => $kodeTransaksi,
                'nominal' => $nominalAsli,
                'potongan' => $potongan,
                'total_bayar' => $totalBayar,
                'tipe_bayar' => 'midtrans',
                'metode_pembayaran' => $metode,
                'status_transaksi' => 'settlement',
                'waktu_transaksi' => Carbon::now(),
                'catatan' => 'Pembayaran instan terverifikasi via Midtrans Simulator (' . $metode . ').',
            ]
        );

        $pendaftar->update(['status_pembayaran' => 'Paid']);
        LogAktivitas::log('UPDATE', 'Pembayaran Midtrans (' . $metode . ') berhasil: ' . $kodeTransaksi, 'pembayaran', $pembayaran->id_pembayaran);

        return redirect()->route('siswa.pembayaran')->with('success', 'Pembayaran berhasil diselesaikan! Status Anda sekarang LUNAS.');
    }

    // === DAFTAR ULANG & PENGATURAN AKUN ===

    public function daftarUlang()
    {
        $user = Auth::user();
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return redirect()->route('siswa.dashboard')->with('error', 'Silakan lengkapi formulir pendaftaran terlebih dahulu.');
        }

        $pengaturan = PengaturanSekolah::getPengaturan();
        return view('siswa.daftar_ulang', compact('user', 'pendaftar', 'pengaturan'));
    }

    public function pengaturanAkun()
    {
        $user = Auth::user();
        $pendaftar = $this->getUserPendaftar();
        return view('siswa.pengaturan', compact('user', 'pendaftar'));
    }

    public function simpanPengaturanAkun(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'hp_siswa' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $pendaftar = $this->getUserPendaftar();
        if ($pendaftar && $request->filled('hp_siswa')) {
            $pendaftar->update(['hp_siswa' => $request->hp_siswa]);
        }

        LogAktivitas::log('UPDATE', 'Calon Siswa memperbarui pengaturan akun: ' . $user->nama, 'user', $user->id_user);
        return back()->with('success', 'Pengaturan akun Anda berhasil diperbarui.');
    }

    // === CETAK DOKUMEN RESMI (PDF / PRINTABLE) ===

    public function cetakFormulir()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return redirect()->route('siswa.dashboard')->with('error', 'Data pendaftaran belum ada.');
        }

        $pengaturan = PengaturanSekolah::getPengaturan();
        return view('siswa.cetak_formulir', compact('pendaftar', 'pengaturan'));
    }

    public function cetakKartuUjian()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar || $pendaftar->status_pembayaran !== 'Paid') {
            return redirect()->route('siswa.dashboard')->with('error', 'Kartu ujian hanya dapat dicetak setelah biaya pendaftaran Lunas.');
        }

        $pengaturan = PengaturanSekolah::getPengaturan();
        return view('siswa.cetak_kartu_ujian', compact('pendaftar', 'pengaturan'));
    }

    public function cetakKwitansi()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar || !$pendaftar->pembayaran || $pendaftar->pembayaran->status_transaksi !== 'settlement') {
            return redirect()->route('siswa.dashboard')->with('error', 'Kwitansi hanya tersedia untuk pembayaran yang telah Lunas.');
        }

        $pengaturan = PengaturanSekolah::getPengaturan();
        return view('siswa.cetak_kwitansi', compact('pendaftar', 'pengaturan'));
    }

    public function unduhSKL()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar || !$pendaftar->terima) {
            return redirect()->route('siswa.dashboard')->with('error', 'Surat Keterangan Lulus hanya tersedia bagi calon siswa yang telah dinyatakan Diterima.');
        }

        $pengaturan = PengaturanSekolah::getPengaturan();
        return view('siswa.cetak_skl', compact('pendaftar', 'pengaturan'));
    }

    public function konfirmasiDaftarUlang(Request $request)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar || $pendaftar->status_kelulusan !== 'Diterima') {
            return back()->with('error', 'Konfirmasi daftar ulang hanya untuk siswa yang diterima.');
        }

        $pendaftar->update(['status_daftar_ulang' => 'Selesai']);
        LogAktivitas::log('UPDATE', 'Siswa mengonfirmasi pendaftaran ulang: ' . $pendaftar->nama, 'pendaftar', $pendaftar->id_pendaftar);

        return back()->with('success', 'Selamat! Anda telah resmi mengonfirmasi pendaftaran ulang sebagai Siswa Baru SMK Islam Bina Khalifah Bangsa.');
    }
}

