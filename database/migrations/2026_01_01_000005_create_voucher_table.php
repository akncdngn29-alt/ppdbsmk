<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_pendaftaran', function (Blueprint $table) {
            $table->id('id_voucher');
            $table->string('kode_voucher', 50)->unique();
            $table->enum('tipe', ['nominal', 'persen'])->default('nominal');
            $table->decimal('nilai_potongan', 12, 2)->default(0.00);
            $table->integer('kuota_voucher')->default(100);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_pendaftaran');
    }
};

