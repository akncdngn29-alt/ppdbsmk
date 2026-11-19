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
        'jenis_pembayaran',
        'id_admin_verifikator',
        'kode_transaksi',
        'snap_token',
        'qr_string',
        'qr_url',
        'nominal',
        'potongan',
        'total_bayar',
        'tipe_bayar',
        'metode_pembayaran',
        'status_transaksi',
        'waktu_transaksi',
        'waktu_konfirmasi_tu',
        'catatan',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'potongan' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'waktu_transaksi' => 'datetime',
        'waktu_konfirmasi_tu' => 'datetime',
    ];

    public function adminVerifikator()
    {
        return $this->belongsTo(Admin::class, 'id_admin_verifikator', 'id_admin');
    }

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

    public static function generateKodeTransaksi(string $jenis = 'pendaftaran'): string
    {
        $prefix = ($jenis === 'daftar_ulang') ? 'DU-PPDB-' : 'PPDB-';
        return $prefix . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}

