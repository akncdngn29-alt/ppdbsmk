<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            if (!Schema::hasColumn('pembayaran', 'qr_string')) {
                $table->text('qr_string')->nullable()->after('snap_token');
            }
            if (!Schema::hasColumn('pembayaran', 'qr_url')) {
                $table->text('qr_url')->nullable()->after('qr_string');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            if (Schema::hasColumn('pembayaran', 'qr_url')) {
                $table->dropColumn('qr_url');
            }
            if (Schema::hasColumn('pembayaran', 'qr_string')) {
                $table->dropColumn('qr_string');
            }
        });
    }
};

