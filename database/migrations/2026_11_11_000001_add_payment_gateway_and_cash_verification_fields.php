<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom merchant_id dan is_active pada tabel pengaturan_sekolah
        Schema::table('pengaturan_sekolah', function (Blueprint $table) {
            if (!Schema::hasColumn('pengaturan_sekolah', 'midtrans_merchant_id')) {
                $table->string('midtrans_merchant_id', 100)->nullable()->after('midtrans_client_key');
            }
            if (!Schema::hasColumn('pengaturan_sekolah', 'midtrans_is_active')) {
                $table->boolean('midtrans_is_active')->default(true)->after('midtrans_is_production');
            }
        });

        // 2. Tambah kolom id_admin_verifikator dan waktu_konfirmasi_tu pada tabel pembayaran
        Schema::table('pembayaran', function (Blueprint $table) {
            if (!Schema::hasColumn('pembayaran', 'id_admin_verifikator')) {
                $table->foreignId('id_admin_verifikator')->nullable()->after('id_gelombang')->constrained('admin', 'id_admin')->nullOnDelete();
            }
            if (!Schema::hasColumn('pembayaran', 'waktu_konfirmasi_tu')) {
                $table->timestamp('waktu_konfirmasi_tu')->nullable()->after('waktu_transaksi');
            }
        });

        // 3. Perluas opsi status_berkas pada tabel berkas_pendaftar jika menggunakan MySQL
        try {
            DB::statement("ALTER TABLE berkas_pendaftar MODIFY COLUMN status_berkas ENUM('Menunggu', 'Valid', 'Tidak Valid', 'Perlu Perbaikan') NOT NULL DEFAULT 'Menunggu'");
        } catch (\Throwable $e) {
            // Abaikan jika database engine tidak mendukung ALTER ENUM secara native
        }
    }

    public function down(): void
    {
        Schema::table('pengaturan_sekolah', function (Blueprint $table) {
            if (Schema::hasColumn('pengaturan_sekolah', 'midtrans_merchant_id')) {
                $table->dropColumn('midtrans_merchant_id');
            }
            if (Schema::hasColumn('pengaturan_sekolah', 'midtrans_is_active')) {
                $table->dropColumn('midtrans_is_active');
            }
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            if (Schema::hasColumn('pembayaran', 'id_admin_verifikator')) {
                $table->dropForeign(['id_admin_verifikator']);
                $table->dropColumn('id_admin_verifikator');
            }
            if (Schema::hasColumn('pembayaran', 'waktu_konfirmasi_tu')) {
                $table->dropColumn('waktu_konfirmasi_tu');
            }
        });
    }
};

