<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NilaiSeleksi extends Model
{
    use HasFactory;

    protected $table = 'nilai_seleksi';
    protected $primaryKey = 'id_nilai';

    protected $fillable = [
        'id_pendaftar',
        'nilai_rapor',
        'nilai_wawancara',
        'tes_buta_warna',
        'total_nilai',
        'catatan_tes',
        'rekomendasi_jurusan',
    ];

    protected $casts = [
        'nilai_rapor' => 'decimal:2',
        'nilai_wawancara' => 'decimal:2',
        'total_nilai' => 'decimal:2',
    ];

    public function pendaftar()
    {
        return $this->belongsTo(Pendaftar::class, 'id_pendaftar', 'id_pendaftar');
    }
}

