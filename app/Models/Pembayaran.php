<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';

    protected $fillable = [
        'id_pendaftar',
        'id_gelombang',
        'id_voucher',
        'kode_transaksi',
        'snap_token',
        'nominal',
        'potongan',
        'total_bayar',
        'tipe_bayar',
        'metode_pembayaran',
        'status_transaksi',
        'waktu_transaksi',
        'catatan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'potongan' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'waktu_transaksi' => 'datetime',
    ];

    public function pendaftar()
    {
        return $this->belongsTo(Pendaftar::class, 'id_pendaftar', 'id_pendaftar');
    }

    public function gelombang()
    {
        return $this->belongsTo(Gelombang::class, 'id_gelombang', 'id_gelombang');
    }

    public function voucher()
    {
        return $this->belongsTo(VoucherPendaftaran::class, 'id_voucher', 'id_voucher');
    }

    public static function generateKodeTransaksi()
    {
        return 'TRX-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}

