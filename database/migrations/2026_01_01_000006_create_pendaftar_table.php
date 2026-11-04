<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftar', function (Blueprint $table) {
            $table->id('id_pendaftar');
            $table->foreignId('id_user')->constrained('user', 'id_user')->onDelete('cascade');
            $table->foreignId('id_gelombang')->constrained('gelombang', 'id_gelombang');
            $table->foreignId('id_jurusan_1')->constrained('jurusan', 'id_jurusan');
            $table->foreignId('id_jurusan_2')->nullable()->constrained('jurusan', 'id_jurusan')->nullOnDelete();
            
            $table->string('no_pendaftaran', 30)->unique();
            $table->string('nisn', 10);
            $table->string('nama', 100);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('agama', 50)->default('Islam');
            
            $table->integer('anak_ke')->default(1);
            $table->integer('jml_saudara')->default(0);
            $table->string('hp_siswa', 20);
            $table->text('almt_siswa');
            $table->string('berat_badan', 10)->default('50');
            $table->string('tinggi_badan', 10)->default('160');
            $table->string('golongan_darah', 5)->default('A');
            
            $table->string('asal_sekolah', 100);
            $table->text('alamat_sekolah')->nullable();
            
            $table->string('nama_ayah', 100);
            $table->string('nama_ibu', 100);
            $table->string('nama_wali', 100)->nullable();
            $table->text('almt_ortu');
            $table->string('hp_ortu', 20);
            $table->string('kerja_ayah', 100);
            $table->string('kerja_ibu', 100);
            $table->string('kerja_wali', 100)->nullable();
            $table->string('penghasilan_ortu', 50);
            $table->integer('tanggungan_anak')->default(1);
            
            $table->enum('jalur_pendaftaran', ['Reguler', 'Prestasi', 'Afirmasi', 'Zonasi'])->default('Reguler');
            $table->enum('ukuran_seragam', ['S', 'M', 'L', 'XL', 'XXL', 'Custom'])->default('L');
            $table->enum('ukuran_wearpack', ['S', 'M', 'L', 'XL', 'XXL', 'Custom'])->default('L');
            $table->text('pilihan_ekskul')->nullable();
            
            $table->enum('status_pembayaran', ['Pending', 'Paid', 'Free'])->default('Pending');
            $table->enum('status_verifikasi', ['Menunggu', 'Terverifikasi', 'Ditolak'])->default('Menunggu');
            $table->text('catatan_verifikasi')->nullable();
            $table->enum('status_kelulusan', ['Diproses', 'Diterima', 'Cadangan', 'Tidak Lolos'])->default('Diproses');
            $table->enum('status_daftar_ulang', ['Belum', 'Selesai', 'Mengundurkan Diri'])->default('Belum');
            $table->boolean('is_locked')->default(false);
            
            $table->dateTime('tgl_daftar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftar');
    }
};

