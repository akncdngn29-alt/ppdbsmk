<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penempatan_peserta', function (Blueprint $table) {
            $table->id('id_penempatan');
            $table->foreignId('id_jadwal')->constrained('jadwal_seleksi', 'id_jadwal')->onDelete('cascade');
            $table->foreignId('id_ruang')->constrained('ruang_seleksi', 'id_ruang')->onDelete('cascade');
            $table->foreignId('id_pendaftar')->constrained('pendaftar', 'id_pendaftar')->onDelete('cascade');
            $table->text('catatan')->nullable();
            $table->timestamps();

            // Satu peserta hanya bisa ada sekali per jadwal
            $table->unique(['id_jadwal', 'id_pendaftar'], 'uq_jadwal_pendaftar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penempatan_peserta');
    }
};
