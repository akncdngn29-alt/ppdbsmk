<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Pendaftar extends Model
{
    use HasFactory;

    protected $table = 'pendaftar';
    protected $primaryKey = 'id_pendaftar';

    protected static function booted(): void
    {
        static::creating(function (Pendaftar $pendaftar) {
            if (empty($pendaftar->verification_token)) {
                $pendaftar->verification_token = Str::random(32);
            }
        });
    }

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
        'verification_token',
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
        return $this->hasOne(Pembayaran::class, 'id_pendaftar', 'id_pendaftar')->where('jenis_pembayaran', 'pendaftaran');
    }

    public function pembayaranDaftarUlang()
    {
        return $this->hasOne(Pembayaran::class, 'id_pendaftar', 'id_pendaftar')->where('jenis_pembayaran', 'daftar_ulang');
    }

    public function semuaPembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_pendaftar', 'id_pendaftar');
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

    public function penempatan()
    {
        return $this->hasMany(PenempatanPeserta::class, 'id_pendaftar', 'id_pendaftar');
    }

    public function penempatanAktif()
    {
        return $this->hasOne(PenempatanPeserta::class, 'id_pendaftar', 'id_pendaftar')
            ->latestOfMany();
    }

    public function jadwalSeleksi()
    {
        return $this->belongsToMany(
            JadwalSeleksi::class,
            'penempatan_peserta',
            'id_pendaftar',
            'id_jadwal'
        )->withPivot('id_penempatan', 'id_ruang', 'catatan')->withTimestamps();
    }

    public static function generateNoPendaftaran($kodeJurusan = 'DFT', $tahun = null)
    {
        $kodeJurusan = !empty($kodeJurusan) ? $kodeJurusan : 'DFT';
        $tahun = $tahun ?: date('Y');
        $prefix = "PPDB-{$tahun}-{$kodeJurusan}-";

        $generateAtomic = function () use ($prefix) {
            // Pastikan baris prefix ada di tabel sekuens
            $existing = DB::table('pendaftaran_sequences')
                ->where('prefix', $prefix)
                ->first();

            if (!$existing) {
                // Inisialisasi dari nomor terbesar yang sudah ada di tabel pendaftar
                $lastRecord = self::where('no_pendaftaran', 'LIKE', "{$prefix}%")
                    ->orderBy('id_pendaftar', 'desc')
                    ->first();

                $currentMax = 0;
                if ($lastRecord) {
                    $lastPart = substr($lastRecord->no_pendaftaran, strrpos($lastRecord->no_pendaftaran, '-') + 1);
                    $currentMax = max(0, (int)$lastPart);
                }

                DB::table('pendaftaran_sequences')->insertOrIgnore([
                    'prefix' => $prefix,
                    'last_number' => $currentMax,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Kunci baris sequence dengan lockForUpdate untuk mencegah nomor ganda saat transaksi simultan
            $seq = DB::table('pendaftaran_sequences')
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            $nextNum = $seq ? ($seq->last_number + 1) : 1;

            // Pastikan kandidat belum terpakai oleh data riwayat yang mungkin melompati nomor
            $candidate = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
            while (self::where('no_pendaftaran', $candidate)->exists()) {
                $nextNum++;
                $candidate = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
            }

            DB::table('pendaftaran_sequences')
                ->where('prefix', $prefix)
                ->update([
                    'last_number' => $nextNum,
                    'updated_at' => now(),
                ]);

            return $candidate;
        };

        if (DB::transactionLevel() > 0) {
            return $generateAtomic();
        }

        return DB::transaction($generateAtomic);
    }
}

