<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // Token verifikasi untuk kartu ujian pendaftar
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->string('verification_token', 64)->nullable()->unique()->after('tgl_daftar');
        });

        // Token verifikasi untuk SKL (surat kelulusan)
        Schema::table('terima', function (Blueprint $table) {
            $table->string('verification_token', 64)->nullable()->unique()->after('keterangan');
        });

        // Generate token untuk data yang sudah ada
        $pendaftars = \DB::table('pendaftar')->whereNull('verification_token')->get();
        foreach ($pendaftars as $p) {
            \DB::table('pendaftar')
                ->where('id_pendaftar', $p->id_pendaftar)
                ->update(['verification_token' => Str::random(32)]);
        }

        $terimas = \DB::table('terima')->whereNull('verification_token')->get();
        foreach ($terimas as $t) {
            \DB::table('terima')
                ->where('id_terima', $t->id_terima)
                ->update(['verification_token' => Str::random(32)]);
        }
    }

    public function down(): void
    {
        Schema::table('pendaftar', function (Blueprint $table) {
            $table->dropColumn('verification_token');
        });

        Schema::table('terima', function (Blueprint $table) {
            $table->dropColumn('verification_token');
        });
    }
};

