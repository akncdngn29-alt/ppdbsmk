<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\VoucherPendaftaran;
use App\Models\Pendaftar;
use App\Models\Pembayaran;
use App\Models\NilaiSeleksi;
use App\Models\PrestasiPendaftar;
use App\Models\Terima;
use App\Models\Pengumuman;
use App\Models\PengaturanSekolah;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pengaturan Sekolah
        PengaturanSekolah::getPengaturan();

        // 2. Master 4 Jurusan
        $jurusanFarmasi = Jurusan::create([
            'nama_jurusan' => 'Farmasi Klinis dan Komunitas',
            'kode_jurusan' => 'FAR',
            'kuota' => 72,
            'deskripsi' => 'Program keahlian kefarmasian klinis, peracikan obat, dan pelayanan komunitas berstandar industri kesehatan.',
        ]);

        $jurusanTKJ = Jurusan::create([
            'nama_jurusan' => 'Teknik Komputer dan Jaringan',
            'kode_jurusan' => 'TKJ',
            'kuota' => 72,
            'deskripsi' => 'Fokus pada infrastruktur jaringan komputer, fiber optic, server cloud, dan keamanan siber modern.',
        ]);

        $jurusanTKR = Jurusan::create([
            'nama_jurusan' => 'Teknik Kendaraan Ringan',
            'kode_jurusan' => 'TKR',
            'kuota' => 72,
            'deskripsi' => 'Mempelajari teknologi mekanik otomotif mobil, sistem EFI modern, kelistrikan, dan diagnosis kendaraan roda empat.',
        ]);

        $jurusanTBSM = Jurusan::create([
            'nama_jurusan' => 'Teknik dan Bisnis Sepeda Motor',
            'kode_jurusan' => 'TBSM',
            'kuota' => 72,
            'deskripsi' => 'Keahlian perbaikan, pemeliharaan sepeda motor injeksi, kelistrikan bodi, dan manajemen wirausaha bengkel.',
        ]);

        // 3. Master Gelombang Pendaftaran
        $gelombang1 = Gelombang::create([
            'nama_gelombang' => 'Gelombang 1 (Early Bird)',
            'tahun_ajaran' => '2026/2027',
            'tgl_buka' => Carbon::now()->subDays(15)->format('Y-m-d'),
            'tgl_tutup' => Carbon::now()->addDays(60)->format('Y-m-d'),
            'biaya_pendaftaran' => 100000.00,
            'status_aktif' => true,
        ]);

        Gelombang::create([
            'nama_gelombang' => 'Gelombang 2 (Reguler)',
            'tahun_ajaran' => '2026/2027',
            'tgl_buka' => Carbon::now()->addDays(61)->format('Y-m-d'),
            'tgl_tutup' => Carbon::now()->addDays(120)->format('Y-m-d'),
            'biaya_pendaftaran' => 150000.00,
            'status_aktif' => false,
        ]);

        // 4. Voucher Pendaftaran
        VoucherPendaftaran::create([
            'kode_voucher' => 'TAHFIZJUARA',
            'tipe' => 'nominal',
            'nilai_potongan' => 100000.00,
            'kuota_voucher' => 50,
            'status_aktif' => true,
        ]);

        VoucherPendaftaran::create([
            'kode_voucher' => 'DISMP25',
            'tipe' => 'nominal',
            'nilai_potongan' => 25000.00,
            'kuota_voucher' => 100,
            'status_aktif' => true,
        ]);

        // 5. Akun Administrator (TU & Kepala Sekolah)
        $adminTU = Admin::create([
            'nama_admin' => 'Budi Santoso, S.Kom',
            'username' => 'admin_tu',
            'email' => 'admin@smkib.com',
            'password' => Hash::make('admin123'),
            'role' => 'tu',
        ]);

        $kepsek = Admin::create([
            'nama_admin' => 'Asliman Puja Kesuma, S.Pd',
            'username' => 'kepsek_asliman',
            'email' => 'kepsek@smkib.com',
            'password' => Hash::make('kepsek123'),
            'role' => 'kepala_sekolah',
        ]);

        // 6. Akun Calon Siswa 1 (Lolos / Diterima)
        $user1 = User::create([
            'nama' => 'Ahmad Fauzi',
            'nisn' => '0078912345',
            'username' => 'ahmadfauzi',
            'email' => 'siswa@smkib.com',
            'password' => Hash::make('siswa123'),
        ]);

        $pendaftar1 = Pendaftar::create([
            'id_user' => $user1->id_user,
            'id_gelombang' => $gelombang1->id_gelombang,
            'id_jurusan_1' => $jurusanTKJ->id_jurusan,
            'id_jurusan_2' => $jurusanTBSM->id_jurusan,
            'no_pendaftaran' => 'PPDB-2026-TKJ-001',
            'nisn' => '0078912345',
            'nama' => 'Ahmad Fauzi',
            'tempat_lahir' => 'Kota Metro',
            'tanggal_lahir' => '2010-04-15',
            'jenis_kelamin' => 'Laki-laki',
            'agama' => 'Islam',
            'anak_ke' => 1,
            'jml_saudara' => 2,
            'hp_siswa' => '082188990011',
            'almt_siswa' => 'Jl. Jenderal Sudirman No. 45, Metro Pusat, Kota Metro',
            'berat_badan' => '52',
            'tinggi_badan' => '165',
            'golongan_darah' => 'O',
            'asal_sekolah' => 'SMP Negeri 1 Metro',
            'alamat_sekolah' => 'Jl. Raden Intan No. 12, Metro',
            'nama_ayah' => 'Surono',
            'nama_ibu' => 'Winarsih',
            'almt_ortu' => 'Jl. Jenderal Sudirman No. 45, Metro Pusat, Kota Metro',
            'hp_ortu' => '081277665544',
            'kerja_ayah' => 'Wiraswasta',
            'kerja_ibu' => 'Ibu Rumah Tangga',
            'penghasilan_ortu' => 'Rp 3.000.000 - Rp 5.000.000',
            'tanggungan_anak' => 3,
            'jalur_pendaftaran' => 'Prestasi',
            'ukuran_seragam' => 'L',
            'ukuran_wearpack' => 'L',
            'pilihan_ekskul' => 'Tafsir & Tahfiz Al-Qur\'an,Pramuka',
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Terverifikasi',
            'catatan_verifikasi' => 'Seluruh berkas lengkap dan sah. Nilai tahfiz memuaskan.',
            'status_kelulusan' => 'Diterima',
            'status_daftar_ulang' => 'Belum',
            'is_locked' => true,
            'tgl_daftar' => Carbon::now()->subDays(5),
        ]);

        Pembayaran::create([
            'id_pendaftar' => $pendaftar1->id_pendaftar,
            'id_gelombang' => $gelombang1->id_gelombang,
            'kode_transaksi' => 'TRX-2026-TKJ-001',
            'nominal' => 100000.00,
            'potongan' => 0.00,
            'total_bayar' => 100000.00,
            'tipe_bayar' => 'midtrans',
            'metode_pembayaran' => 'qris',
            'status_transaksi' => 'settlement',
            'waktu_transaksi' => Carbon::now()->subDays(5),
            'catatan' => 'Pembayaran lunas via QRIS Midtrans',
        ]);

        PrestasiPendaftar::create([
            'id_pendaftar' => $pendaftar1->id_pendaftar,
            'nama_prestasi' => 'Juara 1 Musabaqah Hifdzil Qur\'an (MHQ) 3 Juz',
            'tingkat' => 'Kabupaten/Kota',
            'kategori' => 'Keagamaan/Tahfiz',
            'peringkat' => 'Juara 1',
            'tahun' => 2025,
            'penyelenggara' => 'LPTQ Kota Metro',
            'berkas_sertifikat' => 'demo/sertifikat_tahfiz.pdf',
            'skor_bobot' => 30,
            'status_verifikasi' => 'Valid',
        ]);

        NilaiSeleksi::create([
            'id_pendaftar' => $pendaftar1->id_pendaftar,
            'nilai_rapor' => 88.50,
            'nilai_wawancara' => 90.00,
            'tes_buta_warna' => 'Normal',
            'total_nilai' => 89.25,
            'catatan_tes' => 'Kemampuan logika jaringan dan hafalan Al-Qur\'an sangat baik. Tidak buta warna.',
            'rekomendasi_jurusan' => 'Teknik Komputer dan Jaringan',
        ]);

        Terima::create([
            'id_pendaftar' => $pendaftar1->id_pendaftar,
            'id_jurusan' => $jurusanTKJ->id_jurusan,
            'id_admin_pengesah' => $kepsek->id_admin,
            'no_surat_kelulusan' => '421.5/SKL-PPDB/2026/0001',
            'tgl_diterima' => Carbon::now()->subDay()->format('Y-m-d'),
            'keterangan' => 'Dinyatakan Lulus Seleksi Tahap 1 Program Keahlian TKJ.',
        ]);

        // 7. Akun Calon Siswa 2 (Amanda Manopo - Sedang Diproses)
        $user2 = User::create([
            'nama' => 'Amanda Manopo',
            'nisn' => '0078912346',
            'username' => 'amandamanopo',
            'email' => 'amanda@smkib.com',
            'password' => Hash::make('siswa123'),
        ]);

        $pendaftar2 = Pendaftar::create([
            'id_user' => $user2->id_user,
            'id_gelombang' => $gelombang1->id_gelombang,
            'id_jurusan_1' => $jurusanFarmasi->id_jurusan,
            'id_jurusan_2' => $jurusanTKJ->id_jurusan,
            'no_pendaftaran' => 'PPDB-2026-FAR-002',
            'nisn' => '0078912346',
            'nama' => 'Amanda Manopo',
            'tempat_lahir' => 'Kota Metro',
            'tanggal_lahir' => '2010-06-20',
            'jenis_kelamin' => 'Perempuan',
            'agama' => 'Islam',
            'anak_ke' => 2,
            'jml_saudara' => 1,
            'hp_siswa' => '082188990022',
            'almt_siswa' => 'Jl. Pattimura Barat No. 88, Metro Barat',
            'berat_badan' => '48',
            'tinggi_badan' => '158',
            'golongan_darah' => 'B',
            'asal_sekolah' => 'SMP Negeri 3 Metro',
            'alamat_sekolah' => 'Jl. AH Nasution No. 30, Metro',
            'nama_ayah' => 'Bagas Prasetyo',
            'nama_ibu' => 'Elina Rahayu',
            'almt_ortu' => 'Jl. Pattimura Barat No. 88, Metro Barat',
            'hp_ortu' => '081277665555',
            'kerja_ayah' => 'PNS',
            'kerja_ibu' => 'Ibu Rumah Tangga',
            'penghasilan_ortu' => 'Rp 5.000.000 - Rp 10.000.000',
            'tanggungan_anak' => 2,
            'jalur_pendaftaran' => 'Reguler',
            'ukuran_seragam' => 'M',
            'ukuran_wearpack' => 'M',
            'pilihan_ekskul' => 'Tilawati Al-Qur\'an,Karate',
            'status_pembayaran' => 'Paid',
            'status_verifikasi' => 'Menunggu',
            'status_kelulusan' => 'Diproses',
            'status_daftar_ulang' => 'Belum',
            'is_locked' => true,
            'tgl_daftar' => Carbon::now()->subDays(2),
        ]);

        Pembayaran::create([
            'id_pendaftar' => $pendaftar2->id_pendaftar,
            'id_gelombang' => $gelombang1->id_gelombang,
            'kode_transaksi' => 'TRX-2026-FAR-002',
            'nominal' => 100000.00,
            'potongan' => 0.00,
            'total_bayar' => 100000.00,
            'tipe_bayar' => 'midtrans',
            'metode_pembayaran' => 'bank_transfer',
            'status_transaksi' => 'settlement',
            'waktu_transaksi' => Carbon::now()->subDays(2),
            'catatan' => 'Pembayaran via BSI Virtual Account',
        ]);

        // 8. Pengumuman Awal
        Pengumuman::create([
            'id_admin' => $adminTU->id_admin,
            'judul' => 'Penerimaan Peserta Didik Baru (PPDB) T.A 2026/2027 Resmi Dibuka!',
            'isi_pengumuman' => 'Selamat datang di Sistem Informasi PPDB Online SMK Islam Bina Khalifah Bangsa Kota Metro. Silakan buat akun menggunakan NISN dan lengkapi data formulir.',
            'tipe' => 'info',
            'tampil_di_banner' => true,
            'status_aktif' => true,
            'tgl_mulai' => Carbon::now()->subDays(10),
            'tgl_selesai' => Carbon::now()->addDays(60),
        ]);
    }
}
