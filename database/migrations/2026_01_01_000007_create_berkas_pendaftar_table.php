<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berkas_pendaftar', function (Blueprint $table) {
            $table->id('id_berkas');
            $table->foreignId('id_pendaftar')->constrained('pendaftar', 'id_pendaftar')->onDelete('cascade');
            $table->enum('jenis_berkas', ['ijazah', 'kk', 'pasfoto', 'akta', 'sertifikat', 'lainnya']);
            $table->string('nama_file', 255);
            $table->string('path_file', 255);
            $table->enum('status_berkas', ['Menunggu', 'Valid', 'Tidak Valid'])->default('Menunggu');
            $table->string('catatan', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_pendaftar');
    }
};

