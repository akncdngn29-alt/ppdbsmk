<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->foreignId('id_pendaftar')->constrained('pendaftar', 'id_pendaftar')->onDelete('cascade');
            $table->foreignId('id_gelombang')->constrained('gelombang', 'id_gelombang');
            $table->foreignId('id_voucher')->nullable()->constrained('voucher_pendaftaran', 'id_voucher')->nullOnDelete();
            
            $table->string('kode_transaksi', 100)->unique();
            $table->string('snap_token', 255)->nullable();
            $table->decimal('nominal', 12, 2);
            $table->decimal('potongan', 12, 2)->default(0.00);
            $table->decimal('total_bayar', 12, 2);
            $table->enum('tipe_bayar', ['midtrans', 'tunai_tu'])->default('midtrans');
            $table->string('metode_pembayaran', 50)->nullable();
            $table->enum('status_transaksi', ['pending', 'settlement', 'expire', 'cancel'])->default('pending');
            $table->dateTime('waktu_transaksi')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};

