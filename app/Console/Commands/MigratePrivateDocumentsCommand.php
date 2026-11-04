<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\BerkasPendaftar;
use App\Models\PrestasiPendaftar;

class MigratePrivateDocumentsCommand extends Command
{
    protected $signature = 'ppdb:migrate-documents {--dry-run : Simulasi tanpa memindahkan file}';
    protected $description = 'Migrasi dokumen pendaftar dari public storage ke private storage (UU PDP)';

    public function handle(): int
    {
        $this->info('=== MEMULAI INVENTARISASI & MIGRASI DOKUMEN PENDAFTAR (UU PDP) ===');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Mode DRY-RUN aktif: Tidak ada berkas fisik yang disalin.');
        }

        $publicDisk = Storage::disk('public');
        $privateDisk = Storage::disk('local');

        $successCount = 0;
        $failedCount = 0;
        $alreadyPrivateCount = 0;

        // 1. Cek Berkas Pendaftar
        $this->info('Memeriksa tabel berkas_pendaftar...');
        $berkasList = BerkasPendaftar::all();

        foreach ($berkasList as $b) {
            $path = $b->path_file;
            if ($privateDisk->exists($path)) {
                $alreadyPrivateCount++;
                continue;
            }

            if ($publicDisk->exists($path)) {
                $this->line("Ditemukan di public: {$path}");
                if (!$isDryRun) {
                    $content = $publicDisk->get($path);
                    $privateDisk->put($path, $content);

                    // Verifikasi integritas
                    if ($privateDisk->exists($path) && $privateDisk->size($path) === $publicDisk->size($path)) {
                        $this->info(" -> Berhasil disalin ke private storage.");
                        $successCount++;
                    } else {
                        $this->error(" -> Gagal memverifikasi salinan file: {$path}");
                        $failedCount++;
                    }
                } else {
                    $successCount++;
                }
            } else {
                $this->comment("File fisik belum ada (data demo/mock): {$path}");
            }
        }

        // 2. Cek Prestasi Pendaftar
        $this->info('Memeriksa tabel prestasi_pendaftar...');
        $prestasiList = PrestasiPendaftar::all();

        foreach ($prestasiList as $p) {
            $path = $p->berkas_sertifikat;
            if ($privateDisk->exists($path)) {
                $alreadyPrivateCount++;
                continue;
            }

            if ($publicDisk->exists($path)) {
                $this->line("Ditemukan di public: {$path}");
                if (!$isDryRun) {
                    $content = $publicDisk->get($path);
                    $privateDisk->put($path, $content);

                    if ($privateDisk->exists($path) && $privateDisk->size($path) === $publicDisk->size($path)) {
                        $this->info(" -> Berhasil disalin ke private storage.");
                        $successCount++;
                    } else {
                        $this->error(" -> Gagal memverifikasi salinan file: {$path}");
                        $failedCount++;
                    }
                } else {
                    $successCount++;
                }
            } else {
                $this->comment("File fisik belum ada (data demo/mock): {$path}");
            }
        }

        $this->newLine();
        $this->info('=== REKAPITULASI MIGRASI DOKUMEN ===');
        $this->table(
            ['Kategori', 'Jumlah File'],
            [
                ['Sudah ada di Private Storage', $alreadyPrivateCount],
                ['Berhasil disalin ke Private Storage', $successCount],
                ['Gagal disalin', $failedCount],
            ]
        );

        $this->info('Catatan: File sumber lama tidak dihapus secara otomatis demi keamanan data.');
        return 0;
    }
}

