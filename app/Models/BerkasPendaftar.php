<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BerkasPendaftar extends Model
{
    use HasFactory;

    protected $table = 'berkas_pendaftar';
    protected $primaryKey = 'id_berkas';

    protected $fillable = [
        'id_pendaftar',
        'jenis_berkas',
        'nama_file',
        'path_file',
        'status_berkas',
        'catatan',
    ];

    public function pendaftar()
    {
        return $this->belongsTo(Pendaftar::class, 'id_pendaftar', 'id_pendaftar');
    }

    public function getFileUrlAttribute(): string
    {
        return route('dokumen.berkas', $this->id_berkas);
    }

    public function isTerverifikasi(): bool
    {
        return in_array($this->status_berkas, ['Valid', 'Terverifikasi']);
    }

    public function isPerluPerbaikan(): bool
    {
        return in_array($this->status_berkas, ['Tidak Valid', 'Perlu Perbaikan']);
    }

    public function isMenunggu(): bool
    {
        return $this->status_berkas === 'Menunggu';
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isTerverifikasi()) {
            return 'Terverifikasi';
        }
        if ($this->isPerluPerbaikan()) {
            return 'Perlu Perbaikan';
        }
        return 'Menunggu Verifikasi';
    }
}

