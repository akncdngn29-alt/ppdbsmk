<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenempatanPeserta extends Model
{
    use HasFactory;

    protected $table = 'penempatan_peserta';
    protected $primaryKey = 'id_penempatan';

    protected $fillable = [
        'id_jadwal',
        'id_ruang',
        'id_pendaftar',
        'catatan',
    ];

    /**
     * Jadwal seleksi yang terkait
     */
    public function jadwal()
    {
        return $this->belongsTo(JadwalSeleksi::class, 'id_jadwal', 'id_jadwal');
    }

    /**
     * Ruang seleksi yang terkait
     */
    public function ruang()
    {
        return $this->belongsTo(RuangSeleksi::class, 'id_ruang', 'id_ruang');
    }

    /**
     * Pendaftar yang ditempatkan
     */
    public function pendaftar()
    {
        return $this->belongsTo(Pendaftar::class, 'id_pendaftar', 'id_pendaftar');
    }
}
