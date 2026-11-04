<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestasi_pendaftar', function (Blueprint $table) {
            $table->id('id_prestasi');
            $table->foreignId('id_pendaftar')->constrained('pendaftar', 'id_pendaftar')->onDelete('cascade');
            $table->string('nama_prestasi', 150);
            $table->enum('tingkat', ['Kecamatan', 'Kabupaten/Kota', 'Provinsi', 'Nasional', 'Internasional']);
            $table->enum('kategori', ['Keagamaan/Tahfiz', 'Akademik', 'Olahraga', 'Seni/Budaya']);
            $table->string('peringkat', 50);
            $table->year('tahun');
            $table->string('penyelenggara', 150);
            $table->string('berkas_sertifikat', 255);
            $table->integer('skor_bobot')->default(0);
            $table->enum('status_verifikasi', ['Menunggu', 'Valid', 'Ditolak'])->default('Menunggu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestasi_pendaftar');
    }
};

