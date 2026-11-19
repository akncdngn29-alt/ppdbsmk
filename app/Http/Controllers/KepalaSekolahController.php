<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\Pembayaran;
use App\Models\Terima;
use App\Models\LogAktivitas;
use App\Models\PengaturanSekolah;
use Carbon\Carbon;

class KepalaSekolahController extends Controller
{
    public function index()
    {
        $totalPendaftar = Pendaftar::count();
        $totalDiterima = Pendaftar::where('status_kelulusan', 'Diterima')->count();
        $totalCadangan = Pendaftar::where('status_kelulusan', 'Cadangan')->count();
        $totalDitolak = Pendaftar::where('status_kelulusan', 'Tidak Lolos')->count();

        // Jurusan statistics for Chart.js
        $jurusan = Jurusan::withCount([
            'pendaftarPilihan1',
            'siswaDiterima',
        ])->get();

        // Market Intelligence: Top 5 Asal SMP/MTs
        $topAsalSekolah = Pendaftar::select('asal_sekolah', DB::raw('count(*) as total'))
            ->groupBy('asal_sekolah')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Daily registration trend (last 7 days)
        $dailyTrend = Pendaftar::select(DB::raw('DATE(tgl_daftar) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get()
            ->reverse();

        $pengaturan = PengaturanSekolah::getPengaturan();

        if (auth('admin')->check() && auth('admin')->user()->role === 'kepala_sekolah') {
            LogAktivitas::log(
                'PENGAWASAN',
                'Kepala Sekolah memantau ringkasan statistik dan tren dashboard PPDB',
                null,
                null,
                null,
                null,
                'SUCCESS',
                null,
                auth('admin')->id(),
                'kepala_sekolah'
            );
        }

        return view('kepsek.dashboard', compact(
            'totalPendaftar',
            'totalDiterima',
            'totalCadangan',
            'totalDitolak',
            'jurusan',
            'topAsalSekolah',
            'dailyTrend',
            'pengaturan'
        ));
    }

    public function kelulusan(Request $request)
    {
        $query = Pendaftar::with(['jurusan1', 'jurusan2', 'nilaiSeleksi', 'prestasi', 'terima'])
            ->where('status_pembayaran', 'Paid');

        if ($request->filled('jurusan')) {
            $query->where('id_jurusan_1', $request->jurusan);
        }

        $pendaftar = $query->orderBy('id_pendaftar', 'desc')->paginate(20)->withQueryString();
        $jurusan = Jurusan::all();

        return view('kepsek.kelulusan', compact('pendaftar', 'jurusan'));
    }

    public function sahkanKelulusan(Request $request, $id)
    {
        $request->validate([
            'status_kelulusan' => 'required|in:Diterima,Cadangan,Tidak Lolos',
            'id_jurusan_diterima' => 'nullable|exists:jurusan,id_jurusan',
            'catatan' => 'nullable|string',
        ]);

        $pendaftar = Pendaftar::findOrFail($id);
        $jurusanId = $request->id_jurusan_diterima ?: $pendaftar->id_jurusan_1;

        DB::transaction(function () use ($request, $pendaftar, $jurusanId) {
            $pendaftar->update([
                'status_kelulusan' => $request->status_kelulusan,
            ]);

            if ($request->status_kelulusan === 'Diterima') {
                Terima::updateOrCreate(
                    ['id_pendaftar' => $pendaftar->id_pendaftar],
                    [
                        'id_jurusan' => $jurusanId,
                        'id_admin_pengesah' => auth('admin')->id(),
                        'no_surat_kelulusan' => Terima::generateNoSuratKelulusan(),
                        'tgl_diterima' => Carbon::now()->format('Y-m-d'),
                        'keterangan' => $request->catatan ?: 'Dinyatakan Lulus Seleksi PPDB SMK Islam Bina Khalifah Bangsa.',
                    ]
                );
            } else {
                Terima::where('id_pendaftar', $pendaftar->id_pendaftar)->delete();
            }

            LogAktivitas::log(
                'PENGESAHAN_SKL',
                "Kepala Sekolah mengesahkan kelulusan {$pendaftar->nama}: {$request->status_kelulusan}",
                'pendaftar',
                $pendaftar->id_pendaftar
            );
        });

        return back()->with('success', "Keputusan kelulusan untuk {$pendaftar->nama} berhasil disahkan.");
    }

    public function laporan()
    {
        $jurusan = Jurusan::withCount(['pendaftarPilihan1', 'siswaDiterima'])->get();
        $totalPendaftar = Pendaftar::count();
        $totalDiterima = Pendaftar::where('status_kelulusan', 'Diterima')->count();
        $pengaturan = PengaturanSekolah::getPengaturan();

        if (auth('admin')->check() && auth('admin')->user()->role === 'kepala_sekolah') {
            LogAktivitas::log(
                'PENGAWASAN',
                'Kepala Sekolah meninjau laporan rekapitulasi PPDB',
                null,
                null,
                null,
                null,
                'SUCCESS',
                null,
                auth('admin')->id(),
                'kepala_sekolah'
            );
        }

        return view('kepsek.laporan', compact('jurusan', 'totalPendaftar', 'totalDiterima', 'pengaturan'));
    }

    /**
     * Halaman Riwayat Aktivitas & Audit Trail Eksekutif Kepala Sekolah (Tahap 2).
     * Memiliki hak pengawasan penuh atas seluruh aktivitas: Calon Siswa, Petugas TU, & Kepala Sekolah.
     */
    public function auditLog(Request $request)
    {
        abort_if(!auth('admin')->check() || auth('admin')->user()->role !== 'kepala_sekolah', 403, 'Akses khusus Kepala Sekolah.');

        $query = LogAktivitas::with(['admin', 'user.pendaftar']);

        // 1. Filter Pencarian Teks Bebas
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('deskripsi', 'LIKE', "%{$q}%")
                    ->orWhere('aksi', 'LIKE', "%{$q}%")
                    ->orWhere('ip_address', 'LIKE', "%{$q}%")
                    ->orWhereHas('user', function ($u) use ($q) {
                        $u->where('nama', 'LIKE', "%{$q}%")
                          ->orWhere('nisn', 'LIKE', "%{$q}%")
                          ->orWhere('username', 'LIKE', "%{$q}%")
                          ->orWhereHas('pendaftar', function ($p) use ($q) {
                              $p->where('no_pendaftaran', 'LIKE', "%{$q}%")
                                ->orWhere('nama', 'LIKE', "%{$q}%");
                          });
                    })
                    ->orWhereHas('admin', function ($a) use ($q) {
                        $a->where('nama_admin', 'LIKE', "%{$q}%")
                          ->orWhere('username', 'LIKE', "%{$q}%");
                    });
            });
        }

        // 2. Filter Berdasarkan Role / Aktor
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // 3. Filter Berdasarkan Jenis Aktivitas
        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }

        // 4. Filter Berdasarkan Status
        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        // 5. Filter Rentang Tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('created_at', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('created_at', '<=', $request->tanggal_akhir);
        }

        $logs = $query->orderBy('id_log', 'desc')->paginate(25)->withQueryString();

        // Statistik Pengawasan untuk Kepala Sekolah
        $stats = [
            'total' => LogAktivitas::count(),
            'siswa' => LogAktivitas::where('role', 'calon_siswa')->count(),
            'tu' => LogAktivitas::where('role', 'tu')->count(),
            'kepsek' => LogAktivitas::where('role', 'kepala_sekolah')->count(),
            'gagal' => LogAktivitas::where('status', 'FAILED')->count(),
        ];

        // Daftar jenis aksi unik untuk opsi filter dropdown
        $daftarAksi = LogAktivitas::distinct()->pluck('aksi')->filter()->values();

        return view('kepsek.audit_log', compact('logs', 'stats', 'daftarAksi'));
    }

    /**
     * Halaman Ringkasan Keuangan PPDB Eksekutif Kepala Sekolah.
     * Menyajikan metrik pendapatan riil, breakdown tunai vs online, potongan voucher,
     * serta log transaksi pembayaran yang valid.
     */
    public function keuangan(Request $request)
    {
        abort_if(!auth('admin')->check() || auth('admin')->user()->role !== 'kepala_sekolah', 403, 'Akses khusus Kepala Sekolah.');

        // 1. KPI Metrik Keuangan Real-Time (Status Settlement)
        $totalPendapatan = Pembayaran::where('status_transaksi', 'settlement')->sum('total_bayar');
        $pendapatanTunai = Pembayaran::where('status_transaksi', 'settlement')->whereIn('tipe_bayar', ['tunai', 'tunai_tu'])->sum('total_bayar');
        $pendapatanOnline = Pembayaran::where('status_transaksi', 'settlement')->where('tipe_bayar', 'midtrans')->sum('total_bayar');
        $totalPotongan = Pembayaran::where('status_transaksi', 'settlement')->sum('potongan');
        $totalTransaksiLunas = Pembayaran::where('status_transaksi', 'settlement')->count();
        $totalTransaksiPending = Pembayaran::where('status_transaksi', 'pending')->count();

        // 2. Breakdown per Gelombang
        $gelombangStats = Gelombang::withCount([
            'pembayaran as transaksi_lunas_count' => function ($q) {
                $q->where('status_transaksi', 'settlement');
            }
        ])->get()->map(function ($g) {
            $g->total_nominal = Pembayaran::where('id_gelombang', $g->id_gelombang)
                ->where('status_transaksi', 'settlement')
                ->sum('total_bayar');
            return $g;
        });

        // 3. Breakdown per Jurusan Pilihan 1
        $jurusanStats = Jurusan::all()->map(function ($j) {
            $j->total_lunas = Pembayaran::whereHas('pendaftar', function ($p) use ($j) {
                $p->where('id_jurusan_1', $j->id_jurusan);
            })->where('status_transaksi', 'settlement')->sum('total_bayar');

            $j->pendaftar_lunas_count = Pembayaran::whereHas('pendaftar', function ($p) use ($j) {
                $p->where('id_jurusan_1', $j->id_jurusan);
            })->where('status_transaksi', 'settlement')->count();
            return $j;
        });

        // 4. Query Transaksi dengan Filter & Pencarian
        $query = Pembayaran::with(['pendaftar.jurusan1', 'gelombang', 'voucher']);

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('kode_transaksi', 'LIKE', "%{$q}%")
                    ->orWhereHas('pendaftar', function ($p) use ($q) {
                        $p->where('nama', 'LIKE', "%{$q}%")
                          ->orWhere('no_pendaftaran', 'LIKE', "%{$q}%")
                          ->orWhere('nisn', 'LIKE', "%{$q}%");
                    });
            });
        }

        if ($request->filled('tipe_bayar')) {
            $query->where('tipe_bayar', $request->tipe_bayar);
        }

        if ($request->filled('status')) {
            $query->where('status_transaksi', $request->status);
        }

        if ($request->filled('gelombang')) {
            $query->where('id_gelombang', $request->gelombang);
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('waktu_transaksi', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->whereDate('waktu_transaksi', '<=', $request->tanggal_akhir);
        }

        $transaksi = $query->orderBy('waktu_transaksi', 'desc')->paginate(20)->withQueryString();
        $daftarGelombang = Gelombang::all();

        LogAktivitas::log(
            'PENGAWASAN',
            'Kepala Sekolah memantau ringkasan keuangan dan transaksi pembayaran PPDB',
            null,
            null,
            null,
            null,
            'SUCCESS',
            null,
            auth('admin')->id(),
            'kepala_sekolah'
        );

        return view('kepsek.keuangan', compact(
            'totalPendapatan',
            'pendapatanTunai',
            'pendapatanOnline',
            'totalPotongan',
            'totalTransaksiLunas',
            'totalTransaksiPending',
            'gelombangStats',
            'jurusanStats',
            'transaksi',
            'daftarGelombang'
        ));
    }
}

