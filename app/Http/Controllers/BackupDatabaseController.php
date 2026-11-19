<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\LogAktivitas;
use Carbon\Carbon;

class BackupDatabaseController extends Controller
{
    protected string $backupDir = 'backups';

    /**
     * Dapatkan disk private storage untuk berkas pencadangan basis data.
     */
    private function disk()
    {
        return Storage::disk('local');
    }

    /**
     * Tampilkan daftar file backup basis data yang tersedia.
     */
    public function index()
    {
        if (!$this->disk()->exists($this->backupDir)) {
            $this->disk()->makeDirectory($this->backupDir);
        }

        $allFiles = $this->disk()->files($this->backupDir);
        $backups = [];

        foreach ($allFiles as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $filename = basename($file);
                $size = $this->disk()->size($file);
                $lastModified = $this->disk()->lastModified($file);

                $backups[] = [
                    'filename' => $filename,
                    'path' => $file,
                    'size' => $this->formatBytes($size),
                    'size_bytes' => $size,
                    'created_at' => Carbon::createFromTimestamp($lastModified),
                ];
            }
        }

        // Urutkan backup dari yang terbaru
        usort($backups, fn($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        $dbName = config('database.connections.mysql.database', 'ppdb_smk');
        $totalBackups = count($backups);

        return view('admin.backup.index', compact('backups', 'dbName', 'totalBackups'));
    }

    /**
     * Jalankan pembuatan file backup basis data baru (1-Klik).
     */
    public function createBackup()
    {
        $admin = auth('admin')->user();

        try {
            if (!$this->disk()->exists($this->backupDir)) {
                $this->disk()->makeDirectory($this->backupDir);
            }

            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $dbName = config('database.connections.mysql.database', 'ppdb_smk');
            $filename = "backup_{$dbName}_{$timestamp}.sql";
            $relativePath = "{$this->backupDir}/{$filename}";

            $sqlContent = $this->generateSqlDump($dbName);

            $this->disk()->put($relativePath, $sqlContent);
            $fileSizeBytes = $this->disk()->size($relativePath);

            LogAktivitas::log(
                'BACKUP_DATABASE',
                "{$admin->nama_admin} membuat file backup basis data: {$filename} (" . $this->formatBytes($fileSizeBytes) . ")",
                'pengaturan_sekolah',
                null,
                null,
                ['file' => $filename, 'size' => $fileSizeBytes],
                'SUCCESS',
                null,
                $admin->id_admin,
                $admin->role
            );

            return back()->with('success', "Pencadangan basis data berhasil dibuat: {$filename} (" . $this->formatBytes($fileSizeBytes) . ").");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat backup basis data: ' . $e->getMessage());
        }
    }

    /**
     * Unduh file backup SQL secara aman dari private storage.
     */
    public function download($filename)
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->role !== 'kepala_sekolah') {
            abort(403, 'Akses ditolak. Hanya Kepala Sekolah yang berwenang mengunduh berkas cadangan basis data.');
        }

        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            abort(400, 'Format nama file tidak sah.');
        }

        $safeFilename = basename($filename);
        $relativePath = "{$this->backupDir}/{$safeFilename}";

        if (!$this->disk()->exists($relativePath)) {
            abort(404, 'File backup tidak ditemukan.');
        }

        LogAktivitas::log(
            'UNDUH_BACKUP',
            "{$admin->nama_admin} mengunduh file backup basis data: {$safeFilename}",
            'pengaturan_sekolah',
            null,
            null,
            ['file' => $safeFilename],
            'SUCCESS',
            null,
            $admin->id_admin,
            $admin->role
        );

        return $this->disk()->download($relativePath, $safeFilename, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Pulihkan basis data dari file backup yang dipilih (Khusus Kepala Sekolah).
     */
    public function restore(Request $request, $filename)
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->role !== 'kepala_sekolah') {
            abort(403, 'Akses ditolak. Hanya Kepala Sekolah yang memiliki wewenang memulihkan basis data.');
        }

        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            abort(400, 'Format nama file tidak sah.');
        }

        $safeFilename = basename($filename);
        $relativePath = "{$this->backupDir}/{$safeFilename}";

        if (!$this->disk()->exists($relativePath)) {
            return back()->with('error', 'File backup yang dipilih tidak ditemukan.');
        }

        $request->validate([
            'konfirmasi' => 'required|in:PULIHKAN',
        ], [
            'konfirmasi.in' => 'Ketik kata "PULIHKAN" dengan huruf besar untuk mengonfirmasi pemulihan basis data.',
        ]);

        try {
            $sqlContent = $this->disk()->get($relativePath);

            // Eksekusi SQL dump dengan penonaktifan foreign key check sementara
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            DB::unprepared($sqlContent);
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

            LogAktivitas::log(
                'RESTORE_DATABASE',
                "{$admin->nama_admin} memulihkan basis data dari file: {$safeFilename}",
                'pengaturan_sekolah',
                null,
                null,
                ['file' => $safeFilename],
                'SUCCESS',
                null,
                $admin->id_admin,
                $admin->role
            );

            return back()->with('success', "Basis data berhasil dipulihkan dari file: {$safeFilename}.");

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            return back()->with('error', 'Gagal memulihkan basis data: ' . $e->getMessage());
        }
    }

    /**
     * Hapus file backup yang sudah tidak diperlukan (Khusus Kepala Sekolah).
     */
    public function destroy($filename)
    {
        $admin = auth('admin')->user();
        if (!$admin || $admin->role !== 'kepala_sekolah') {
            abort(403, 'Akses ditolak. Hanya Kepala Sekolah yang memiliki wewenang menghapus file cadangan basis data.');
        }

        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            abort(400, 'Format nama file tidak sah.');
        }

        $safeFilename = basename($filename);
        $relativePath = "{$this->backupDir}/{$safeFilename}";

        if ($this->disk()->exists($relativePath)) {
            $this->disk()->delete($relativePath);

            LogAktivitas::log(
                'HAPUS_BACKUP',
                "{$admin->nama_admin} menghapus file backup basis data: {$safeFilename}",
                'pengaturan_sekolah',
                null,
                null,
                ['file' => $safeFilename],
                'SUCCESS',
                null,
                $admin->id_admin,
                $admin->role
            );

            return back()->with('success', "File backup {$safeFilename} berhasil dihapus.");
        }

        return back()->with('error', 'File backup tidak ditemukan.');
    }

    /**
     * Generator dump SQL murni via PDO (Portabel & Handal di semua lingkungan OS).
     */
    protected function generateSqlDump(string $dbName): string
    {
        $pdo = DB::connection()->getPdo();
        $tables = [];

        $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $now = Carbon::now()->toDateTimeString();
        $out = "-- =========================================================\n"
             . "-- PPDB SMK ISLAM BINA KHALIFAH BANGSA (KOTA METRO, LAMPUNG)\n"
             . "-- Database SQL Backup Dump\n"
             . "-- Database: {$dbName}\n"
             . "-- Generated At: {$now}\n"
             . "-- =========================================================\n\n"
             . "SET FOREIGN_KEY_CHECKS=0;\n"
             . "SET SQL_MODE=\"NO_AUTO_VALUE_ON_ZERO\";\n"
             . "SET time_zone = \"+00:00\";\n\n";

        foreach ($tables as $table) {
            $createTableStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_NUM);
            $createTableSql = $createTableStmt[1] ?? '';

            $out .= "-- --------------------------------------------------------\n";
            $out .= "-- Struktur Tabel `{$table}`\n";
            $out .= "-- --------------------------------------------------------\n";
            $out .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $out .= "{$createTableSql};\n\n";

            // Ambil data isi tabel
            $rowsStmt = $pdo->query("SELECT * FROM `{$table}`");
            $rows = $rowsStmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $out .= "-- Dumping Data Tabel `{$table}`\n";
                $columns = array_keys($rows[0]);
                $colNames = implode('`, `', $columns);

                foreach (array_chunk($rows, 100) as $chunk) {
                    $valSets = [];
                    foreach ($chunk as $row) {
                        $escapedVals = array_map(function ($val) use ($pdo) {
                            if (is_null($val)) return 'NULL';
                            return $pdo->quote($val);
                        }, array_values($row));
                        $valSets[] = "(" . implode(', ', $escapedVals) . ")";
                    }
                    $out .= "INSERT INTO `{$table}` (`{$colNames}`) VALUES\n" . implode(",\n", $valSets) . ";\n";
                }
                $out .= "\n";
            }
        }

        $out .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $out .= "-- Dump Selesai.\n";

        return $out;
    }

    /**
     * Format byte ke satuan yang mudah dibaca (KB, MB).
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

