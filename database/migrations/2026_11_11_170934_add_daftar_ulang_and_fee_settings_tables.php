<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom biaya daftar ulang & keterangan di tabel pengaturan_sekolah
        Schema::table('pengaturan_sekolah', function (Blueprint $table) {
            if (!Schema::hasColumn('pengaturan_sekolah', 'biaya_daftar_ulang')) {
                $table->decimal('biaya_daftar_ulang', 12, 2)->default(1500000.00)->after('mode_maintenance');
            }
            if (!Schema::hasColumn('pengaturan_sekolah', 'keterangan_daftar_ulang')) {
                $table->text('keterangan_daftar_ulang')->nullable()->after('biaya_daftar_ulang');
            }
            if (!Schema::hasColumn('pengaturan_sekolah', 'instruksi_daftar_ulang')) {
                $table->text('instruksi_daftar_ulang')->nullable()->after('keterangan_daftar_ulang');
            }
        });

        // 2. Tambah kolom jenis_pembayaran di tabel pembayaran
        Schema::table('pembayaran', function (Blueprint $table) {
            if (!Schema::hasColumn('pembayaran', 'jenis_pembayaran')) {
                $table->enum('jenis_pembayaran', ['pendaftaran', 'daftar_ulang'])->default('pendaftaran')->after('id_voucher');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengaturan_sekolah', function (Blueprint $table) {
            if (Schema::hasColumn('pengaturan_sekolah', 'biaya_daftar_ulang')) {
                $table->dropColumn('biaya_daftar_ulang');
            }
            if (Schema::hasColumn('pengaturan_sekolah', 'keterangan_daftar_ulang')) {
                $table->dropColumn('keterangan_daftar_ulang');
            }
            if (Schema::hasColumn('pengaturan_sekolah', 'instruksi_daftar_ulang')) {
                $table->dropColumn('instruksi_daftar_ulang');
            }
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            if (Schema::hasColumn('pembayaran', 'jenis_pembayaran')) {
                $table->dropColumn('jenis_pembayaran');
            }
        });
    }
};
