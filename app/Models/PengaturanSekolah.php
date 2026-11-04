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
        'midtrans_is_production',
        'whatsapp_api_token',
        'mode_maintenance',
    ];

    protected $casts = [
        'midtrans_is_production' => 'boolean',
        'mode_maintenance' => 'boolean',
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
            ]
        );
    }
}

