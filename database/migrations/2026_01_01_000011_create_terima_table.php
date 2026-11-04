<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('terima', function (Blueprint $table) {
            $table->id('id_terima');
            $table->foreignId('id_pendaftar')->unique()->constrained('pendaftar', 'id_pendaftar')->onDelete('cascade');
            $table->foreignId('id_jurusan')->constrained('jurusan', 'id_jurusan');
            $table->foreignId('id_admin_pengesah')->nullable()->constrained('admin', 'id_admin')->nullOnDelete();
            $table->string('no_surat_kelulusan', 100)->unique();
            $table->date('tgl_diterima');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terima');
    }
};

