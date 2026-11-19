<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuangSeleksi extends Model
{
    use HasFactory;

    protected $table = 'ruang_seleksi';
    protected $primaryKey = 'id_ruang';

    protected $fillable = [
        'kode_ruang',
        'nama_ruang',
        'gedung',
        'kapasitas',
        'keterangan',
        'status_aktif',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'kapasitas'    => 'integer',
    ];

    /**
     * Semua record penempatan di ruang ini
     */
    public function penempatan()
    {
        return $this->hasMany(PenempatanPeserta::class, 'id_ruang', 'id_ruang');
    }

    public function scopeAktif($query)
    {
        return $query->where('status_aktif', true);
    }

    /**
     * Hitung sisa kapasitas untuk jadwal tertentu
     */
    public function sisaKapasitas(int $idJadwal): int
    {
        $terisi = $this->penempatan()->where('id_jadwal', $idJadwal)->count();
        return max(0, $this->kapasitas - $terisi);
    }
}
