<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pendaftar;
use App\Models\Jurusan;
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

        return back()->with('success', "Keputusan kelulusan untuk {$pendaftar->nama} berhasil disahkan.");
    }

    public function laporan()
    {
        $jurusan = Jurusan::withCount(['pendaftarPilihan1', 'siswaDiterima'])->get();
        $totalPendaftar = Pendaftar::count();
        $totalDiterima = Pendaftar::where('status_kelulusan', 'Diterima')->count();
        $pengaturan = PengaturanSekolah::getPengaturan();

        return view('kepsek.laporan', compact('jurusan', 'totalPendaftar', 'totalDiterima', 'pengaturan'));
    }

    public function auditLog()
    {
        $logs = LogAktivitas::with(['admin', 'user'])
            ->orderBy('id_log', 'desc')
            ->paginate(25);

        return view('kepsek.audit_log', compact('logs'));
    }
}

