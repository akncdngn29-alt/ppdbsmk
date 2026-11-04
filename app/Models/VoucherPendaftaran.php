<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherPendaftaran extends Model
{
    use HasFactory;

    protected $table = 'voucher_pendaftaran';
    protected $primaryKey = 'id_voucher';

    protected $fillable = [
        'kode_voucher',
        'tipe',
        'nilai_potongan',
        'kuota_voucher',
        'status_aktif',
    ];

    protected $casts = [
        'nilai_potongan' => 'decimal:2',
        'status_aktif' => 'boolean',
    ];

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_voucher', 'id_voucher');
    }
}

