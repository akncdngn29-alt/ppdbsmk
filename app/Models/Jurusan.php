<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;

    protected $table = 'jurusan';
    protected $primaryKey = 'id_jurusan';

    protected $fillable = [
        'nama_jurusan',
        'kode_jurusan',
        'kuota',
        'deskripsi',
        'prospek_kerja',
        'status_aktif',
    ];

    protected $casts = [
        'kuota' => 'integer',
        'status_aktif' => 'boolean',
    ];

    public function pendaftarPilihan1()
    {
        return $this->hasMany(Pendaftar::class, 'id_jurusan_1', 'id_jurusan');
    }

    public function pendaftarPilihan2()
    {
        return $this->hasMany(Pendaftar::class, 'id_jurusan_2', 'id_jurusan');
    }

    public function siswaDiterima()
    {
        return $this->hasMany(Terima::class, 'id_jurusan', 'id_jurusan');
    }

    /**
     * Scope query untuk hanya mengambil jurusan yang berstatus aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    /**
     * Cek apakah jurusan aktif.
     */
    public function isAktif(): bool
    {
        return (bool) $this->status_aktif;
    }

    /**
     * Label status manusiawi.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status_aktif ? 'Aktif' : 'Nonaktif';
    }

    /**
     * Perhitungan sisa kuota penerimaan.
     */
    public function getSisaKuotaAttribute(): int
    {
        $count = $this->pendaftar_pilihan1_count ?? $this->pendaftarPilihan1()->count();
        return max(0, $this->kuota - $count);
    }

    /**
     * Cek apakah jurusan sudah memiliki pendaftar (pilihan 1, pilihan 2, atau siswa diterima).
     */
    public function hasPendaftar(): bool
    {
        return $this->pendaftarPilihan1()->exists()
            || $this->pendaftarPilihan2()->exists()
            || $this->siswaDiterima()->exists();
    }
}
