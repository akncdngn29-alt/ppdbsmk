<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gelombang', function (Blueprint $table) {
            $table->id('id_gelombang');
            $table->string('nama_gelombang', 100);
            $table->string('tahun_ajaran', 20)->default('2026/2027');
            $table->date('tgl_buka');
            $table->date('tgl_tutup');
            $table->decimal('biaya_pendaftaran', 12, 2)->default(0.00);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gelombang');
    }
};

