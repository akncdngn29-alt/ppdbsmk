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
        'role',
        'aksi',
        'tabel_terkait',
        'id_data',
        'deskripsi',
        'status',
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

    /**
     * Dapatkan nama aktor yang melakukan atau terlibat dalam aksi.
     */
    public function getNamaAktorAttribute(): string
    {
        if ($this->admin) {
            return $this->admin->nama_admin;
        }

        if ($this->user) {
            return $this->user->nama ?? $this->user->username ?? 'Calon Siswa';
        }

        return 'Sistem Otomatis';
    }

    /**
     * Label peran manusiawi untuk UI.
     */
    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'calon_siswa' => 'Calon Siswa',
            'tu' => 'Petugas TU',
            'kepala_sekolah' => 'Kepala Sekolah',
            default => 'Sistem Otomatis',
        };
    }

    /**
     * Catat log aktivitas ke sistem.
     * Tidak boleh menyimpan password, token, atau informasi sensitif.
     */
    public static function log(
        string $aksi,
        string $deskripsi,
        ?string $tabelTerkait = null,
        mixed $idData = null,
        mixed $dataLama = null,
        mixed $dataBaru = null,
        string $status = 'SUCCESS',
        ?int $idUserOverride = null,
        ?int $idAdminOverride = null,
        ?string $roleOverride = null
    ) {
        $idAdmin = $idAdminOverride ?? (auth('admin')->check() ? auth('admin')->id() : null);
        $idUser = $idUserOverride ?? (auth('web')->check() ? auth('web')->id() : null);

        // Jika idUser masih null tapi aksi berkaitan dengan entitas siswa
        if ($idUser === null && $tabelTerkait === 'pendaftar' && $idData) {
            $idUser = Pendaftar::where('id_pendaftar', $idData)->value('id_user');
        } elseif ($idUser === null && $tabelTerkait === 'user' && $idData) {
            $idUser = (int) $idData;
        } elseif ($idUser === null && $tabelTerkait === 'berkas_pendaftar' && $idData) {
            $idUser = BerkasPendaftar::where('id_berkas', $idData)
                ->join('pendaftar', 'berkas_pendaftar.id_pendaftar', '=', 'pendaftar.id_pendaftar')
                ->value('pendaftar.id_user');
        } elseif ($idUser === null && $tabelTerkait === 'pembayaran' && $idData) {
            $idUser = Pembayaran::where('id_pembayaran', $idData)
                ->join('pendaftar', 'pembayaran.id_pendaftar', '=', 'pendaftar.id_pendaftar')
                ->value('pendaftar.id_user');
        } elseif ($idUser === null && $tabelTerkait === 'terima' && $idData) {
            $idUser = Terima::where('id_terima', $idData)
                ->join('pendaftar', 'terima.id_pendaftar', '=', 'pendaftar.id_pendaftar')
                ->value('pendaftar.id_user');
        }

        // Tentukan role aktor
        if ($roleOverride) {
            $role = $roleOverride;
        } elseif ($idAdmin) {
            $adminUser = auth('admin')->user();
            if ($adminUser && $adminUser->id_admin == $idAdmin) {
                $role = $adminUser->role;
            } else {
                $role = Admin::where('id_admin', $idAdmin)->value('role') ?? 'tu';
            }
        } elseif ($idUser) {
            $role = 'calon_siswa';
        } else {
            $role = 'sistem';
        }

        // Sanitasi data: pastikan tidak ada password atau token yang tersimpan
        $sanitizedOld = self::sanitizePayload($dataLama);
        $sanitizedNew = self::sanitizePayload($dataBaru);

        return self::create([
            'id_admin' => $idAdmin,
            'id_user' => $idUser,
            'role' => $role,
            'aksi' => strtoupper($aksi),
            'tabel_terkait' => $tabelTerkait,
            'id_data' => $idData,
            'deskripsi' => $deskripsi,
            'status' => strtoupper($status),
            'data_lama' => $sanitizedOld,
            'data_baru' => $sanitizedNew,
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'user_agent' => request()->userAgent() ?? 'CLI / Internal',
        ]);
    }

    /**
     * Sanitasi array untuk menghapus kata sandi atau token rahasia sebelum disimpan.
     */
    protected static function sanitizePayload(mixed $payload): mixed
    {
        if (!is_array($payload)) {
            return $payload;
        }

        $forbiddenKeys = ['password', 'password_confirmation', 'remember_token', 'token', 'secret', 'snap_token'];
        $clean = [];

        foreach ($payload as $key => $value) {
            if (in_array(strtolower((string) $key), $forbiddenKeys, true)) {
                $clean[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $clean[$key] = self::sanitizePayload($value);
            } else {
                $clean[$key] = $value;
            }
        }

        return $clean;
    }
}
