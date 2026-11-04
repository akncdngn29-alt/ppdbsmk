<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAktivitas extends Model
{
    use HasFactory;

    protected $table = 'log_aktivitas';
    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_admin',
        'id_user',
        'aksi',
        'tabel_terkait',
        'id_data',
        'deskripsi',
        'data_lama',
        'data_baru',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public static function log($aksi, $deskripsi, $tabelTerkait = null, $idData = null, $dataLama = null, $dataBaru = null)
    {
        $idAdmin = auth('admin')->id();
        $idUser = auth()->id();

        return self::create([
            'id_admin' => $idAdmin,
            'id_user' => $idUser,
            'aksi' => $aksi,
            'tabel_terkait' => $tabelTerkait,
            'id_data' => $idData,
            'deskripsi' => $deskripsi,
            'data_lama' => $dataLama,
            'data_baru' => $dataBaru,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

