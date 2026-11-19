<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JadwalSeleksi;
use App\Models\RuangSeleksi;
use App\Models\PenempatanPeserta;
use App\Models\Pendaftar;
use App\Models\PengaturanSekolah;
use App\Models\LogAktivitas;

class JadwalSeleksiController extends Controller
{
    // =========================================================
    // PETUGAS TU: MANAJEMEN JADWAL SELEKSI
    // =========================================================

    /**
     * Daftar semua jadwal seleksi
     */
    public function index(Request $request)
    {
        $query = JadwalSeleksi::withCount('penempatan');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_jadwal', 'like', "%{$q}%")
                    ->orWhere('jenis_seleksi', 'like', "%{$q}%")
                    ->orWhere('lokasi', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $jadwals = $query->orderByDesc('tanggal')
            ->orderByDesc('id_jadwal')
            ->paginate(15)
            ->withQueryString();

        $totalRuang   = RuangSeleksi::count();
        $ruangAktif   = RuangSeleksi::aktif()->count();
        $totalJadwal  = JadwalSeleksi::count();
        $jadwalAktif  = JadwalSeleksi::aktif()->count();

        // Total kapasitas seluruh ruang aktif
        $totalKapasitasRuang = RuangSeleksi::aktif()->sum('kapasitas');

        return view('admin.jadwal.index', compact(
            'jadwals', 'totalRuang', 'ruangAktif', 'totalJadwal', 'jadwalAktif', 'totalKapasitasRuang'
        ));
    }

    /**
     * Form tambah jadwal baru
     */
    public function create()
    {
        return view('admin.jadwal.form', ['jadwal' => null]);
    }

    /**
     * Simpan jadwal baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_jadwal'   => 'required|string|max:150',
            'jenis_seleksi' => 'required|string|max:100',
            'tanggal'       => 'required|date',
            'waktu_mulai'   => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'lokasi'        => 'nullable|string|max:200',
            'gedung'        => 'nullable|string|max:100',
            'keterangan'    => 'nullable|string',
            'status'        => 'required|in:aktif,nonaktif',
        ]);

        $jadwal = JadwalSeleksi::create($validated);

        LogAktivitas::log(
            'TAMBAH_JADWAL',
            "Jadwal seleksi \"{$jadwal->nama_jadwal}\" ({$jadwal->jenis_seleksi}) berhasil ditambahkan.",
            'jadwal_seleksi',
            $jadwal->id_jadwal,
            null,
            $jadwal->toArray()
        );

        return redirect()->route('admin.jadwal.index')
            ->with('success', "Jadwal \"{$jadwal->nama_jadwal}\" berhasil ditambahkan.");
    }

    /**
     * Form edit jadwal
     */
    public function edit($id)
    {
        $jadwal = JadwalSeleksi::findOrFail($id);
        return view('admin.jadwal.form', compact('jadwal'));
    }

    /**
     * Update jadwal
     */
    public function update(Request $request, $id)
    {
        $jadwal = JadwalSeleksi::findOrFail($id);
        $dataLama = $jadwal->toArray();

        $validated = $request->validate([
            'nama_jadwal'   => 'required|string|max:150',
            'jenis_seleksi' => 'required|string|max:100',
            'tanggal'       => 'required|date',
            'waktu_mulai'   => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'lokasi'        => 'nullable|string|max:200',
            'gedung'        => 'nullable|string|max:100',
            'keterangan'    => 'nullable|string',
            'status'        => 'required|in:aktif,nonaktif',
        ]);

        $jadwal->update($validated);

        LogAktivitas::log(
            'UBAH_JADWAL',
            "Jadwal seleksi \"{$jadwal->nama_jadwal}\" berhasil diperbarui.",
            'jadwal_seleksi',
            $jadwal->id_jadwal,
            $dataLama,
            $jadwal->fresh()->toArray()
        );

        return redirect()->route('admin.jadwal.index')
            ->with('success', "Jadwal \"{$jadwal->nama_jadwal}\" berhasil diperbarui.");
    }

    /**
     * Hapus jadwal (hanya jika tidak ada peserta yang sudah ditempatkan)
     */
    public function destroy($id)
    {
        $jadwal = JadwalSeleksi::findOrFail($id);

        $jumlahPenempatan = PenempatanPeserta::where('id_jadwal', $id)->count();
        if ($jumlahPenempatan > 0) {
            return back()->with('error', "Jadwal tidak dapat dihapus karena masih ada {$jumlahPenempatan} peserta yang ditempatkan. Batalkan penempatan peserta terlebih dahulu.");
        }

        $namaJadwal = $jadwal->nama_jadwal;
        $dataLama   = $jadwal->toArray();

        $jadwal->delete();

        LogAktivitas::log(
            'HAPUS_JADWAL',
            "Jadwal seleksi \"{$namaJadwal}\" dihapus.",
            'jadwal_seleksi',
            $id,
            $dataLama,
            null
        );

        return redirect()->route('admin.jadwal.index')
            ->with('success', "Jadwal \"{$namaJadwal}\" berhasil dihapus.");
    }

    // =========================================================
    // PETUGAS TU: MANAJEMEN RUANG SELEKSI
    // =========================================================

    /**
     * Halaman manajemen ruang seleksi
     */
    public function manajemenRuang(Request $request)
    {
        $query = RuangSeleksi::withCount('penempatan');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_ruang', 'like', "%{$q}%")
                    ->orWhere('kode_ruang', 'like', "%{$q}%")
                    ->orWhere('gedung', 'like', "%{$q}%");
            });
        }

        $ruangs = $query->orderBy('kode_ruang')->paginate(20)->withQueryString();

        $totalRuang     = RuangSeleksi::count();
        $ruangAktif     = RuangSeleksi::aktif()->count();
        $totalKapasitas = RuangSeleksi::aktif()->sum('kapasitas');

        return view('admin.jadwal.ruang', compact('ruangs', 'totalRuang', 'ruangAktif', 'totalKapasitas'));
    }

    /**
     * Simpan ruang baru
     */
    public function tambahRuang(Request $request)
    {
        $validated = $request->validate([
            'kode_ruang'  => 'required|string|max:20|unique:ruang_seleksi,kode_ruang',
            'nama_ruang'  => 'required|string|max:100',
            'gedung'      => 'nullable|string|max:100',
            'kapasitas'   => 'required|integer|min:1|max:500',
            'keterangan'  => 'nullable|string',
            'status_aktif'=> 'nullable|boolean',
        ]);

        $validated['status_aktif'] = $request->boolean('status_aktif', true);

        $ruang = RuangSeleksi::create($validated);

        LogAktivitas::log(
            'TAMBAH_RUANG',
            "Ruang seleksi \"{$ruang->nama_ruang}\" ({$ruang->kode_ruang}) kapasitas {$ruang->kapasitas} berhasil ditambahkan.",
            'ruang_seleksi',
            $ruang->id_ruang,
            null,
            $ruang->toArray()
        );

        return redirect()->route('admin.ruang.index')
            ->with('success', "Ruang \"{$ruang->nama_ruang}\" berhasil ditambahkan.");
    }

    /**
     * Update ruang seleksi
     */
    public function updateRuang(Request $request, $id)
    {
        $ruang    = RuangSeleksi::findOrFail($id);
        $dataLama = $ruang->toArray();

        $validated = $request->validate([
            'kode_ruang'  => "required|string|max:20|unique:ruang_seleksi,kode_ruang,{$id},id_ruang",
            'nama_ruang'  => 'required|string|max:100',
            'gedung'      => 'nullable|string|max:100',
            'kapasitas'   => 'required|integer|min:1|max:500',
            'keterangan'  => 'nullable|string',
            'status_aktif'=> 'nullable|boolean',
        ]);

        $validated['status_aktif'] = $request->boolean('status_aktif', false);

        // Validasi: Perubahan kapasitas tidak boleh membuat jumlah peserta terdaftar melebihi kapasitas baru
        $maxPesertaPerJadwal = PenempatanPeserta::where('id_ruang', $id)
            ->groupBy('id_jadwal')
            ->selectRaw('count(*) as total')
            ->pluck('total')
            ->max() ?? 0;

        if ($validated['kapasitas'] < $maxPesertaPerJadwal) {
            return back()->with('error', "Kapasitas baru ({$validated['kapasitas']}) tidak boleh lebih kecil dari jumlah peserta yang sudah ditempatkan di ruang ini ({$maxPesertaPerJadwal} peserta).");
        }

        $ruang->update($validated);

        LogAktivitas::log(
            'UBAH_RUANG',
            "Ruang seleksi \"{$ruang->nama_ruang}\" ({$ruang->kode_ruang}) berhasil diperbarui.",
            'ruang_seleksi',
            $ruang->id_ruang,
            $dataLama,
            $ruang->fresh()->toArray()
        );

        return redirect()->route('admin.ruang.index')
            ->with('success', "Ruang \"{$ruang->nama_ruang}\" berhasil diperbarui.");
    }

    /**
     * Hapus ruang seleksi (hanya jika tidak digunakan dalam penempatan manapun)
     */
    public function hapusRuang($id)
    {
        $ruang = RuangSeleksi::findOrFail($id);

        $digunakan = PenempatanPeserta::where('id_ruang', $id)->count();
        if ($digunakan > 0) {
            return back()->with('error', "Ruang \"{$ruang->nama_ruang}\" tidak dapat dihapus karena masih digunakan dalam {$digunakan} penempatan peserta.");
        }

        $namaRuang = $ruang->nama_ruang;
        $kodeRuang = $ruang->kode_ruang;
        $dataLama  = $ruang->toArray();

        $ruang->delete();

        LogAktivitas::log(
            'HAPUS_RUANG',
            "Ruang seleksi \"{$namaRuang}\" ({$kodeRuang}) dihapus.",
            'ruang_seleksi',
            $id,
            $dataLama,
            null
        );

        return redirect()->route('admin.ruang.index')
            ->with('success', "Ruang \"{$namaRuang}\" berhasil dihapus.");
    }

    // =========================================================
    // PETUGAS TU: PENEMPATAN PESERTA
    // =========================================================

    /**
     * Halaman penempatan peserta untuk jadwal tertentu
     */
    public function penempatanOtomatis(Request $request, $idJadwal)
    {
        $jadwal = JadwalSeleksi::findOrFail($idJadwal);

        // Ambil peserta yang eligible: is_locked=true dan status_pembayaran='Paid'
        $pesertaQuery = Pendaftar::with(['jurusan1', 'jurusan2'])
            ->where('is_locked', true)
            ->where('status_pembayaran', 'Paid');

        if ($request->filled('q')) {
            $q = $request->q;
            $pesertaQuery->where(function ($sub) use ($q) {
                $sub->where('nama', 'like', "%{$q}%")
                    ->orWhere('no_pendaftaran', 'like', "%{$q}%")
                    ->orWhere('nisn', 'like', "%{$q}%");
            });
        }

        $semuaPesertaEligible = $pesertaQuery->orderBy('no_pendaftaran')->get();

        // Peserta yang sudah ditempatkan di jadwal ini
        $sudahDitempatkanIds = PenempatanPeserta::where('id_jadwal', $idJadwal)
            ->pluck('id_pendaftar')
            ->toArray();

        // Pisahkan yang belum dan sudah ditempatkan pada jadwal ini
        $belumDitempatkan = $semuaPesertaEligible->whereNotIn('id_pendaftar', $sudahDitempatkanIds)->values();
        $sudahDitempatkan = $semuaPesertaEligible->whereIn('id_pendaftar', $sudahDitempatkanIds)->values();

        // Data ruang aktif beserta jumlah peserta yang sudah terisi di jadwal ini
        $ruangs = RuangSeleksi::aktif()
            ->withCount(['penempatan as terisi' => function ($q) use ($idJadwal) {
                $q->where('id_jadwal', $idJadwal);
            }])
            ->orderBy('kode_ruang')
            ->get();

        // Detail penempatan di jadwal ini
        $penempatanQuery = PenempatanPeserta::with(['pendaftar.jurusan1', 'ruang'])
            ->where('id_jadwal', $idJadwal);

        if ($request->filled('ruang_filter')) {
            $penempatanQuery->where('id_ruang', $request->ruang_filter);
        }

        $penempatan = $penempatanQuery->get();

        return view('admin.jadwal.penempatan', compact(
            'jadwal',
            'semuaPesertaEligible',
            'belumDitempatkan',
            'sudahDitempatkan',
            'ruangs',
            'penempatan'
        ));
    }

    /**
     * Proses penempatan otomatis peserta ke ruang
     */
    public function prosesOtomatis(Request $request, $idJadwal)
    {
        $jadwal = JadwalSeleksi::findOrFail($idJadwal);

        $validated = $request->validate([
            'pendaftar_ids'   => 'required|array|min:1',
            'pendaftar_ids.*' => 'integer|exists:pendaftar,id_pendaftar',
        ]);

        $pendaftarIds = $validated['pendaftar_ids'];

        $ruangAktif = RuangSeleksi::aktif()->orderBy('kode_ruang')->get();

        if ($ruangAktif->isEmpty()) {
            return back()->with('error', 'Tidak ada ruang seleksi aktif. Tambahkan ruang terlebih dahulu.');
        }

        $jumlahDitempatkan = 0;
        $jumlahBelumCukupRuang = 0;
        $pesertaBentrokCount = 0;

        try {
            DB::transaction(function () use ($jadwal, $pendaftarIds, $ruangAktif, &$jumlahDitempatkan, &$jumlahBelumCukupRuang, &$pesertaBentrokCount) {
                // 1. Filter peserta yang sudah ditempatkan di jadwal ini
                $sudahDitempatkan = PenempatanPeserta::where('id_jadwal', $jadwal->id_jadwal)
                    ->pluck('id_pendaftar')
                    ->toArray();

                $perluDitempatkan = array_diff($pendaftarIds, $sudahDitempatkan);

                if (empty($perluDitempatkan)) {
                    return;
                }

                // 2. Ambil data peserta terurut stabil berdasarkan no_pendaftaran
                $pesertaList = Pendaftar::whereIn('id_pendaftar', $perluDitempatkan)
                    ->where('is_locked', true)
                    ->where('status_pembayaran', 'Paid')
                    ->orderBy('no_pendaftaran')
                    ->get();

                // 3. Filter peserta yang bentrok jadwal lain
                $pesertaValid = [];
                foreach ($pesertaList as $p) {
                    $bentrok = JadwalSeleksi::where('id_jadwal', '!=', $jadwal->id_jadwal)
                        ->where('tanggal', $jadwal->tanggal)
                        ->where(function ($q) use ($jadwal) {
                            $q->where('waktu_mulai', '<', $jadwal->waktu_selesai)
                              ->where('waktu_selesai', '>', $jadwal->waktu_mulai);
                        })
                        ->whereHas('penempatan', function ($q) use ($p) {
                            $q->where('id_pendaftar', $p->id_pendaftar);
                        })
                        ->exists();

                    if ($bentrok) {
                        $pesertaBentrokCount++;
                    } else {
                        $pesertaValid[] = $p;
                    }
                }

                // 4. Bagi ke ruang yang tersedia secara seimbang sesuai kapasitas
                $index = 0;
                $totalValid = count($pesertaValid);

                foreach ($ruangAktif as $ruang) {
                    $terisi = PenempatanPeserta::where('id_jadwal', $jadwal->id_jadwal)
                        ->where('id_ruang', $ruang->id_ruang)
                        ->lockForUpdate()
                        ->count();

                    $sisa = $ruang->kapasitas - $terisi;

                    while ($sisa > 0 && $index < $totalValid) {
                        PenempatanPeserta::create([
                            'id_jadwal'    => $jadwal->id_jadwal,
                            'id_ruang'     => $ruang->id_ruang,
                            'id_pendaftar' => $pesertaValid[$index]->id_pendaftar,
                        ]);
                        $sisa--;
                        $index++;
                        $jumlahDitempatkan++;
                    }

                    if ($index >= $totalValid) {
                        break;
                    }
                }

                $jumlahBelumCukupRuang = $totalValid - $index;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'uq_jadwal_pendaftar') || str_contains($e->getMessage(), 'unique_jadwal_peserta')) {
                return back()->with('error', 'Beberapa peserta sudah ditempatkan pada jadwal ini. Silakan coba kembali.');
            }
            throw $e;
        }

        LogAktivitas::log(
            'PENEMPATAN_OTOMATIS',
            "Penempatan otomatis jadwal \"{$jadwal->nama_jadwal}\": {$jumlahDitempatkan} berhasil ditempatkan" .
                ($jumlahBelumCukupRuang > 0 ? ", {$jumlahBelumCukupRuang} belum tertampung kapasitas" : "") .
                ($pesertaBentrokCount > 0 ? ", {$pesertaBentrokCount} dilewati karena jadwal bentrok." : "."),
            'penempatan_peserta',
            $jadwal->id_jadwal,
            null,
            [
                'jumlah_ditempatkan' => $jumlahDitempatkan,
                'belum_tertampung'   => $jumlahBelumCukupRuang,
                'bentrok'            => $pesertaBentrokCount
            ]
        );

        $pesan = "{$jumlahDitempatkan} peserta berhasil ditempatkan.";
        if ($jumlahBelumCukupRuang > 0) {
            $pesan .= " {$jumlahBelumCukupRuang} peserta belum tertampung karena kapasitas ruang penuh.";
        }
        if ($pesertaBentrokCount > 0) {
            $pesan .= " {$pesertaBentrokCount} peserta dilewati karena memiliki jadwal seleksi lain yang bentrok.";
        }

        return redirect()->route('admin.jadwal.penempatan', $jadwal->id_jadwal)
            ->with('success', $pesan);
    }

    /**
     * Penempatan manual / pindah peserta ke ruang
     */
    public function penempatanManual(Request $request)
    {
        $validated = $request->validate([
            'id_jadwal'    => 'required|integer|exists:jadwal_seleksi,id_jadwal',
            'id_pendaftar' => 'required|integer|exists:pendaftar,id_pendaftar',
            'id_ruang'     => 'required|integer|exists:ruang_seleksi,id_ruang',
            'catatan'      => 'nullable|string|max:500',
        ]);

        $jadwal    = JadwalSeleksi::findOrFail($validated['id_jadwal']);
        $ruang     = RuangSeleksi::findOrFail($validated['id_ruang']);
        $pendaftar = Pendaftar::findOrFail($validated['id_pendaftar']);

        // 1. Validasi: Ruang tujuan harus aktif
        if (!$ruang->status_aktif) {
            return back()->with('error', "Ruang \"{$ruang->nama_ruang}\" sedang tidak aktif dan tidak dapat menerima peserta.");
        }

        // 2. Validasi: Jadwal bentrok untuk peserta yang sama
        $jadwalLainBentrok = JadwalSeleksi::where('id_jadwal', '!=', $jadwal->id_jadwal)
            ->where('tanggal', $jadwal->tanggal)
            ->where(function ($q) use ($jadwal) {
                $q->where(function ($sub) use ($jadwal) {
                    $sub->where('waktu_mulai', '<', $jadwal->waktu_selesai)
                        ->where('waktu_selesai', '>', $jadwal->waktu_mulai);
                });
            })
            ->whereHas('penempatan', function ($q) use ($validated) {
                $q->where('id_pendaftar', $validated['id_pendaftar']);
            })
            ->first();

        if ($jadwalLainBentrok) {
            return back()->with('error', "Jadwal bentrok: Peserta sudah terdaftar pada jadwal \"{$jadwalLainBentrok->nama_jadwal}\" ({$jadwalLainBentrok->waktu_mulai} - {$jadwalLainBentrok->waktu_selesai}) pada tanggal yang sama.");
        }

        // Cek apakah peserta sudah memiliki penempatan di jadwal ini
        $existingPenempatan = PenempatanPeserta::where('id_jadwal', $validated['id_jadwal'])
            ->where('id_pendaftar', $validated['id_pendaftar'])
            ->first();

        $isPindah = $existingPenempatan !== null;
        $ruangLama = $isPindah ? $existingPenempatan->id_ruang : null;

        // 3. Validasi: Kapasitas ruang tujuan masih tersedia
        $terisi = PenempatanPeserta::where('id_jadwal', $validated['id_jadwal'])
            ->where('id_ruang', $validated['id_ruang'])
            ->count();

        // Jika pindah di ruang yang sama, tidak menambah kuota
        $pengurang = ($isPindah && $ruangLama === (int) $validated['id_ruang']) ? 1 : 0;
        if (($terisi - $pengurang) >= $ruang->kapasitas) {
            return back()->with('error', "Ruang \"{$ruang->nama_ruang}\" sudah penuh (kapasitas: {$ruang->kapasitas}).");
        }

        DB::transaction(function () use ($validated, $existingPenempatan, $isPindah) {
            if ($isPindah) {
                $existingPenempatan->update([
                    'id_ruang' => $validated['id_ruang'],
                    'catatan'  => $validated['catatan'] ?? $existingPenempatan->catatan,
                ]);
            } else {
                PenempatanPeserta::create($validated);
            }
        });

        $aksi = $isPindah ? 'PINDAH_RUANG' : 'PENEMPATAN_MANUAL';
        $deskripsi = $isPindah
            ? "Peserta \"{$pendaftar->nama}\" berhasil dipindahkan ke ruang \"{$ruang->nama_ruang}\" pada jadwal \"{$jadwal->nama_jadwal}\"."
            : "Peserta \"{$pendaftar->nama}\" berhasil ditempatkan secara manual ke ruang \"{$ruang->nama_ruang}\" pada jadwal \"{$jadwal->nama_jadwal}\".";

        LogAktivitas::log(
            $aksi,
            $deskripsi,
            'penempatan_peserta',
            $validated['id_jadwal'],
            $isPindah ? ['id_ruang_lama' => $ruangLama] : null,
            ['id_ruang_baru' => $validated['id_ruang'], 'id_pendaftar' => $validated['id_pendaftar']]
        );

        return redirect()->route('admin.jadwal.penempatan', $validated['id_jadwal'])
            ->with('success', $deskripsi);
    }

    /**
     * Batalkan penempatan peserta
     */
    public function batalkanPenempatan($idPenempatan)
    {
        $penempatan = PenempatanPeserta::with(['jadwal', 'ruang', 'pendaftar'])->findOrFail($idPenempatan);

        $idJadwal    = $penempatan->id_jadwal;
        $namaPeserta = $penempatan->pendaftar->nama ?? 'Peserta';
        $namaRuang   = $penempatan->ruang->nama_ruang ?? 'Ruang';
        $namaJadwal  = $penempatan->jadwal->nama_jadwal ?? 'Jadwal';
        $dataLama    = $penempatan->toArray();

        $penempatan->delete();

        LogAktivitas::log(
            'BATAL_PENEMPATAN',
            "Penempatan peserta \"{$namaPeserta}\" di ruang \"{$namaRuang}\" pada jadwal \"{$namaJadwal}\" dibatalkan.",
            'penempatan_peserta',
            $idPenempatan,
            $dataLama,
            null
        );

        return redirect()->route('admin.jadwal.penempatan', $idJadwal)
            ->with('success', "Penempatan peserta \"{$namaPeserta}\" berhasil dibatalkan.");
    }

    /**
     * Cetak daftar peserta per ruang per jadwal
     */
    public function cetakDaftarPeserta($idJadwal, $idRuang)
    {
        $jadwal = JadwalSeleksi::findOrFail($idJadwal);
        $ruang  = RuangSeleksi::findOrFail($idRuang);
        $pengaturan = PengaturanSekolah::first() ?? new PengaturanSekolah([
            'nama_sekolah' => 'SMK Islam Bina Khalifah Bangsa',
            'alamat'       => 'Kota Metro, Lampung',
            'kota'         => 'Kota Metro',
            'npsn'         => '10807575',
        ]);

        $penempatan = PenempatanPeserta::with(['pendaftar.jurusan1', 'pendaftar.jurusan2'])
            ->where('id_jadwal', $idJadwal)
            ->where('id_ruang', $idRuang)
            ->join('pendaftar', 'penempatan_peserta.id_pendaftar', '=', 'pendaftar.id_pendaftar')
            ->orderBy('pendaftar.no_pendaftaran')
            ->select('penempatan_peserta.*')
            ->get();

        return view('admin.jadwal.cetak_daftar', compact('jadwal', 'ruang', 'penempatan', 'pengaturan'));
    }

    // =========================================================
    // KEPALA SEKOLAH: MONITORING JADWAL (READ-ONLY)
    // =========================================================

    /**
     * Monitoring jadwal oleh Kepala Sekolah
     */
    public function monitoringJadwal()
    {
        $jadwals = JadwalSeleksi::withCount('penempatan')
            ->with(['ruangPenempatan' => function ($q) {
                $q->select('ruang_seleksi.id_ruang', 'kode_ruang', 'nama_ruang', 'kapasitas');
            }])
            ->orderByDesc('tanggal')
            ->get();

        $totalPesertaEligible = Pendaftar::where('is_locked', true)
            ->where('status_pembayaran', 'Paid')
            ->count();

        $totalDitempatkan = PenempatanPeserta::distinct('id_pendaftar')->count('id_pendaftar');
        $totalBelumDitempatkan = max(0, $totalPesertaEligible - $totalDitempatkan);

        $ruangs = RuangSeleksi::withCount('penempatan')->orderBy('kode_ruang')->get();
        $totalKapasitasRuang = RuangSeleksi::aktif()->sum('kapasitas');

        return view('kepsek.jadwal_monitoring', compact(
            'jadwals',
            'totalPesertaEligible',
            'totalDitempatkan',
            'totalBelumDitempatkan',
            'ruangs',
            'totalKapasitasRuang'
        ));
    }
}
