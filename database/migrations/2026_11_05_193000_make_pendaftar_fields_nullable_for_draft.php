<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop foreign key id_jurusan_1 sementara untuk modifikasi menjadi nullable
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->dropForeign('pendaftar_id_jurusan_1_foreign');
        });

        // 2. Modifikasi kolom id_jurusan_1 menjadi nullable dan pasang kembali foreign key
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->unsignedBigInteger('id_jurusan_1')->nullable()->change();
            $table->foreign('id_jurusan_1')->references('id_jurusan')->on('jurusan')->nullOnDelete();
        });

        // 3. Modifikasi kolom teks/string/numerik biasa menjadi nullable
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->string('tempat_lahir', 100)->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->change();
            $table->string('agama', 50)->nullable()->default('Islam')->change();
            $table->integer('anak_ke')->nullable()->default(1)->change();
            $table->integer('jml_saudara')->nullable()->default(0)->change();
            $table->string('hp_siswa', 20)->nullable()->change();
            $table->text('almt_siswa')->nullable()->change();
            $table->string('berat_badan', 10)->nullable()->default('50')->change();
            $table->string('tinggi_badan', 10)->nullable()->default('160')->change();
            $table->string('golongan_darah', 5)->nullable()->default('A')->change();
            $table->string('asal_sekolah', 100)->nullable()->change();
            $table->string('nama_ayah', 100)->nullable()->change();
            $table->string('nama_ibu', 100)->nullable()->change();
            $table->text('almt_ortu')->nullable()->change();
            $table->string('hp_ortu', 20)->nullable()->change();
            $table->string('kerja_ayah', 100)->nullable()->change();
            $table->string('kerja_ibu', 100)->nullable()->change();
            $table->string('penghasilan_ortu', 50)->nullable()->change();
            $table->integer('tanggungan_anak')->nullable()->default(1)->change();
        });

        // 4. Modifikasi kolom ENUM agar mengizinkan NULL
        DB::statement("ALTER TABLE pendaftar MODIFY COLUMN jenis_kelamin ENUM('Laki-laki', 'Perempuan') NULL");
        DB::statement("ALTER TABLE pendaftar MODIFY COLUMN jalur_pendaftaran ENUM('Reguler', 'Prestasi', 'Afirmasi', 'Zonasi') NULL DEFAULT 'Reguler'");
        DB::statement("ALTER TABLE pendaftar MODIFY COLUMN ukuran_seragam ENUM('S', 'M', 'L', 'XL', 'XXL', 'Custom') NULL DEFAULT 'L'");
        DB::statement("ALTER TABLE pendaftar MODIFY COLUMN ukuran_wearpack ENUM('S', 'M', 'L', 'XL', 'XXL', 'Custom') NULL DEFAULT 'L'");
    }

    public function down(): void
    {
        // Revert ENUM
        DB::statement("ALTER TABLE pendaftar MODIFY COLUMN jenis_kelamin ENUM('Laki-laki', 'Perempuan') NOT NULL");
        DB::statement("ALTER TABLE pendaftar MODIFY COLUMN jalur_pendaftaran ENUM('Reguler', 'Prestasi', 'Afirmasi', 'Zonasi') NOT NULL DEFAULT 'Reguler'");
        DB::statement("ALTER TABLE pendaftar MODIFY COLUMN ukuran_seragam ENUM('S', 'M', 'L', 'XL', 'XXL', 'Custom') NOT NULL DEFAULT 'L'");
        DB::statement("ALTER TABLE pendaftar MODIFY COLUMN ukuran_wearpack ENUM('S', 'M', 'L', 'XL', 'XXL', 'Custom') NOT NULL DEFAULT 'L'");

        Schema::table('pendaftar', function (Blueprint $table) {
            $table->string('tempat_lahir', 100)->nullable(false)->change();
            $table->date('tanggal_lahir')->nullable(false)->change();
            $table->string('agama', 50)->nullable(false)->default('Islam')->change();
            $table->integer('anak_ke')->nullable(false)->default(1)->change();
            $table->integer('jml_saudara')->nullable(false)->default(0)->change();
            $table->string('hp_siswa', 20)->nullable(false)->change();
            $table->text('almt_siswa')->nullable(false)->change();
            $table->string('berat_badan', 10)->nullable(false)->default('50')->change();
            $table->string('tinggi_badan', 10)->nullable(false)->default('160')->change();
            $table->string('golongan_darah', 5)->nullable(false)->default('A')->change();
            $table->string('asal_sekolah', 100)->nullable(false)->change();
            $table->string('nama_ayah', 100)->nullable(false)->change();
            $table->string('nama_ibu', 100)->nullable(false)->change();
            $table->text('almt_ortu')->nullable(false)->change();
            $table->string('hp_ortu', 20)->nullable(false)->change();
            $table->string('kerja_ayah', 100)->nullable(false)->change();
            $table->string('kerja_ibu', 100)->nullable(false)->change();
            $table->string('penghasilan_ortu', 50)->nullable(false)->change();
            $table->integer('tanggungan_anak')->nullable(false)->default(1)->change();

            $table->dropForeign(['id_jurusan_1']);
            $table->unsignedBigInteger('id_jurusan_1')->nullable(false)->change();
            $table->foreign('id_jurusan_1')->references('id_jurusan')->on('jurusan');
        });
    }
};

