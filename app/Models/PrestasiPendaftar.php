<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrestasiPendaftar extends Model
{
    use HasFactory;

    protected $table = 'prestasi_pendaftar';
    protected $primaryKey = 'id_prestasi';

    protected $fillable = [
        'id_pendaftar',
        'nama_prestasi',
        'tingkat',
        'kategori',
        'peringkat',
        'tahun',
        'penyelenggara',
        'berkas_sertifikat',
        'skor_bobot',
        'status_verifikasi',
    ];

    public function pendaftar()
    {
        return $this->belongsTo(Pendaftar::class, 'id_pendaftar', 'id_pendaftar');
    }

    public function getFileUrlAttribute(): string
    {
        return route('dokumen.prestasi', $this->id_prestasi);
    }
}

