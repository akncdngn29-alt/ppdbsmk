<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'nama_admin',
        'username',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isTU(): bool
    {
        return $this->role === 'tu';
    }

    public function isKepalaSekolah(): bool
    {
        return $this->role === 'kepala_sekolah';
    }

    public function pengesahanTerima()
    {
        return $this->hasMany(Terima::class, 'id_admin_pengesah', 'id_admin');
    }

    public function pengumuman()
    {
        return $this->hasMany(Pengumuman::class, 'id_admin', 'id_admin');
    }

    public function logAktivitas()
    {
        return $this->hasMany(LogAktivitas::class, 'id_admin', 'id_admin');
    }
}

