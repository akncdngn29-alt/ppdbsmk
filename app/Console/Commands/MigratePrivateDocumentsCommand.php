<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\BerkasPendaftar;
use App\Models\PrestasiPendaftar;

class MigratePrivateDocumentsCommand extends Command
{
    protected $signature = 'ppdb:migrate-documents 
                            {--dry-run : Jalankan simulasi inventarisasi tanpa memindahkan berkas fisik}
                            {--prune-public : Hapus berkas lama dari public storage (Wajib menyertakan --force)}
                            {--force : Konfirmasi persetujuan eksplisit tindakan perubahan/penghapusan}';

    protected $description = 'Migrasi dokumen pendaftar dari public storage ke private storage (UU PDP No. 27/2022)';

    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('  INVENTARISASI & MIGRASI DOKUMEN PENDAFTAR (UU PDP NO. 27/2022)');
        $this->info('================================================================');

        $isDryRun = (bool) $this->option('dry-run');
        $prunePublic = (bool) $this->option('prune-public');
        $force = (bool) $this->option('force');

        if ($isDryRun) {
            $this->warn('>> MODE DRY-RUN AKTIF: Tidak ada file fisik yang disalin atau diubah.');
        }

        $publicDisk = Storage::disk('public');
        $privateDisk = Storage::disk('local');

        $totalDbRecords = 0;
        $prePrivateCount = 0;
        $prePublicCount = 0;
        $missingCount = 0;
        $migratedCount = 0;
        $failedCount = 0;
        $pathNormalizedCount = 0;

        // 1. Proses Berkas Pendaftar
        $this->line('<fg=cyan>1. Memeriksa entri berkas_pendaftar...</fg=cyan>');
        $berkasList = BerkasPendaftar::all();
        $totalDbRecords += $berkasList->count();

        foreach ($berkasList as $b) {
            $path = $this->normalizePath($b->path_file);
            if ($path !== $b->path_file) {
                if (!$isDryRun) {
                    $b->path_file = $path;
                    $b->save();
                }
                $pathNormalizedCount++;
            }

            $inPrivate = $privateDisk->exists($path);
            $inPublic = $publicDisk->exists($path);

            if ($inPrivate) {
                $prePrivateCount++;
            }

            if ($inPublic) {
                $prePublicCount++;
            }

            if (!$inPrivate && !$inPublic) {
                $missingCount++;
                continue;
            }

            // Jika ada di public namun belum ada di private -> salin
            if ($inPublic && !$inPrivate) {
                $this->line(" - Menyalin berkas: {$path}");
                if (!$isDryRun) {
                    try {
                        $content = $publicDisk->get($path);
                        $privateDisk->put($path, $content);

                        if ($privateDisk->exists($path) && $privateDisk->size($path) === $publicDisk->size($path)) {
                            $migratedCount++;
                        } else {
                            $this->error("   [GAGAL] Ukuran file tidak cocok: {$path}");
                            $failedCount++;
                        }
                    } catch (\Throwable $e) {
                        $this->error("   [ERROR] " . $e->getMessage());
                        $failedCount++;
                    }
                } else {
                    $migratedCount++;
                }
            }
        }

        // 2. Proses Prestasi Pendaftar
        $this->line('<fg=cyan>2. Memeriksa entri prestasi_pendaftar...</fg=cyan>');
        $prestasiList = PrestasiPendaftar::all();
        $totalDbRecords += $prestasiList->count();

        foreach ($prestasiList as $p) {
            $path = $this->normalizePath($p->berkas_sertifikat);
            if ($path !== $p->berkas_sertifikat) {
                if (!$isDryRun) {
                    $p->berkas_sertifikat = $path;
                    $p->save();
                }
                $pathNormalizedCount++;
            }

            $inPrivate = $privateDisk->exists($path);
            $inPublic = $publicDisk->exists($path);

            if ($inPrivate) {
                $prePrivateCount++;
            }

            if ($inPublic) {
                $prePublicCount++;
            }

            if (!$inPrivate && !$inPublic) {
                $missingCount++;
                continue;
            }

            if ($inPublic && !$inPrivate) {
                $this->line(" - Menyalin sertifikat prestasi: {$path}");
                if (!$isDryRun) {
                    try {
                        $content = $publicDisk->get($path);
                        $privateDisk->put($path, $content);

                        if ($privateDisk->exists($path) && $privateDisk->size($path) === $publicDisk->size($path)) {
                            $migratedCount++;
                        } else {
                            $this->error("   [GAGAL] Ukuran file tidak cocok: {$path}");
                            $failedCount++;
                        }
                    } catch (\Throwable $e) {
                        $this->error("   [ERROR] " . $e->getMessage());
                        $failedCount++;
                    }
                } else {
                    $migratedCount++;
                }
            }
        }

        // Hitung total berkas fisik setelah migrasi
        $postPrivateCount = $isDryRun ? ($prePrivateCount + $migratedCount) : 0;
        if (!$isDryRun) {
            foreach ($berkasList as $b) {
                if ($privateDisk->exists($this->normalizePath($b->path_file))) {
                    $postPrivateCount++;
                }
            }
            foreach ($prestasiList as $p) {
                if ($privateDisk->exists($this->normalizePath($p->berkas_sertifikat))) {
                    $postPrivateCount++;
                }
            }
        }

        // Tampilkan Laporan Jumlah File Sebelum dan Sesudah Migrasi
        $this->newLine();
        $this->info('=== LAPORAN INVENTARISASI & HASIL MIGRASI DOKUMEN ===');
        $this->table(
            ['Parameter Pemeriksaan', 'Sebelum Migrasi', 'Sesudah Migrasi'],
            [
                ['Total Record Terdata di Database', $totalDbRecords, $totalDbRecords],
                ['Path Database Dinormalisasi', '-', $pathNormalizedCount],
                ['Berkas Fisik di Private Storage', $prePrivateCount, $postPrivateCount],
                ['Berkas Fisik di Public Storage', $prePublicCount, $prePublicCount],
                ['Berkas Berhasil Disalin', '-', $migratedCount],
                ['Berkas Gagal Disalin', '-', $failedCount],
                ['Berkas Belum Ada Fisik (Data Demo/Mock)', $missingCount, $missingCount],
            ]
        );

        $this->comment('Kebijakan Perlindungan: Dokumen lama di public storage TIDAK dihapus otomatis.');

        // Opsional: Penghapusan file lama hanya jika --prune-public dan --force diberikan
        if ($prunePublic) {
            if (!$force) {
                $this->error('PERINGATAN KEAMANAN: Penghapusan dokumen publik memerlukan opsi konfirmasi eksplisit --force.');
                return 1;
            }

            if ($failedCount > 0) {
                $this->error('PEMBERSIHAN DIBATALKAN: Terdapat berkas yang gagal disalin. Harap perbaiki sebelum menghapus berkas publik.');
                return 1;
            }

            $this->warn('Menjalankan pembersihan berkas publik yang telah terverifikasi aman di private storage...');
            $prunedCount = 0;

            foreach ($berkasList as $b) {
                $path = $this->normalizePath($b->path_file);
                if ($privateDisk->exists($path) && $publicDisk->exists($path)) {
                    $publicDisk->delete($path);
                    $prunedCount++;
                }
            }

            foreach ($prestasiList as $p) {
                $path = $this->normalizePath($p->berkas_sertifikat);
                if ($privateDisk->exists($path) && $publicDisk->exists($path)) {
                    $publicDisk->delete($path);
                    $prunedCount++;
                }
            }

            $this->info("Pembersihan selesai: {$prunedCount} berkas lama telah dihapus dari public storage.");
        }

        return 0;
    }

    /**
     * Normalisasi path relatif database (menghilangkan prefix publik yang tidak perlu).
     */
    private function normalizePath(?string $path): string
    {
        if (!$path) {
            return '';
        }

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        return $path;
    }
}
