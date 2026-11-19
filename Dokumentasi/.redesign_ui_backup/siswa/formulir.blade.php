@extends('layouts.siswa')

@section('title', 'Formulir Pendaftaran Siswa Baru')

@section('content')
<div class="space-y-6">

        <!-- Page Header -->
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <a href="{{ route('siswa.dashboard') }}" class="text-xs font-semibold text-[#f43f5e] hover:underline flex items-center gap-1 mb-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Dashboard
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white">Formulir Pendaftaran Siswa Baru</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Lengkapi data pribadi, pilihan jurusan, asal sekolah, dan orang tua/wali dengan benar.</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold 
                    @if($pendaftar && $pendaftar->is_locked) bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700
                    @elseif($pendaftar) bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-700
                    @else bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-300 dark:border-slate-700 @endif">
                    <i data-lucide="{{ $pendaftar && $pendaftar->is_locked ? 'lock' : 'file-edit' }}" class="w-3.5 h-3.5"></i>
                    Status: {{ $pendaftar ? ($pendaftar->is_locked ? 'Difinalisasi (Terkunci)' : 'Draft Tersimpan') : 'Draft Baru' }}
                </span>
            </div>
        </div>

        @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 shrink-0 text-[#f43f5e]"></i>
            <div>{{ session('success') }}</div>
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 text-rose-600"></i>
            <div>{{ session('error') }}</div>
        </div>
        @endif

        @if(session('info'))
        <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 text-xs flex items-center gap-3">
            <i data-lucide="info" class="w-5 h-5 shrink-0 text-blue-600"></i>
            <div>{{ session('info') }}</div>
        </div>
        @endif

        @if($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs space-y-2">
            <div class="flex items-center gap-2 font-bold text-sm">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600"></i>
                <span>Terdapat Kolom yang Belum Lengkap / Format Tidak Sesuai:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 pl-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(!$pendaftar || !$pendaftar->is_locked)
        <div class="p-4 rounded-2xl bg-sky-50 dark:bg-sky-950/40 border border-sky-200 dark:border-sky-800 text-sky-800 dark:text-sky-300 text-xs flex items-center gap-3">
            <i data-lucide="info" class="w-5 h-5 shrink-0 text-sky-600"></i>
            <div>
                <strong>Pengisian Bertahap Didukung:</strong> Anda dapat menyimpan biodata sebagai <strong>Draft</strong> kapan saja dengan tombol <em>Simpan Draft</em> meskipun belum semua kolom terisi. Setelah seluruh persyaratan lengkap bertanda bintang (<span class="text-rose-500">*</span>), tekan tombol <strong>Finalisasi Formulir</strong> untuk mengunci data Anda.
            </div>
        </div>
        @endif

        @if($pendaftar && $pendaftar->is_locked)
        <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs flex items-center gap-3">
            <i data-lucide="lock" class="w-5 h-5 shrink-0 text-amber-600"></i>
            <div>
                <strong>Formulir Terkunci (Read-Only).</strong> Anda telah memfinalisasi data ini. Jika terdapat kesalahan pengisian nama atau jurusan, silakan hubungi Petugas TU di sekolah untuk membuka kunci.
            </div>
        </div>
        @endif

        <form action="{{ route('siswa.formulir.simpan') }}" method="POST" class="space-y-8">
            @csrf

            <!-- Section 1: Pilihan Jurusan & Jalur -->
            <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-3 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-pink-50 text-[#f43f5e] flex items-center justify-center font-bold text-sm">1</div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Pilihan Program Keahlian & Jalur PPDB</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="id_jurusan_1" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Pilihan Jurusan 1 (Prioritas Utama) <span class="text-rose-500">*</span></label>
                        <select id="id_jurusan_1" name="id_jurusan_1" required onchange="handleJurusanChange()" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                            <option value="">-- Pilih Jurusan 1 --</option>
                            @foreach($jurusan as $j)
                            <option value="{{ $j->id_jurusan }}" {{ old('id_jurusan_1', $pendaftar ? $pendaftar->id_jurusan_1 : '') == $j->id_jurusan ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="id_jurusan_2" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Pilihan Jurusan 2 (Cadangan Limpahan)</label>
                        <select id="id_jurusan_2" name="id_jurusan_2" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                            <option value="">-- Tidak Ada / Opsional --</option>
                            @foreach($jurusan as $j)
                            <option value="{{ $j->id_jurusan }}" {{ old('id_jurusan_2', $pendaftar ? $pendaftar->id_jurusan_2 : '') == $j->id_jurusan ? 'selected' : '' }}>
                                {{ $j->nama_jurusan }} ({{ $j->kode_jurusan }})
                            </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">Jurusan pilihan 2 tidak boleh sama dengan pilihan 1.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="jalur_pendaftaran" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Jalur Pendaftaran <span class="text-rose-500">*</span></label>
                        <select id="jalur_pendaftaran" name="jalur_pendaftaran" required class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                            <option value="Reguler" {{ old('jalur_pendaftaran', $pendaftar ? $pendaftar->jalur_pendaftaran : '') == 'Reguler' ? 'selected' : '' }}>Reguler (Nilai Rapor)</option>
                            <option value="Prestasi" {{ old('jalur_pendaftaran', $pendaftar ? $pendaftar->jalur_pendaftaran : '') == 'Prestasi' ? 'selected' : '' }}>Prestasi (Tahfiz/Seni/Olahraga)</option>
                            <option value="Afirmasi" {{ old('jalur_pendaftaran', $pendaftar ? $pendaftar->jalur_pendaftaran : '') == 'Afirmasi' ? 'selected' : '' }}>Afirmasi (KIP / SKTM)</option>
                            <option value="Zonasi" {{ old('jalur_pendaftaran', $pendaftar ? $pendaftar->jalur_pendaftaran : '') == 'Zonasi' ? 'selected' : '' }}>Zonasi (Domisili Metro)</option>
                        </select>
                    </div>

                    <div>
                        <label for="ukuran_seragam" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Ukuran Seragam Sekolah <span class="text-rose-500">*</span></label>
                        <select id="ukuran_seragam" name="ukuran_seragam" required class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                            @foreach(['S', 'M', 'L', 'XL', 'XXL', 'Custom'] as $sz)
                            <option value="{{ $sz }}" {{ old('ukuran_seragam', $pendaftar ? $pendaftar->ukuran_seragam : 'L') == $sz ? 'selected' : '' }}>Ukuran {{ $sz }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="ukuran_wearpack" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Ukuran Wearpack Praktek <span class="text-rose-500">*</span></label>
                        <select id="ukuran_wearpack" name="ukuran_wearpack" required class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                            @foreach(['S', 'M', 'L', 'XL', 'XXL', 'Custom'] as $sz)
                            <option value="{{ $sz }}" {{ old('ukuran_wearpack', $pendaftar ? $pendaftar->ukuran_wearpack : 'L') == $sz ? 'selected' : '' }}>Ukuran {{ $sz }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Peminatan Ekstrakurikuler (Boleh Pilih Lebih Dari Satu):</label>
                    @php
                        $chosenEkskul = $pendaftar && $pendaftar->pilihan_ekskul ? explode(',', $pendaftar->pilihan_ekskul) : [];
                        $ekskulList = [
                            'Tilawati Al-Qur\'an', 'Tafsir & Tahfiz Al-Qur\'an', 'Pramuka',
                            'Paskibra', 'Karate', 'Gamelan Karawitan', 'Gamelan Tolak Balak'
                        ];
                    @endphp
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                        @foreach($ekskulList as $ek)
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 cursor-pointer hover:border-[#f43f5e]">
                            <input type="checkbox" name="pilihan_ekskul[]" value="{{ $ek }}" {{ in_array($ek, $chosenEkskul) ? 'checked' : '' }} class="rounded text-[#f43f5e] focus:ring-[#f43f5e]">
                            <span class="text-slate-700 dark:text-slate-300">{{ $ek }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Section 2: Biodata Calon Siswa -->
            <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-3 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-pink-50 text-[#f43f5e] flex items-center justify-center font-bold text-sm">2</div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Biodata Pribadi Calon Siswa</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">NISN (Terkunci)</label>
                        <input type="text" disabled value="{{ $user->nisn }}" class="w-full px-4 py-3 rounded-xl bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-500 text-sm font-mono cursor-not-allowed">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap (Terkunci)</label>
                        <input type="text" disabled value="{{ $user->nama }}" class="w-full px-4 py-3 rounded-xl bg-slate-100 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 text-slate-500 text-sm cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="tempat_lahir" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Tempat Lahir <span class="text-rose-500">*</span></label>
                        <input type="text" id="tempat_lahir" name="tempat_lahir" required value="{{ old('tempat_lahir', $pendaftar ? $pendaftar->tempat_lahir : '') }}" placeholder="Contoh: Kota Metro" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                    <div>
                        <label for="tanggal_lahir" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Lahir <span class="text-rose-500">*</span></label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" required value="{{ old('tanggal_lahir', $pendaftar && $pendaftar->tanggal_lahir ? $pendaftar->tanggal_lahir->format('Y-m-d') : '') }}" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                    <div>
                        <label for="jenis_kelamin" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                        <select id="jenis_kelamin" name="jenis_kelamin" required class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                            <option value="Laki-laki" {{ old('jenis_kelamin', $pendaftar ? $pendaftar->jenis_kelamin : '') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin', $pendaftar ? $pendaftar->jenis_kelamin : '') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div>
                        <label for="agama" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Agama <span class="text-rose-500">*</span></label>
                        <input type="text" id="agama" name="agama" required value="{{ old('agama', $pendaftar ? $pendaftar->agama : 'Islam') }}" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                    <div>
                        <label for="anak_ke" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Anak Ke- <span class="text-rose-500">*</span></label>
                        <input type="number" id="anak_ke" name="anak_ke" min="1" required value="{{ old('anak_ke', $pendaftar ? $pendaftar->anak_ke : '1') }}" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                    <div>
                        <label for="jml_saudara" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Jumlah Saudara <span class="text-rose-500">*</span></label>
                        <input type="number" id="jml_saudara" name="jml_saudara" min="0" required value="{{ old('jml_saudara', $pendaftar ? $pendaftar->jml_saudara : '0') }}" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                    <div>
                        <label for="hp_siswa" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">No. WhatsApp Siswa <span class="text-rose-500">*</span></label>
                        <input type="text" id="hp_siswa" name="hp_siswa" required value="{{ old('hp_siswa', $pendaftar ? $pendaftar->hp_siswa : '') }}" placeholder="0821xxxxxxxx" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="berat_badan" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Berat Badan (Kg) <span class="text-rose-500">*</span></label>
                        <input type="text" id="berat_badan" name="berat_badan" required value="{{ old('berat_badan', $pendaftar ? $pendaftar->berat_badan : '50') }}" placeholder="Contoh: 52" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                    <div>
                        <label for="tinggi_badan" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Tinggi Badan (Cm) <span class="text-rose-500">*</span></label>
                        <input type="text" id="tinggi_badan" name="tinggi_badan" required value="{{ old('tinggi_badan', $pendaftar ? $pendaftar->tinggi_badan : '160') }}" placeholder="Contoh: 165" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                    <div>
                        <label for="golongan_darah" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Golongan Darah <span class="text-rose-500">*</span></label>
                        <select id="golongan_darah" name="golongan_darah" required class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                            @foreach(['A', 'B', 'AB', 'O', '-'] as $gd)
                            <option value="{{ $gd }}" {{ old('golongan_darah', $pendaftar ? $pendaftar->golongan_darah : 'O') == $gd ? 'selected' : '' }}>Golongan {{ $gd }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="almt_siswa" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Alamat Tempat Tinggal Lengkap <span class="text-rose-500">*</span></label>
                    <textarea id="almt_siswa" name="almt_siswa" rows="2" required placeholder="Nama jalan, RT/RW, Kelurahan/Desa, Kecamatan, Kota/Kabupaten" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">{{ old('almt_siswa', $pendaftar ? $pendaftar->almt_siswa : '') }}</textarea>
                </div>
            </div>

            <!-- Section 3: Data Asal Sekolah -->
            <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-3 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-pink-50 text-[#f43f5e] flex items-center justify-center font-bold text-sm">3</div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Data Asal Sekolah (SMP / MTs)</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="asal_sekolah" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nama SMP / MTs Asal <span class="text-rose-500">*</span></label>
                        <input type="text" id="asal_sekolah" name="asal_sekolah" required value="{{ old('asal_sekolah', $pendaftar ? $pendaftar->asal_sekolah : '') }}" placeholder="Contoh: SMP Negeri 1 Metro" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>

                    <div>
                        <label for="alamat_sekolah" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Alamat / Kota Asal Sekolah</label>
                        <input type="text" id="alamat_sekolah" name="alamat_sekolah" value="{{ old('alamat_sekolah', $pendaftar ? $pendaftar->alamat_sekolah : '') }}" placeholder="Contoh: Jl. Raden Intan No. 12, Kota Metro" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                </div>
            </div>

            <!-- Section 4: Data Orang Tua / Wali -->
            <div class="p-6 md:p-8 rounded-3xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm space-y-6">
                <div class="border-b border-slate-200 dark:border-slate-700 pb-3 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-pink-50 text-[#f43f5e] flex items-center justify-center font-bold text-sm">4</div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Data Orang Tua / Wali Murid</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_ayah" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nama Ayah Kandung <span class="text-rose-500">*</span></label>
                        <input type="text" id="nama_ayah" name="nama_ayah" required value="{{ old('nama_ayah', $pendaftar ? $pendaftar->nama_ayah : '') }}" placeholder="Nama lengkap ayah" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                    <div>
                        <label for="kerja_ayah" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Pekerjaan Ayah <span class="text-rose-500">*</span></label>
                        <input type="text" id="kerja_ayah" name="kerja_ayah" required value="{{ old('kerja_ayah', $pendaftar ? $pendaftar->kerja_ayah : '') }}" placeholder="Contoh: Wiraswasta / PNS / Petani" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nama_ibu" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nama Ibu Kandung <span class="text-rose-500">*</span></label>
                        <input type="text" id="nama_ibu" name="nama_ibu" required value="{{ old('nama_ibu', $pendaftar ? $pendaftar->nama_ibu : '') }}" placeholder="Nama lengkap ibu" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                    <div>
                        <label for="kerja_ibu" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Pekerjaan Ibu <span class="text-rose-500">*</span></label>
                        <input type="text" id="kerja_ibu" name="kerja_ibu" required value="{{ old('kerja_ibu', $pendaftar ? $pendaftar->kerja_ibu : '') }}" placeholder="Contoh: Ibu Rumah Tangga / Guru / Pedagang" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="hp_ortu" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">No. WhatsApp Orang Tua <span class="text-rose-500">*</span></label>
                        <input type="text" id="hp_ortu" name="hp_ortu" required value="{{ old('hp_ortu', $pendaftar ? $pendaftar->hp_ortu : '') }}" placeholder="0812xxxxxxxx" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                    <div>
                        <label for="penghasilan_ortu" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Penghasilan Rata-rata Ortu <span class="text-rose-500">*</span></label>
                        <select id="penghasilan_ortu" name="penghasilan_ortu" required class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                            <option value="< Rp 1.000.000" {{ old('penghasilan_ortu', $pendaftar ? $pendaftar->penghasilan_ortu : '') == '< Rp 1.000.000' ? 'selected' : '' }}>< Rp 1.000.000</option>
                            <option value="Rp 1.000.000 - Rp 3.000.000" {{ old('penghasilan_ortu', $pendaftar ? $pendaftar->penghasilan_ortu : '') == 'Rp 1.000.000 - Rp 3.000.000' ? 'selected' : '' }}>Rp 1.000.000 - Rp 3.000.000</option>
                            <option value="Rp 3.000.000 - Rp 5.000.000" {{ old('penghasilan_ortu', $pendaftar ? $pendaftar->penghasilan_ortu : '') == 'Rp 3.000.000 - Rp 5.000.000' ? 'selected' : '' }}>Rp 3.000.000 - Rp 5.000.000</option>
                            <option value="> Rp 5.000.000" {{ old('penghasilan_ortu', $pendaftar ? $pendaftar->penghasilan_ortu : '') == '> Rp 5.000.000' ? 'selected' : '' }}>> Rp 5.000.000</option>
                        </select>
                    </div>
                    <div>
                        <label for="tanggungan_anak" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Jumlah Tanggungan Anak <span class="text-rose-500">*</span></label>
                        <input type="number" id="tanggungan_anak" name="tanggungan_anak" min="1" required value="{{ old('tanggungan_anak', $pendaftar ? $pendaftar->tanggungan_anak : '2') }}" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                </div>

                <div>
                    <label for="almt_ortu" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Alamat Tempat Tinggal Orang Tua <span class="text-rose-500">*</span></label>
                    <textarea id="almt_ortu" name="almt_ortu" rows="2" required placeholder="Alamat lengkap domisili orang tua" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">{{ old('almt_ortu', $pendaftar ? $pendaftar->almt_ortu : '') }}</textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <div>
                        <label for="nama_wali" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nama Wali (Opsional jika tinggal bersama wali)</label>
                        <input type="text" id="nama_wali" name="nama_wali" value="{{ old('nama_wali', $pendaftar ? $pendaftar->nama_wali : '') }}" placeholder="Nama wali jika ada" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                    <div>
                        <label for="kerja_wali" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Pekerjaan Wali (Opsional)</label>
                        <input type="text" id="kerja_wali" name="kerja_wali" value="{{ old('kerja_wali', $pendaftar ? $pendaftar->kerja_wali : '') }}" placeholder="Pekerjaan wali jika ada" class="w-full px-4 py-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-[#f43f5e] focus:ring-1 focus:ring-[#f43f5e]">
                    </div>
                </div>
            </div>

            <!-- Submit Button Bar -->
            @if(!$pendaftar || !$pendaftar->is_locked)
            <div class="flex items-center justify-between flex-wrap gap-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                <a href="{{ route('siswa.dashboard') }}" class="px-5 py-3 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Dashboard
                </a>
                <div class="flex items-center gap-3">
                    <button type="submit" name="action" value="draft" formnovalidate class="px-6 py-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-200 font-bold text-sm shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4 text-[#f43f5e]"></i> Simpan Draft
                    </button>
                    <button type="submit" name="action" value="finalisasi" onclick="return confirm('Apakah Anda yakin seluruh data formulir sudah lengkap dan benar? Setelah difinalisasi, formulir akan terkunci dan tidak dapat diubah lagi tanpa persetujuan Admin TU.');" class="px-7 py-3.5 rounded-xl bg-[#f43f5e] hover:bg-[#e11d48] text-white font-bold text-sm shadow-md transition-all flex items-center gap-2">
                        <i data-lucide="lock" class="w-4 h-4"></i> Finalisasi Formulir
                    </button>
                </div>
            </div>
            @endif
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function handleJurusanChange() {
        const j1 = document.getElementById('id_jurusan_1').value;
        const j2Select = document.getElementById('id_jurusan_2');

        // Disable matching option in J2
        Array.from(j2Select.options).forEach(opt => {
            if (opt.value && opt.value === j1) {
                opt.disabled = true;
                if (j2Select.value === j1) {
                    j2Select.value = '';
                }
            } else {
                opt.disabled = false;
            }
        });
    }

    // Run on load to init disabled state
    document.addEventListener('DOMContentLoaded', handleJurusanChange);
</script>
@endpush

