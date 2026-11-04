<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voucher_pendaftaran', function (Blueprint $table) {
            if (!Schema::hasColumn('voucher_pendaftaran', 'kuota_terpakai')) {
                $table->integer('kuota_terpakai')->default(0)->after('kuota_voucher');
            }
        });
    }

    public function down(): void
    {
        Schema::table('voucher_pendaftaran', function (Blueprint $table) {
            if (Schema::hasColumn('voucher_pendaftaran', 'kuota_terpakai')) {
                $table->dropColumn('kuota_terpakai');
            }
        });
    }
};

