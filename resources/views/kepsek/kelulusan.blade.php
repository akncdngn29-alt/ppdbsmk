@extends('layouts.admin')

@section('title', 'Sidang Pengesahan Kelulusan')
@section('header_title', 'Sidang Pleno & Pengesahan Kelulusan Calon Siswa')

@section('content')
<div class="space-y-6">

    <!-- Header Description Card -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-pink-100 text-[#fa2f79] dark:bg-pink-950/60 dark:text-pink-400 flex items-center justify-center shrink-0">
                    <i data-lucide="award" class="w-6 h-6"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-white">Otoritas Pengesahan Surat Keterangan Lulus (SKL)</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-2xl leading-relaxed">
                        Hanya calon siswa yang telah melunasi biaya pendaftaran yang tampil di daftar sidang ini. Kepala Sekolah memiliki hak prerogatif untuk mengesahkan status <strong>Diterima</strong>, <strong>Cadangan</strong>, atau <strong>Tidak Lolos</strong>, serta mengalihkan penerimaan ke pilihan jurusan ke-2 apabila kuota jurusan utama telah penuh.
                    </p>
                </div>
            </div>

            <!-- Filter Jurusan -->
            <form action="{{ route('kepsek.kelulusan') }}" method="GET" class="flex items-center gap-2 shrink-0">
                <select name="jurusan" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                    <option value="">Semua Program Keahlian</option>
                    @foreach($jurusan as $j)
                        <option value="{{ $j->id_jurusan }}" {{ request('jurusan') == $j->id_jurusan ? 'selected' : '' }}>
                            {{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})
                        </option>
                    @endforeach
                </select>
                @if(request('jurusan'))
                    <a href="{{ route('kepsek.kelulusan') }}" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-700 text-xs">
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Tabel Daftar Calon Siswa & Pengesahan -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i data-lucide="clipboard-check" class="w-4 h-4 text-[#fa2f79]"></i>
                Daftar Calon Siswa Terverifikasi
            </h3>
            <span class="text-xs text-slate-500 dark:text-slate-400">Total: {{ $pendaftar->total() }} Calon Siswa</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-bold border-b border-slate-200 dark:border-slate-800">
                        <th class="py-3 px-4">Calon Siswa</th>
                        <th class="py-3 px-4">Pilihan Jurusan</th>
                        <th class="py-3 px-4">Hasil Seleksi & Tes</th>
                        <th class="py-3 px-4 text-center">Status Kelulusan</th>
                        <th class="py-3 px-4 text-center">Aksi / Pengesahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($pendaftar as $p)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors">
                            <!-- Calon Siswa -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 dark:text-white text-xs">{{ $p->nama }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">NISN: {{ $p->nisn }} &bull; {{ $p->no_pendaftaran }}</div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">{{ $p->asal_sekolah }}</div>
                            </td>

                            <!-- Pilihan Jurusan -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-[#fa2f79] dark:text-pink-400">
                                    1. {{ $p->jurusan1 ? $p->jurusan1->nama_jurusan : '-' }}
                                </div>
                                @if($p->jurusan2)
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                        2. {{ $p->jurusan2->nama_jurusan }}
                                    </div>
                                @endif
                            </td>

                            <!-- Hasil Seleksi & Tes -->
                            <td class="py-3.5 px-4">
                                @if($p->nilaiSeleksi)
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded font-black text-xs {{ $p->nilaiSeleksi->total_nilai >= 75 ? 'bg-pink-100 text-pink-700 dark:bg-pink-950 dark:text-pink-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400' }}">
                                            Skor: {{ number_format($p->nilaiSeleksi->total_nilai, 1) }}
                                        </span>
                                        <span class="text-[10px] text-slate-400">
                                            (Rapor: {{ $p->nilaiSeleksi->nilai_rapor }}, Wawancara: {{ $p->nilaiSeleksi->nilai_wawancara }})
                                        </span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                        Tes Buta Warna: <span class="font-semibold {{ $p->nilaiSeleksi->tes_buta_warna === 'Normal' ? 'text-[#fa2f79]' : 'text-rose-500' }}">{{ $p->nilaiSeleksi->tes_buta_warna }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">Belum diinput TU</span>
                                @endif

                                @if($p->prestasi && count($p->prestasi) > 0)
                                    <div class="mt-1 flex items-center gap-1 text-[10px] text-indigo-600 dark:text-indigo-400 font-semibold">
                                        <i data-lucide="trophy" class="w-3 h-3"></i> {{ count($p->prestasi) }} Sertifikat Prestasi
                                    </div>
                                @endif
                            </td>

                            <!-- Status Kelulusan -->
                            <td class="py-3.5 px-4 text-center">
                                @if($p->status_kelulusan === 'Diterima')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-pink-100 text-pink-800 dark:bg-pink-950/80 dark:text-pink-300">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Diterima
                                    </span>
                                    @if($p->terima)
                                        <div class="text-[10px] text-slate-400 font-mono mt-1">
                                            {{ $p->terima->no_surat_kelulusan }}
                                        </div>
                                    @endif
                                @elseif($p->status_kelulusan === 'Cadangan')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300">
                                        <i data-lucide="clock" class="w-3 h-3"></i> Cadangan
                                    </span>
                                @elseif($p->status_kelulusan === 'Tidak Lolos')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300">
                                        <i data-lucide="x-circle" class="w-3 h-3"></i> Tidak Lolos
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                        <i data-lucide="help-circle" class="w-3 h-3"></i> Menunggu Keputusan
                                    </span>
                                @endif
                            </td>

                            <!-- Form Keputusan / Aksi -->
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" onclick="openDecisionModal('{{ $p->id_pendaftar }}', '{{ addslashes($p->nama) }}', '{{ $p->status_kelulusan }}', '{{ $p->id_jurusan_1 }}', '{{ $p->id_jurusan_2 }}', '{{ $p->terima ? addslashes($p->terima->keterangan) : '' }}')"
                                        class="px-3.5 py-1.5 rounded-xl bg-slate-900 dark:bg-white text-white dark:text-slate-900 font-bold text-xs hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors shadow-sm inline-flex items-center gap-1.5">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Sahkan
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                Tidak ada data calon siswa yang siap disahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $pendaftar->links() }}
        </div>
    </div>

</div>

<!-- Modal Dialog Pengesahan Kelulusan -->
<div id="decisionModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full border border-slate-200 dark:border-slate-800 shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i data-lucide="award" class="w-5 h-5 text-[#fa2f79]"></i>
                <h3 class="text-sm font-bold text-slate-800 dark:text-white">Keputusan Sidang Kelulusan</h3>
            </div>
            <button onclick="closeDecisionModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="decisionForm" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <span class="text-xs text-slate-500 dark:text-slate-400 block">Nama Calon Siswa:</span>
                <span id="modalSiswaNama" class="text-sm font-bold text-slate-900 dark:text-white"></span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Keputusan Akhir *</label>
                <select name="status_kelulusan" id="modalStatusKelulusan" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white font-bold focus:outline-none focus:ring-2 focus:ring-pink-400">
                    <option value="Diterima">Diterima (Terbitkan SKL)</option>
                    <option value="Cadangan">Cadangan (Waiting List)</option>
                    <option value="Tidak Lolos">Tidak Lolos Seleksi</option>
                </select>
            </div>

            <div id="jurusanDiterimaContainer">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Diterima Pada Program Keahlian *</label>
                <select name="id_jurusan_diterima" id="modalJurusanDiterima" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400">
                    @foreach($jurusan as $j)
                        <option value="{{ $j->id_jurusan }}">{{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Anda dapat mengalihkan penerimaan ke pilihan jurusan ke-2 apabila kuota jurusan ke-1 penuh.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Catatan Pengesahan / Keterangan SKL</label>
                <textarea name="catatan" id="modalCatatan" rows="3" placeholder="Contoh: Dinyatakan Lulus Seleksi PPDB SMK Islam Bina Khalifah Bangsa Tahun Ajaran 2026/2027."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-pink-400"></textarea>
            </div>

            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-2">
                <button type="button" onclick="closeDecisionModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white font-bold text-xs flex items-center gap-1.5 shadow-md shadow-pink-500/25 transition-colors">
                    <i data-lucide="check-check" class="w-4 h-4"></i> Simpan & Sahkan Keputusan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openDecisionModal(id, nama, status, j1, j2, keterangan) {
        const form = document.getElementById('decisionForm');
        const routeTemplate = @json(route('kepsek.kelulusan.sahkan', ['id' => '__ID__']));
        form.action = routeTemplate.replace('__ID__', id);
        document.getElementById('modalSiswaNama').textContent = nama;
        
        const statusSelect = document.getElementById('modalStatusKelulusan');
        if (status && ['Diterima', 'Cadangan', 'Tidak Lolos'].includes(status)) {
            statusSelect.value = status;
        } else {
            statusSelect.value = 'Diterima';
        }

        const jurusanSelect = document.getElementById('modalJurusanDiterima');
        if (j1) {
            jurusanSelect.value = j1;
        }

        document.getElementById('modalCatatan').value = keterangan || 'Dinyatakan Lulus Seleksi PPDB SMK Islam Bina Khalifah Bangsa.';
        
        toggleJurusanVisibility();
        document.getElementById('decisionModal').classList.remove('hidden');
    }

    function closeDecisionModal() {
        document.getElementById('decisionModal').classList.add('hidden');
    }

    function toggleJurusanVisibility() {
        const status = document.getElementById('modalStatusKelulusan').value;
        const container = document.getElementById('jurusanDiterimaContainer');
        if (status === 'Diterima') {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }

    document.getElementById('modalStatusKelulusan').addEventListener('change', toggleJurusanVisibility);
</script>
@endpush
@endsection

