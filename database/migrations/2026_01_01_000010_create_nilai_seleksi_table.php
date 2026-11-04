<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_seleksi', function (Blueprint $table) {
            $table->id('id_nilai');
            $table->foreignId('id_pendaftar')->constrained('pendaftar', 'id_pendaftar')->onDelete('cascade');
            $table->decimal('nilai_rapor', 5, 2)->default(0.00);
            $table->decimal('nilai_wawancara', 5, 2)->default(0.00);
            $table->enum('tes_buta_warna', ['Normal', 'Parsial', 'Total'])->default('Normal');
            $table->decimal('total_nilai', 5, 2)->default(0.00);
            $table->text('catatan_tes')->nullable();
            $table->string('rekomendasi_jurusan', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_seleksi');
    }
};

