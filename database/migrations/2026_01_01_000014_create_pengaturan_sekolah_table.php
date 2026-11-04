<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_sekolah', function (Blueprint $table) {
            $table->id('id_pengaturan');
            $table->string('nama_sekolah', 150)->default('SMK Islam Bina Khalifah Bangsa');
            $table->string('npsn', 20)->default('69773555');
            $table->string('ijin_operasional', 100)->default('115/KPTS/D.3/03/2013');
            $table->text('alamat')->nullable();
            $table->string('kota', 50)->default('Kota Metro');
            $table->string('provinsi', 50)->default('Lampung');
            $table->string('no_telp', 30)->default('081234567890');
            $table->string('email', 100)->default('info@smkib.com');
            $table->string('website', 100)->default('https://smkib.com');
            $table->string('nama_kepsek', 100)->default('Asliman Puja Kesuma, S.Pd');
            $table->string('nip_kepsek', 50)->nullable();
            $table->string('logo', 255)->nullable();
            $table->string('stempel', 255)->nullable();
            $table->string('ttd_kepsek', 255)->nullable();
            $table->string('midtrans_server_key', 255)->nullable();
            $table->string('midtrans_client_key', 255)->nullable();
            $table->boolean('midtrans_is_production')->default(false);
            $table->string('whatsapp_api_token', 255)->nullable();
            $table->boolean('mode_maintenance')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sekolah');
    }
};

