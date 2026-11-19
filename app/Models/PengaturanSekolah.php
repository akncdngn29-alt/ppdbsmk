<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanSekolah extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_sekolah';
    protected $primaryKey = 'id_pengaturan';

    protected $fillable = [
        'nama_sekolah',
        'npsn',
        'ijin_operasional',
        'alamat',
        'kota',
        'provinsi',
        'no_telp',
        'email',
        'website',
        'nama_kepsek',
        'nip_kepsek',
        'logo',
        'stempel',
        'ttd_kepsek',
        'midtrans_server_key',
        'midtrans_client_key',
        'midtrans_merchant_id',
        'midtrans_is_production',
        'midtrans_is_active',
        'whatsapp_api_token',
        'mode_maintenance',
        'biaya_daftar_ulang',
        'keterangan_daftar_ulang',
        'instruksi_daftar_ulang',
    ];

    protected $casts = [
        'midtrans_is_production' => 'boolean',
        'midtrans_is_active' => 'boolean',
        'mode_maintenance' => 'boolean',
        'biaya_daftar_ulang' => 'decimal:2',
    ];

    public static function getPengaturan()
    {
        return self::firstOrCreate(
            ['id_pengaturan' => 1],
            [
                'nama_sekolah' => 'SMK Islam Bina Khalifah Bangsa',
                'npsn' => '69773555',
                'ijin_operasional' => '115/KPTS/D.3/03/2013',
                'alamat' => 'Jl. Pattimura No. 24, Ganjar Asri, Kec. Metro Barat, Kota Metro, Lampung 34125',
                'kota' => 'Kota Metro',
                'provinsi' => 'Lampung',
                'no_telp' => '0821-7890-1234',
                'email' => 'info@smkib.com',
                'website' => 'https://smkib.com',
                'nama_kepsek' => 'Asliman Puja Kesuma, S.Pd',
                'nip_kepsek' => '197805122005011003',
                'mode_maintenance' => false,
                'biaya_daftar_ulang' => 1500000.00,
                'keterangan_daftar_ulang' => "1. Seragam Harian (Osis, Pramuka, Batik, Wearpack Kejuruan)\n2. Atribut Sekolah Lengkap (Topi, Dasi, Sabuk, Bet Jurusan)\n3. Kegiatan Pra-MPLS & MPLS Siswa Baru\n4. Asuransi Kecelakaan Siswa Selama 1 Tahun",
                'instruksi_daftar_ulang' => 'Bagi calon siswa yang memilih pembayaran tunai, silakan datang langsung ke Loket TU Kampus SMK IB Khalifah Bangsa dengan membawa bukti formulir dan berkas fisik.',
            ]
        );
    }
}

