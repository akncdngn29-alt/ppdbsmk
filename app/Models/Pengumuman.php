<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';
    protected $primaryKey = 'id_pengumuman';

    protected $fillable = [
        'id_admin',
        'judul',
        'isi_pengumuman',
        'tipe',
        'tampil_di_banner',
        'status_aktif',
        'tgl_mulai',
        'tgl_selesai',
    ];

    protected $casts = [
        'tampil_di_banner' => 'boolean',
        'status_aktif' => 'boolean',
        'tgl_mulai' => 'datetime',
        'tgl_selesai' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public static function getActiveBanner()
    {
        $now = Carbon::now();
        return self::where('status_aktif', true)
            ->where('tampil_di_banner', true)
            ->where('tgl_mulai', '<=', $now)
            ->where('tgl_selesai', '>=', $now)
            ->orderBy('id_pengumuman', 'desc')
            ->first();
    }
}

