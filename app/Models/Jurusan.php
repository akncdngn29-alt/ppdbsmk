<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;

    protected $table = 'jurusan';
    protected $primaryKey = 'id_jurusan';

    protected $fillable = [
        'nama_jurusan',
        'kode_jurusan',
        'kuota',
        'deskripsi',
    ];

    public function pendaftarPilihan1()
    {
        return $this->hasMany(Pendaftar::class, 'id_jurusan_1', 'id_jurusan');
    }

    public function pendaftarPilihan2()
    {
        return $this->hasMany(Pendaftar::class, 'id_jurusan_2', 'id_jurusan');
    }

    public function siswaDiterima()
    {
        return $this->hasMany(Terima::class, 'id_jurusan', 'id_jurusan');
    }
}

