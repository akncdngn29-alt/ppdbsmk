<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            if (!Schema::hasColumn('log_aktivitas', 'role')) {
                $table->string('role', 50)->nullable()->after('id_user')->index();
            }
            if (!Schema::hasColumn('log_aktivitas', 'status')) {
                $table->string('status', 50)->default('SUCCESS')->after('deskripsi')->index();
            }
        });

        // Backfill data lama agar kompatibel
        try {
            DB::table('log_aktivitas')->whereNull('status')->update(['status' => 'SUCCESS']);

            DB::table('log_aktivitas')
                ->whereNotNull('id_admin')
                ->whereNull('role')
                ->update([
                    'role' => DB::raw("(SELECT role FROM admin WHERE admin.id_admin = log_aktivitas.id_admin LIMIT 1)")
                ]);

            DB::table('log_aktivitas')
                ->whereNotNull('id_user')
                ->whereNull('id_admin')
                ->whereNull('role')
                ->update(['role' => 'calon_siswa']);

            // Jika ada yang belum memiliki role, defaultkan ke 'sistem'
            DB::table('log_aktivitas')
                ->whereNull('role')
                ->update(['role' => 'sistem']);
        } catch (\Throwable $e) {
            // Abaikan jika tabel kosong atau subquery belum dapat dijalankan
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            if (Schema::hasColumn('log_aktivitas', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('log_aktivitas', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

