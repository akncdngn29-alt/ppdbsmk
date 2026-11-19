<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VoucherPendaftaran extends Model
{
    use HasFactory;

    protected $table = 'voucher_pendaftaran';
    protected $primaryKey = 'id_voucher';

    protected $fillable = [
        'kode_voucher',
        'nama_voucher',
        'tipe',
        'nilai_potongan',
        'kuota_voucher',
        'kuota_terpakai',
        'berlaku_mulai',
        'berlaku_sampai',
        'deskripsi',
        'status_aktif',
    ];

    protected $casts = [
        'nilai_potongan' => 'decimal:2',
        'kuota_voucher' => 'integer',
        'kuota_terpakai' => 'integer',
        'status_aktif' => 'boolean',
        'berlaku_mulai' => 'date',
        'berlaku_sampai' => 'date',
    ];

    public function isExpired(): bool
    {
        if (!$this->berlaku_sampai) {
            return false;
        }
        return Carbon::today()->gt(Carbon::parse($this->berlaku_sampai)->endOfDay());
    }

    public function isNotStartedYet(): bool
    {
        if (!$this->berlaku_mulai) {
            return false;
        }
        return Carbon::today()->lt(Carbon::parse($this->berlaku_mulai)->startOfDay());
    }

    public function isAvailable(): bool
    {
        return $this->status_aktif
            && ($this->kuota_terpakai < $this->kuota_voucher)
            && !$this->isExpired()
            && !$this->isNotStartedYet();
    }

    public function validateAvailability(): array
    {
        if (!$this->status_aktif) {
            return [
                'valid' => false,
                'message' => 'Kode voucher ini sedang tidak aktif atau dinonaktifkan oleh administrator.',
            ];
        }

        if ($this->isNotStartedYet()) {
            return [
                'valid' => false,
                'message' => 'Kode voucher belum dapat digunakan (baru mulai berlaku tanggal ' . ($this->berlaku_mulai ? $this->berlaku_mulai->translatedFormat('d F Y') : '-') . ').',
            ];
        }

        if ($this->isExpired()) {
            return [
                'valid' => false,
                'message' => 'Kode voucher telah kedaluwarsa pada tanggal ' . ($this->berlaku_sampai ? $this->berlaku_sampai->translatedFormat('d F Y') : '-') . '.',
            ];
        }

        if ($this->kuota_terpakai >= $this->kuota_voucher) {
            return [
                'valid' => false,
                'message' => 'Batas kuota penggunaan voucher telah habis (' . $this->kuota_voucher . ' pemakaian tercapai).',
            ];
        }

        return [
            'valid' => true,
            'message' => 'Kode voucher valid dan dapat digunakan.',
        ];
    }

    public function hitungPotongan(float $nominalAsli): float
    {
        if ($this->tipe === 'persen') {
            $potongan = $nominalAsli * ((float)$this->nilai_potongan / 100);
        } else {
            $potongan = (float)$this->nilai_potongan;
        }

        return min($nominalAsli, max(0.0, $potongan));
    }

    public function hitungTotalBayar(float $nominalAsli): float
    {
        return max(0.0, $nominalAsli - $this->hitungPotongan($nominalAsli));
    }

    public function sisaKuota(): int
    {
        return max(0, $this->kuota_voucher - $this->kuota_terpakai);
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->status_aktif) {
            return 'Nonaktif';
        }
        if ($this->isExpired()) {
            return 'Kedaluwarsa';
        }
        if ($this->isNotStartedYet()) {
            return 'Belum Mulai';
        }
        if ($this->kuota_terpakai >= $this->kuota_voucher) {
            return 'Kuota Habis';
        }
        return 'Aktif';
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_voucher', 'id_voucher');
    }
}
