<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id('id_pengumuman');
            $table->foreignId('id_admin')->constrained('admin', 'id_admin')->onDelete('cascade');
            $table->string('judul', 150);
            $table->text('isi_pengumuman');
            $table->enum('tipe', ['info', 'peringatan', 'penting', 'maintenance'])->default('info');
            $table->boolean('tampil_di_banner')->default(true);
            $table->boolean('status_aktif')->default(true);
            $table->dateTime('tgl_mulai');
            $table->dateTime('tgl_selesai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengumuman');
    }
};

