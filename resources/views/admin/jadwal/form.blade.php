@extends('layouts.admin')

@php
    $isEdit = !empty($jadwal);
    $title = $isEdit ? 'Ubah Jadwal Seleksi' : 'Tambah Jadwal Seleksi';
@endphp

@section('title', $title)
@section('header_title', $title)

@section('content')
<div class="space-y-6">

    <!-- Back Button & Title -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.jadwal.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-white transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke Daftar Jadwal</span>
        </a>
        <span class="text-xs text-slate-400 font-semibold">{{ $isEdit ? 'Mode Edit Agenda' : 'Agenda Seleksi Baru' }}</span>
    </div>

    <!-- Card Form -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 sm:p-8">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-4 mb-6">
            <h2 class="text-lg font-black text-slate-800 dark:text-white">{{ $title }}</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Atur rincian tes wawancara, tes baca Al-Qur'an (tahfiz), tes buta warna, atau tes akademik.</p>
        </div>

        <form action="{{ $isEdit ? route('admin.jadwal.update', $jadwal->id_jadwal) : route('admin.jadwal.store') }}" method="POST" class="space-y-5">
            @csrf
            @if($isEdit)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Nama Jadwal -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Nama Jadwal / Agenda <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="nama_jadwal" value="{{ old('nama_jadwal', $jadwal->nama_jadwal ?? '') }}" required placeholder="Contoh: Tes Seleksi Masuk Gelombang 1 - Sesi Pagi" class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('nama_jadwal') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Jenis Seleksi -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Jenis Seleksi <span class="text-rose-500">*</span>
                    </label>
                    <select name="jenis_seleksi" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @php
                            $selectedJenis = old('jenis_seleksi', $jadwal->jenis_seleksi ?? 'Wawancara & Tahfiz');
                            $opsiJenis = [
                                'Wawancara & Tahfiz' => "Tes Wawancara Minat Bakat & Tahfiz",
                                'Tes Buta Warna'     => "Tes Buta Warna & Kesehatan Fisik",
                                'Tes Akademik'       => "Tes Potensi Akademik (TPA)",
                                'Seleksi Terpadu'    => "Seleksi Terpadu (Wawancara, Tahfiz & Tes Fisik)",
                                'Lainnya'            => "Seleksi Khusus / Lainnya",
                            ];
                        @endphp
                        @foreach($opsiJenis as $val => $lbl)
                            <option value="{{ $val }}" {{ $selectedJenis === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    @error('jenis_seleksi') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Status Aktif / Nonaktif -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Status Jadwal <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="aktif" {{ old('status', $jadwal->status ?? 'aktif') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="nonaktif" {{ old('status', $jadwal->status ?? '') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('status') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Tanggal Pelaksanaan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', isset($jadwal->tanggal) ? (is_string($jadwal->tanggal) ? $jadwal->tanggal : $jadwal->tanggal->format('Y-m-d')) : date('Y-m-d')) }}" required class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('tanggal') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Jam Mulai & Selesai -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                            Jam Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" name="waktu_mulai" value="{{ old('waktu_mulai', isset($jadwal->waktu_mulai) ? substr($jadwal->waktu_mulai, 0, 5) : '08:00') }}" required class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @error('waktu_mulai') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                            Jam Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input type="time" name="waktu_selesai" value="{{ old('waktu_selesai', isset($jadwal->waktu_selesai) ? substr($jadwal->waktu_selesai, 0, 5) : '12:00') }}" required class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @error('waktu_selesai') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Lokasi Kampus -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Lokasi / Kampus
                    </label>
                    <input type="text" name="lokasi" value="{{ old('lokasi', $jadwal->lokasi ?? 'Kampus SMK Islam Bina Khalifah Bangsa, Kota Metro') }}" placeholder="Kampus SMK..." class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('lokasi') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Gedung -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Gedung
                    </label>
                    <input type="text" name="gedung" value="{{ old('gedung', $jadwal->gedung ?? 'Gedung Utama / Lantai 2') }}" placeholder="Gedung A / Lab Komputer..." class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @error('gedung') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Keterangan Tambahan / Tata Tertib -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">
                        Keterangan Tambahan untuk Peserta
                    </label>
                    <textarea name="keterangan" rows="3" placeholder="Informasi pakaian, perlengkapan yang dibawa, atau instruksi panitia..." class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-emerald-500">{{ old('keterangan', $jadwal->keterangan ?? 'Wajib membawa cetakan Kartu Peserta Ujian, mengenakan seragam SMP asal rapi & bersepatu, dan hadir 15 menit sebelum tes dimulai.') }}</textarea>
                    @error('keterangan') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-800 pt-5 flex items-center justify-end gap-3">
                <a href="{{ route('admin.jadwal.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#fa2f79] hover:bg-[#e11d48] text-white text-xs font-bold shadow-md shadow-pink-500/25 transition-colors">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Jadwal' }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

