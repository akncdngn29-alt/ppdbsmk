<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'db:backup {--filename= : Nama kustom untuk file backup}';
    protected $description = 'Membuat backup dump SQL basis data PPDB SMK secara aman dan portabel';

    public function handle(): int
    {
        $this->info('Memulai pencadangan basis data PPDB...');

        try {
            $dbName = config('database.connections.mysql.database', 'ppdb_smk');
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = $this->option('filename') ?: "backup_{$dbName}_{$timestamp}.sql";
            $relativePath = "backups/{$filename}";

            if (!Storage::exists('backups')) {
                Storage::makeDirectory('backups');
            }

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

                $rowsStmt = $pdo->query("SELECT * FROM `{$table}`");
                $rows = $rowsStmt->fetchAll(\PDO::FETCH_ASSOC);

                if (!empty($rows)) {
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

            Storage::put($relativePath, $out);
            $size = Storage::size($relativePath);

            $this->info("Backup berhasil dibuat: storage/app/{$relativePath} (" . round($size / 1024, 2) . " KB)");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Gagal mencadangkan basis data: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
