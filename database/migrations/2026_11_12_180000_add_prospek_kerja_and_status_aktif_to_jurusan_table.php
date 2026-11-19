<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurusan', function (Blueprint $table) {
            if (!Schema::hasColumn('jurusan', 'prospek_kerja')) {
                $table->text('prospek_kerja')->nullable()->after('deskripsi');
            }
            if (!Schema::hasColumn('jurusan', 'status_aktif')) {
                $table->boolean('status_aktif')->default(true)->after('prospek_kerja');
            }
        });

        // Set initial default prospek kerja for existing 4 majors if empty
        $defaultProspek = [
            'FAR' => 'Apoteker Asisten, Tenaga Teknis Kefarmasian RS/Klinik, Industri Obat & Herbal, Pengawasan Mutu Laboratorium',
            'TKJ' => 'Network Engineer, Cloud & Server Administrator, IT Support Specialist, Cyber Security Analyst, Teknisi Fiber Optik',
            'TKR' => 'Teknisi Otomotif Mobil Modern, Mekanik Bengkel Resmi/Dealer, Service Advisor, Modifikator & Diagnosa EFI',
            'TBSM' => 'Teknisi Mesin Injeksi Roda Dua, Service & Sparepart Specialist, Kepala Bengkel Motor, Wirausaha Bengkel Mandiri',
        ];

        foreach ($defaultProspek as $kode => $prospek) {
            DB::table('jurusan')
                ->where('kode_jurusan', $kode)
                ->where(function ($query) {
                    $query->whereNull('prospek_kerja')->orWhere('prospek_kerja', '');
                })
                ->update(['prospek_kerja' => $prospek, 'status_aktif' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('jurusan', function (Blueprint $table) {
            if (Schema::hasColumn('jurusan', 'status_aktif')) {
                $table->dropColumn('status_aktif');
            }
            if (Schema::hasColumn('jurusan', 'prospek_kerja')) {
                $table->dropColumn('prospek_kerja');
            }
        });
    }
};

