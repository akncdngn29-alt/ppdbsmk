<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendaftar extends Model
{
    use HasFactory;

    protected $table = 'pendaftar';
    protected $primaryKey = 'id_pendaftar';

    protected $fillable = [
        'id_user',
        'id_gelombang',
        'id_jurusan_1',
        'id_jurusan_2',
        'no_pendaftaran',
        'nisn',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'anak_ke',
        'jml_saudara',
        'hp_siswa',
        'almt_siswa',
        'berat_badan',
        'tinggi_badan',
        'golongan_darah',
        'asal_sekolah',
        'alamat_sekolah',
        'nama_ayah',
        'nama_ibu',
        'nama_wali',
        'almt_ortu',
        'hp_ortu',
        'kerja_ayah',
        'kerja_ibu',
        'kerja_wali',
        'penghasilan_ortu',
        'tanggungan_anak',
        'jalur_pendaftaran',
        'ukuran_seragam',
        'ukuran_wearpack',
        'pilihan_ekskul',
        'status_pembayaran',
        'status_verifikasi',
        'catatan_verifikasi',
        'status_kelulusan',
        'status_daftar_ulang',
        'is_locked',
        'tgl_daftar',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tgl_daftar' => 'datetime',
        'is_locked' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function gelombang()
    {
        return $this->belongsTo(Gelombang::class, 'id_gelombang', 'id_gelombang');
    }

    public function jurusan1()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan_1', 'id_jurusan');
    }

    public function jurusan2()
    {
        return $this->belongsTo(Jurusan::class, 'id_jurusan_2', 'id_jurusan');
    }

    public function berkas()
    {
        return $this->hasMany(BerkasPendaftar::class, 'id_pendaftar', 'id_pendaftar');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'id_pendaftar', 'id_pendaftar');
    }

    public function prestasi()
    {
        return $this->hasMany(PrestasiPendaftar::class, 'id_pendaftar', 'id_pendaftar');
    }

    public function nilaiSeleksi()
    {
        return $this->hasOne(NilaiSeleksi::class, 'id_pendaftar', 'id_pendaftar');
    }

    public function terima()
    {
        return $this->hasOne(Terima::class, 'id_pendaftar', 'id_pendaftar');
    }

    public static function generateNoPendaftaran($kodeJurusan, $tahun = null)
    {
        $tahun = $tahun ?: date('Y');
        $prefix = "PPDB-{$tahun}-{$kodeJurusan}-";
        $lastRecord = self::where('no_pendaftaran', 'LIKE', "{$prefix}%")
            ->orderBy('id_pendaftar', 'desc')
            ->first();

        if ($lastRecord) {
            $lastNum = (int) substr($lastRecord->no_pendaftaran, strrpos($lastRecord->no_pendaftaran, '-') + 1);
            $newNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNum = '001';
        }

        return $prefix . $newNum;
    }
}

