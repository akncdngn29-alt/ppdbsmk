<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Gelombang extends Model
{
    use HasFactory;

    protected $table = 'gelombang';
    protected $primaryKey = 'id_gelombang';

    protected $fillable = [
        'nama_gelombang',
        'tahun_ajaran',
        'tgl_buka',
        'tgl_tutup',
        'biaya_pendaftaran',
        'status_aktif',
    ];

    protected $casts = [
        'tgl_buka' => 'date',
        'tgl_tutup' => 'date',
        'biaya_pendaftaran' => 'decimal:2',
        'status_aktif' => 'boolean',
    ];

    public function pendaftar()
    {
        return $this->hasMany(Pendaftar::class, 'id_gelombang', 'id_gelombang');
    }

    public function isOpen(): bool
    {
        $today = Carbon::today();
        return $this->status_aktif && $today->betweenIncluded($this->tgl_buka, $this->tgl_tutup);
    }
}

