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
        if (!Schema::hasTable('pendaftaran_sequences')) {
            Schema::create('pendaftaran_sequences', function (Blueprint $table) {
                $table->id('id_sequence');
                $table->string('prefix', 50)->unique();
                $table->unsignedInteger('last_number')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendaftaran_sequences');
    }
};

