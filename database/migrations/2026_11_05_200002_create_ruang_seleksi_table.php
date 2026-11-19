<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruang_seleksi', function (Blueprint $table) {
            $table->id('id_ruang');
            $table->string('kode_ruang', 20)->unique();
            $table->string('nama_ruang', 100);
            $table->string('gedung', 100)->nullable();
            $table->unsignedInteger('kapasitas')->default(30);
            $table->text('keterangan')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruang_seleksi');
    }
};
