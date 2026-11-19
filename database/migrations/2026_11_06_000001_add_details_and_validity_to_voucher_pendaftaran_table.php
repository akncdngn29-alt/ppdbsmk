<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voucher_pendaftaran', function (Blueprint $table) {
            if (!Schema::hasColumn('voucher_pendaftaran', 'nama_voucher')) {
                $table->string('nama_voucher', 100)->nullable()->after('kode_voucher');
            }
            if (!Schema::hasColumn('voucher_pendaftaran', 'berlaku_mulai')) {
                $table->date('berlaku_mulai')->nullable()->after('kuota_terpakai');
            }
            if (!Schema::hasColumn('voucher_pendaftaran', 'berlaku_sampai')) {
                $table->date('berlaku_sampai')->nullable()->after('berlaku_mulai');
            }
            if (!Schema::hasColumn('voucher_pendaftaran', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('berlaku_sampai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('voucher_pendaftaran', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('voucher_pendaftaran', 'deskripsi')) $cols[] = 'deskripsi';
            if (Schema::hasColumn('voucher_pendaftaran', 'berlaku_sampai')) $cols[] = 'berlaku_sampai';
            if (Schema::hasColumn('voucher_pendaftaran', 'berlaku_mulai')) $cols[] = 'berlaku_mulai';
            if (Schema::hasColumn('voucher_pendaftaran', 'nama_voucher')) $cols[] = 'nama_voucher';
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};

