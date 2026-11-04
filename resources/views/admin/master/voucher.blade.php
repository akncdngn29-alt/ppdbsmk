@extends('layouts.admin')

@section('title', 'Master Voucher Diskon - PPDB SMK IB')
@section('header_title', 'Voucher Beasiswa Tahfiz & Diskon Formulir')

@section('content')
<div class="space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Form Tambah Voucher -->
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Buat Kode Voucher Baru</h3>
            <form action="{{ route('admin.master.voucher.simpan') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Kode Voucher (Huruf Kapital Unik)</label>
                    <input type="text" name="kode_voucher" required placeholder="Contoh: TAHFIZ100" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold uppercase">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tipe Potongan</label>
                    <select name="tipe" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold">
                        <option value="nominal">Nominal Rupiah (Rp)</option>
                        <option value="persen">Persentase (%)</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Nilai Potongan (Rp atau %)</label>
                    <input type="number" name="nilai_potongan" required min="1" step="1000" value="50000" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono font-bold">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Kuota Pemakaian</label>
                    <input type="number" name="kuota_voucher" required min="1" value="50" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-mono">
                </div>

                <button type="submit" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-colors">
                    Simpan Kode Voucher
                </button>
            </form>
        </div>

        <!-- List Voucher -->
        <div class="lg:col-span-2 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">Daftar Voucher Aktif</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 dark:bg-slate-800 text-slate-500 uppercase font-bold text-[10px]">
                        <tr>
                            <th class="p-3">Kode Voucher</th>
                            <th class="p-3">Tipe</th>
                            <th class="p-3">Besaran Potongan</th>
                            <th class="p-3">Sisa Kuota</th>
                            <th class="p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($vouchers as $v)
                        <tr>
                            <td class="p-3 font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ $v->kode_voucher }}</td>
                            <td class="p-3 uppercase font-medium">{{ $v->tipe }}</td>
                            <td class="p-3 font-mono font-bold text-slate-900 dark:text-white">
                                {{ $v->tipe === 'nominal' ? 'Rp ' . number_format($v->nilai_potongan, 0, ',', '.') : $v->nilai_potongan . '%' }}
                            </td>
                            <td class="p-3 font-mono">{{ $v->kuota_voucher }} kali</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $v->status_aktif ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $v->status_aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

