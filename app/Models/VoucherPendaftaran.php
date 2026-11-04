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
        'kuota_terpakai',
        'status_aktif',
    ];

    protected $casts = [
        'nilai_potongan' => 'decimal:2',
        'kuota_voucher' => 'integer',
        'kuota_terpakai' => 'integer',
        'status_aktif' => 'boolean',
    ];

    public function isAvailable(): bool
    {
        return $this->status_aktif && ($this->kuota_terpakai < $this->kuota_voucher);
    }

    public function sisaKuota(): int
    {
        return max(0, $this->kuota_voucher - $this->kuota_terpakai);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_voucher', 'id_voucher');
    }
}

