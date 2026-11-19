@extends('layouts.admin')

@section('title', 'Penempatan Peserta - ' . $jadwal->nama_jadwal . ' - SMK Islam Bina Khalifah Bangsa')
@section('header_title', 'Penempatan Peserta Seleksi')

@section('content')
<div class="space-y-6">

    <!-- Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.jadwal.index') }}" class="text-xs font-bold text-slate-500 hover:text-pink-600 transition">Jadwal Seleksi</a>
                <span class="text-xs text-slate-400">/</span>
                <span class="text-xs font-bold text-navy-950 dark:text-white">Alokasi & Penempatan</span>
            </div>
            <h2 class="text-xl font-extrabold text-navy-950 dark:text-white">{{ $jadwal->nama_jadwal }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                {{ \Carbon\Carbon::parse($jadwal->tanggal)->isoFormat('dddd, D MMMM Y') }} | Pukul {{ substr($jadwal->waktu_mulai, 0, 5) }} - {{ substr($jadwal->waktu_selesai, 0, 5) }} WIB | {{ $jadwal->lokasi ?? 'Kampus Utama' }}
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.jadwal.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 transition shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Ringkasan Statistik Peserta & Kapasitas -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm transition hover:shadow-md">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Peserta Terverifikasi</span>
            <h3 class="text-2xl font-black text-navy-950 dark:text-white mt-0.5">{{ $semuaPesertaEligible->count() }}</h3>
            <p class="text-[11px] text-slate-500 mt-1">Formulir final & biaya lunas</p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm transition hover:shadow-md">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sudah Ditempatkan</span>
            <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $sudahDitempatkan->count() }}</h3>
            <p class="text-[11px] text-emerald-600 font-medium mt-1">Telah memiliki ruang tes</p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm transition hover:shadow-md">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Belum Dapat Ruang</span>
            <h3 class="text-2xl font-black {{ $belumDitempatkan->count() > 0 ? 'text-pink-600' : 'text-slate-400' }} mt-0.5">{{ $belumDitempatkan->count() }}</h3>
            <p class="text-[11px] {{ $belumDitempatkan->count() > 0 ? 'text-pink-600' : 'text-slate-400' }} font-medium mt-1">Menunggu alokasi ruang</p>
        </div>

        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm transition hover:shadow-md">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Kapasitas Ruang</span>
            <h3 class="text-2xl font-black text-navy-950 dark:text-navy-300 mt-0.5">{{ $ruangs->sum('kapasitas') }}</h3>
            <p class="text-[11px] text-slate-500 mt-1">Sisa kuota: {{ max(0, $ruangs->sum('kapasitas') - $sudahDitempatkan->count()) }} kursi</p>
        </div>
    </div>

    <!-- Ringkasan Keterisian per Ruang Aktif -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-4">
        <div class="flex items-center justify-between mb-3">
            <h4 class="text-xs font-bold text-navy-950 dark:text-slate-300 uppercase tracking-wider">Status Keterisian Tiap Ruang</h4>
            <a href="{{ route('admin.ruang.index') }}" class="text-[11px] text-pink-600 hover:underline font-bold">+ Kelola Ruang</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @forelse($ruangs as $ruang)
                @php
                    $terisi = $ruang->terisi ?? 0;
                    $persen = $ruang->kapasitas > 0 ? min(100, round(($terisi / $ruang->kapasitas) * 100)) : 0;
                    $isPenuh = $terisi >= $ruang->kapasitas;
                @endphp
                <div class="p-3 rounded-2xl border {{ $isPenuh ? 'border-rose-200 bg-rose-50/30 dark:bg-rose-950/20' : 'border-slate-200/80 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40' }}">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="font-bold text-xs text-navy-950 dark:text-white">{{ $ruang->nama_ruang }}</span>
                        <span class="text-[10px] font-mono px-1.5 py-0.5 rounded {{ $isPenuh ? 'bg-rose-100 text-rose-700 font-bold' : 'bg-navy-50 text-navy-900 dark:bg-navy-900/60 dark:text-navy-300 font-bold' }}">
                            {{ $ruang->kode_ruang }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-400 mb-1">
                        <span>Terisi: <strong class="text-navy-950 dark:text-white">{{ $terisi }}</strong> / {{ $ruang->kapasitas }}</span>
                        <span class="font-bold {{ $isPenuh ? 'text-rose-600' : 'text-pink-600' }}">{{ $persen }}%</span>
                    </div>
                    <!-- Progress Bar -->
                    <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full {{ $isPenuh ? 'bg-rose-500' : 'bg-pink-500' }}" style="width: {{ $persen }}%"></div>
                    </div>
                    <div class="mt-2.5 flex items-center justify-between text-[10px]">
                        <span class="text-slate-400">{{ $ruang->gedung ?: 'Kampus' }}</span>
                        @if($terisi > 0)
                        <a href="{{ route('admin.jadwal.cetak', [$jadwal->id_jadwal, $ruang->id_ruang]) }}" target="_blank" class="inline-flex items-center gap-1 text-pink-600 dark:text-pink-400 font-bold hover:underline">
                            <i data-lucide="printer" class="w-3 h-3"></i>
                            <span>Cetak Hadir</span>
                        </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-4 py-4 text-center text-slate-400 text-xs">
                    Tidak ada ruang seleksi aktif. Silakan tambahkan ruang di menu Manajemen Ruang.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Panel 1: Peserta yang Belum Ditempatkan & Tombol Otomatis -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden">
        <form action="{{ route('admin.jadwal.penempatan.otomatis', $jadwal->id_jadwal) }}" method="POST">
            @csrf
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-pink-50/30 dark:bg-pink-950/20">
                <div>
                    <h3 class="text-sm font-extrabold text-navy-950 dark:text-white flex items-center gap-2">
                        <i data-lucide="user-plus" class="w-4 h-4 text-pink-500"></i>
                        <span>Peserta Belum Mendapatkan Ruang ({{ $belumDitempatkan->count() }} Orang)</span>
                    </h3>
                    <p class="text-[11px] text-slate-500">Pilih peserta di bawah untuk dibagi secara otomatis ke ruang aktif yang tersedia sesuai kapasitas.</p>
                </div>
                @if($belumDitempatkan->count() > 0)
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleSelectAll(this)" class="px-3 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50">
                        Pilih Semua
                    </button>
                    <button type="submit" onclick="return confirm('Bagikan peserta terpilih ke ruang aktif secara otomatis?')" class="px-4 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold shadow-md shadow-pink-600/30 inline-flex items-center gap-1.5 transition">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                        <span>Penempatan Otomatis</span>
                    </button>
                </div>
                @endif
            </div>

            @if($belumDitempatkan->count() > 0)
            <div class="overflow-x-auto max-h-80">
                <table class="w-full text-xs text-left">
                    <thead class="bg-navy-50/60 dark:bg-slate-800 text-navy-950 dark:text-slate-300 uppercase tracking-wider text-[10px] sticky top-0">
                        <tr>
                            <th class="py-2.5 px-4 w-10 text-center">Pilih</th>
                            <th class="py-2.5 px-4">No. Pendaftaran</th>
                            <th class="py-2.5 px-4">Nama Calon Siswa</th>
                            <th class="py-2.5 px-4">NISN</th>
                            <th class="py-2.5 px-4">Pilihan Jurusan</th>
                            <th class="py-2.5 px-4">Asal Sekolah</th>
                            <th class="py-2.5 px-4 text-right">Alokasi Manual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($belumDitempatkan as $p)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                            <td class="py-2.5 px-4 text-center">
                                <input type="checkbox" name="pendaftar_ids[]" value="{{ $p->id_pendaftar }}" class="checkbox-peserta rounded border-slate-300 text-pink-600 focus:ring-pink-500">
                            </td>
                            <td class="py-2.5 px-4 font-mono font-bold text-pink-600">{{ $p->no_pendaftaran }}</td>
                            <td class="py-2.5 px-4 font-bold text-navy-950 dark:text-white uppercase">{{ $p->nama }}</td>
                            <td class="py-2.5 px-4 font-mono">{{ $p->nisn }}</td>
                            <td class="py-2.5 px-4 font-semibold text-slate-700 dark:text-slate-300">
                                {{ $p->jurusan1 ? $p->jurusan1->nama_jurusan : '-' }}
                            </td>
                            <td class="py-2.5 px-4 text-slate-500">{{ $p->asal_sekolah }}</td>
                            <td class="py-2.5 px-4 text-right">
                                <button type="button" onclick="openManualPlacement({{ $p->id_pendaftar }}, '{{ addslashes($p->nama) }}', '{{ $p->no_pendaftaran }}')" class="px-2.5 py-1 rounded-lg bg-navy-50 hover:bg-pink-50 hover:text-pink-700 text-[11px] font-bold text-navy-950 transition">
                                    Tetapkan Ruang &rarr;
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="p-6 text-center text-slate-400">
                <i data-lucide="check-check" class="w-8 h-8 mx-auto mb-1.5 text-emerald-500"></i>
                <p class="font-bold text-xs text-navy-950 dark:text-slate-300">Semua Calon Siswa Terverifikasi Sudah Memiliki Ruang Seleksi!</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Tidak ada antrean peserta yang belum mendapatkan ruang.</p>
            </div>
            @endif
        </form>
    </div>

    <!-- Panel 2: Daftar Peserta yang Sudah Ditempatkan -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-sm font-extrabold text-navy-950 dark:text-white flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-pink-600"></i>
                    <span>Daftar Peserta yang Sudah Ditempatkan ({{ $penempatan->count() }} Orang)</span>
                </h3>
                <p class="text-[11px] text-slate-500">Peserta yang sudah terdaftar di ruang seleksi jadwal ini.</p>
            </div>
            
            <!-- Filter Ruang -->
            <form method="GET" action="{{ route('admin.jadwal.penempatan', $jadwal->id_jadwal) }}" class="flex items-center gap-2">
                <select name="ruang_filter" onchange="this.form.submit()" class="px-3 py-1.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500">
                    <option value="">Semua Ruang</option>
                    @foreach($ruangs as $r)
                        <option value="{{ $r->id_ruang }}" {{ request('ruang_filter') == $r->id_ruang ? 'selected' : '' }}>
                            {{ $r->nama_ruang }} ({{ $r->kode_ruang }})
                        </option>
                    @endforeach
                </select>
                @if(request('ruang_filter'))
                <a href="{{ route('admin.jadwal.penempatan', $jadwal->id_jadwal) }}" class="p-1.5 rounded-lg bg-slate-100 text-slate-500 hover:text-slate-800" title="Reset filter">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-navy-50/60 dark:bg-slate-800/50 text-navy-950 dark:text-slate-300 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4">No.</th>
                        <th class="py-3 px-4">No. Pendaftaran</th>
                        <th class="py-3 px-4">Nama Calon Siswa</th>
                        <th class="py-3 px-4">Pilihan Jurusan</th>
                        <th class="py-3 px-4">Ruang Seleksi</th>
                        <th class="py-3 px-4">Catatan</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                    @forelse($penempatan as $idx => $pnt)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                        <td class="py-3 px-4 text-slate-400 font-mono">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4 font-mono font-bold text-pink-600">{{ $pnt->pendaftar->no_pendaftaran ?? '-' }}</td>
                        <td class="py-3 px-4 font-bold text-navy-950 dark:text-white uppercase">{{ $pnt->pendaftar->nama ?? '-' }}</td>
                        <td class="py-3 px-4 font-semibold text-slate-700 dark:text-slate-300">
                            {{ $pnt->pendaftar && $pnt->pendaftar->jurusan1 ? $pnt->pendaftar->jurusan1->nama_jurusan : '-' }}
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-navy-50 dark:bg-navy-950/40 text-navy-950 dark:text-navy-300 font-bold border border-navy-200/50">
                                <i data-lucide="door-closed" class="w-3.5 h-3.5 text-pink-600"></i>
                                <span>{{ $pnt->ruang->nama_ruang ?? '-' }}</span>
                                <span class="font-mono text-[10px] text-pink-600">({{ $pnt->ruang->kode_ruang ?? '-' }})</span>
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-400 text-[11px]">{{ $pnt->catatan ?: '-' }}</td>
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" onclick="openPindahRuang({{ $pnt->id_pendaftar }}, '{{ addslashes($pnt->pendaftar->nama ?? '') }}', '{{ $pnt->pendaftar->no_pendaftaran ?? '' }}', {{ $pnt->id_ruang }})" class="px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300 hover:bg-sky-100 font-bold text-[11px] inline-flex items-center gap-1 transition">
                                    <i data-lucide="arrow-right-left" class="w-3 h-3"></i>
                                    <span>Pindah</span>
                                </button>
                                <form action="{{ route('admin.jadwal.penempatan.batal', $pnt->id_penempatan) }}" method="POST" onsubmit="return confirm('Batalkan penempatan peserta {{ $pnt->pendaftar->nama ?? '' }}?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Batalkan Penempatan" class="p-1 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition">
                                        <i data-lucide="user-x" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-400">
                            <i data-lucide="user-minus" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                            <p class="font-semibold text-xs">Belum ada peserta yang ditempatkan pada jadwal ini.</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Gunakan tombol Penempatan Otomatis di atas atau tetapkan secara manual.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Penempatan Manual / Pindah Ruang -->
<div id="modal-penempatan-manual" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 id="modal-manual-title" class="text-sm font-extrabold text-navy-950 dark:text-white">Alokasi Ruang Manual</h3>
            <button onclick="document.getElementById('modal-penempatan-manual').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <form action="{{ route('admin.jadwal.penempatan.manual') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <input type="hidden" name="id_jadwal" value="{{ $jadwal->id_jadwal }}">
            <input type="hidden" id="manual_id_pendaftar" name="id_pendaftar" value="">

            <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                <span class="text-[10px] text-slate-400 font-bold uppercase block">Calon Siswa</span>
                <span id="manual_nama_siswa" class="font-extrabold text-xs text-navy-950 dark:text-white block uppercase">-</span>
                <span id="manual_no_daftar" class="font-mono text-pink-600 text-[11px] font-bold block">-</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                    Pilih Ruang Seleksi Tujuan <span class="text-rose-500">*</span>
                </label>
                <select id="manual_id_ruang" name="id_ruang" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500">
                    <option value="">-- Pilih Ruang --</option>
                    @foreach($ruangs as $r)
                        @php
                            $terisi = $r->terisi ?? 0;
                            $sisa = max(0, $r->kapasitas - $terisi);
                            $isFull = $sisa <= 0;
                        @endphp
                        <option value="{{ $r->id_ruang }}" {{ $isFull ? 'disabled' : '' }}>
                            {{ $r->nama_ruang }} ({{ $r->kode_ruang }}) - Sisa: {{ $sisa }}/{{ $r->kapasitas }} kursi {{ $isFull ? '(PENUH)' : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Hanya ruang dengan kapasitas tersedia yang dapat dipilih.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                    Catatan Khusus (Opsional)
                </label>
                <input type="text" name="catatan" placeholder="Contoh: Meja baris depan / kebutuhan khusus" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-pink-500">
            </div>

            <div class="border-t border-slate-100 dark:border-slate-800 pt-4 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-penempatan-manual').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-50">Batal</button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold transition">Simpan Penempatan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleSelectAll(btn) {
        const checkboxes = document.querySelectorAll('.checkbox-peserta');
        const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
        checkboxes.forEach(cb => cb.checked = anyUnchecked);
        btn.textContent = anyUnchecked ? 'Batalkan Semua' : 'Pilih Semua';
    }

    function openManualPlacement(idPendaftar, nama, noDaftar) {
        document.getElementById('modal-manual-title').textContent = 'Tetapkan Ruang Peserta';
        document.getElementById('manual_id_pendaftar').value = idPendaftar;
        document.getElementById('manual_nama_siswa').textContent = nama;
        document.getElementById('manual_no_daftar').textContent = noDaftar;
        document.getElementById('manual_id_ruang').value = '';
        document.getElementById('modal-penempatan-manual').classList.remove('hidden');
    }

    function openPindahRuang(idPendaftar, nama, noDaftar, idRuangSaatIni) {
        document.getElementById('modal-manual-title').textContent = 'Pindahkan Peserta ke Ruang Lain';
        document.getElementById('manual_id_pendaftar').value = idPendaftar;
        document.getElementById('manual_nama_siswa').textContent = nama;
        document.getElementById('manual_no_daftar').textContent = noDaftar;
        document.getElementById('manual_id_ruang').value = idRuangSaatIni;
        document.getElementById('modal-penempatan-manual').classList.remove('hidden');
    }
</script>
@endsection
