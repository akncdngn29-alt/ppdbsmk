<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id('id_log');
            $table->foreignId('id_admin')->nullable()->constrained('admin', 'id_admin')->nullOnDelete();
            $table->foreignId('id_user')->nullable()->constrained('user', 'id_user')->nullOnDelete();
            $table->string('aksi', 50);
            $table->string('tabel_terkait', 50)->nullable();
            $table->unsignedBigInteger('id_data')->nullable();
            $table->string('deskripsi', 255);
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};

