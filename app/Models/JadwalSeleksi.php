<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalSeleksi extends Model
{
    use HasFactory;

    protected $table = 'jadwal_seleksi';
    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'nama_jadwal',
        'jenis_seleksi',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'lokasi',
        'gedung',
        'keterangan',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Ruang-ruang yang digunakan pada jadwal ini (many-to-many via penempatan_peserta)
     */
    public function ruangPenempatan()
    {
        return $this->belongsToMany(
            RuangSeleksi::class,
            'penempatan_peserta',
            'id_jadwal',
            'id_ruang'
        )->withPivot('id_penempatan', 'catatan')->withTimestamps();
    }

    /**
     * Semua record penempatan pada jadwal ini
     */
    public function penempatan()
    {
        return $this->hasMany(PenempatanPeserta::class, 'id_jadwal', 'id_jadwal');
    }

    /**
     * Semua pendaftar yang sudah ditempatkan pada jadwal ini
     */
    public function pendaftar()
    {
        return $this->belongsToMany(
            Pendaftar::class,
            'penempatan_peserta',
            'id_jadwal',
            'id_pendaftar'
        )->withPivot('id_penempatan', 'id_ruang', 'catatan')->withTimestamps();
    }

    /**
     * Hitung total peserta yang sudah ditempatkan
     */
    public function getTotalPenempatanAttribute(): int
    {
        return $this->penempatan()->count();
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
