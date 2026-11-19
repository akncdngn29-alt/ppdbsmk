<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Terima extends Model
{
    use HasFactory;

    protected $table = 'terima';
    protected $primaryKey = 'id_terima';

    protected $fillable = [
        'id_pendaftar',
        'id_jurusan',
        'id_admin_pengesah',
        'no_surat_kelulusan',
        'tgl_diterima',
        'keterangan',
        'verification_token',
    ];

    protected $casts = [
        'tgl_diterima' => 'date',
    ];

    protected static function booted(): void
    {
        static::creating(function (Terima $terima) {
            if (empty($terima->verification_token)) {
                $terima->verification_token = Str::random(32);
            }
        });
    }

    public function pendaftar()
    {
        return $this->belongsTo(Pendaftar::class, 'id_pendaftar', 'id_pendaftar');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan', 'id_jurusan');
    }

    public function adminPengesah()
    {
        return $this->belongsTo(Admin::class, 'id_admin_pengesah', 'id_admin');
    }

    public static function generateNoSuratKelulusan($tahun = null)
    {
        $tahun = $tahun ?: date('Y');
        $prefix = "421.5/SKL-PPDB/{$tahun}/";
        $count = self::where('no_surat_kelulusan', 'LIKE', "{$prefix}%")->count();
        $newNum = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        return $prefix . $newNum;
    }
}

