<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\BerkasPendaftar;
use App\Models\PrestasiPendaftar;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DokumenController extends Controller
{
    /**
     * Tampilkan atau unduh berkas pendaftar dengan otorisasi ketat (UU PDP).
     */
    public function viewBerkas($id): BinaryFileResponse
    {
        $this->authorizeAccess();

        $berkas = BerkasPendaftar::with('pendaftar')->findOrFail($id);

        // Jika login sebagai Calon Siswa, verifikasi kepemilikan dokumen
        if (auth('web')->check()) {
            $user = auth('web')->user();
            if ($berkas->pendaftar->id_user !== $user->id_user) {
                abort(403, 'Akses ditolak. Anda hanya diizinkan mengakses dokumen milik Anda sendiri.');
            }
        }

        return $this->streamDocument($berkas->path_file, $berkas->nama_file);
    }

    /**
     * Tampilkan atau unduh sertifikat prestasi pendaftar dengan otorisasi.
     */
    public function viewPrestasi($id): BinaryFileResponse
    {
        $this->authorizeAccess();

        $prestasi = PrestasiPendaftar::with('pendaftar')->findOrFail($id);

        if (auth('web')->check()) {
            $user = auth('web')->user();
            if ($prestasi->pendaftar->id_user !== $user->id_user) {
                abort(403, 'Akses ditolak. Anda hanya diizinkan mengakses sertifikat prestasi milik Anda sendiri.');
            }
        }

        $namaFile = basename($prestasi->berkas_sertifikat);
        return $this->streamDocument($prestasi->berkas_sertifikat, $namaFile);
    }

    /**
     * Validasi autentikasi pengguna (web siswa atau admin staf).
     */
    private function authorizeAccess(): void
    {
        $isStudent = auth('web')->check();
        $isAdmin = auth('admin')->check();

        if (!$isStudent && !$isAdmin) {
            abort(401, 'Silakan login terlebih dahulu untuk mengakses dokumen pendaftar.');
        }

        // Jika admin, pastikan memiliki role 'tu' atau 'kepala_sekolah'
        if ($isAdmin) {
            $admin = auth('admin')->user();
            if (!in_array($admin->role, ['tu', 'kepala_sekolah'])) {
                abort(403, 'Kewenangan tidak memadai.');
            }
        }
    }

    /**
     * Stream file fisik secara aman dari private storage dengan proteksi Path Traversal.
     */
    private function streamDocument(string $path, string $downloadName): BinaryFileResponse
    {
        // Proteksi Path Traversal Attack (mencegah karakter '..' atau absolute root path traversal)
        if (str_contains($path, '..') || str_starts_with($path, '/') || str_starts_with($path, '\\')) {
            abort(400, 'Format path dokumen tidak sah.');
        }

        // Sanitasi nama file download
        $cleanDownloadName = basename($downloadName);

        // 1. Cek di private disk (storage/app/private/)
        $privateDisk = Storage::disk('local');
        if ($privateDisk->exists($path)) {
            $fullPath = $privateDisk->path($path);
            return response()->file($fullPath, [
                'Content-Disposition' => 'inline; filename="' . addslashes($cleanDownloadName) . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        // 2. Fallback cek di public disk jika dokumen lama belum dimigrasi
        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($path)) {
            $fullPath = $publicDisk->path($path);
            return response()->file($fullPath, [
                'Content-Disposition' => 'inline; filename="' . addslashes($cleanDownloadName) . '"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        abort(404, 'File dokumen tidak ditemukan pada sistem penyimpanan.');
    }
}

