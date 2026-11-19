<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Admin;
use App\Models\LogAktivitas;

class ManajemenStafController extends Controller
{
    /**
     * Tampilkan daftar akun staf administrasi dan pimpinan.
     */
    public function index(Request $request)
    {
        $currentAdmin = auth('admin')->user();
        $query = Admin::query();

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_admin', 'LIKE', "%{$q}%")
                    ->orWhere('username', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $staf = $query->orderBy('id_admin', 'asc')->paginate(15)->withQueryString();

        $stats = [
            'total' => Admin::count(),
            'tu' => Admin::where('role', 'tu')->count(),
            'kepsek' => Admin::where('role', 'kepala_sekolah')->count(),
        ];

        return view('admin.staf.index', compact('staf', 'stats', 'currentAdmin'));
    }

    /**
     * Simpan akun staf baru.
     */
    public function store(Request $request)
    {
        $currentAdmin = auth('admin')->user();
        if ($currentAdmin->role !== 'kepala_sekolah') {
            abort(403, 'Akses ditolak. Hanya Kepala Sekolah yang berwenang menambahkan akun staf baru.');
        }

        $request->validate([
            'nama_admin' => 'required|string|max:100',
            'username' => 'required|string|max:50|alpha_dash|unique:admin,username',
            'email' => 'required|email|max:100|unique:admin,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:tu,kepala_sekolah',
        ], [
            'username.unique' => 'Username ini sudah terdaftar oleh staf lain.',
            'email.unique' => 'Alamat email ini sudah terdaftar oleh staf lain.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        $adminBaru = Admin::create([
            'nama_admin' => trim($request->nama_admin),
            'username' => strtolower(trim($request->username)),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        LogAktivitas::log(
            'TAMBAH_STAF',
            "{$currentAdmin->nama_admin} menambahkan akun staf baru: {$adminBaru->nama_admin} ({$adminBaru->role})",
            'admin',
            $adminBaru->id_admin,
            null,
            ['nama' => $adminBaru->nama_admin, 'username' => $adminBaru->username, 'role' => $adminBaru->role],
            'SUCCESS',
            null,
            $currentAdmin->id_admin,
            $currentAdmin->role
        );

        return back()->with('success', "Akun staf untuk {$adminBaru->nama_admin} berhasil ditambahkan.");
    }

    /**
     * Perbarui data akun staf.
     */
    public function update(Request $request, $id)
    {
        $currentAdmin = auth('admin')->user();
        $targetAdmin = Admin::findOrFail($id);

        $request->validate([
            'nama_admin' => 'required|string|max:100',
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('admin', 'username')->ignore($targetAdmin->id_admin, 'id_admin'),
            ],
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('admin', 'email')->ignore($targetAdmin->id_admin, 'id_admin'),
            ],
            'role' => 'required|in:tu,kepala_sekolah',
            'password' => 'nullable|string|min:6|confirmed',
        ], [
            'username.unique' => 'Username ini sudah terdaftar oleh staf lain.',
            'email.unique' => 'Alamat email ini sudah terdaftar oleh staf lain.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
        ]);

        // Proteksi Otorisasi: Petugas TU hanya dapat memperbarui akun miliknya sendiri
        if ($currentAdmin->role === 'tu') {
            if ($targetAdmin->id_admin !== $currentAdmin->id_admin) {
                abort(403, 'Akses ditolak. Petugas TU hanya berwenang memperbarui akun miliknya sendiri dan tidak dapat mengubah akun TU lain maupun Kepala Sekolah.');
            }
            if ($request->role === 'kepala_sekolah') {
                return back()->with('error', 'Petugas TU tidak dapat menaikkan hak akses menjadi Kepala Sekolah.');
            }
        }

        $dataLama = [
            'nama' => $targetAdmin->nama_admin,
            'username' => $targetAdmin->username,
            'email' => $targetAdmin->email,
            'role' => $targetAdmin->role,
        ];

        $targetAdmin->nama_admin = trim($request->nama_admin);
        $targetAdmin->username = strtolower(trim($request->username));
        $targetAdmin->email = strtolower(trim($request->email));

        // Hanya Kepala Sekolah yang dapat mengubah role akun staf
        if ($currentAdmin->role === 'kepala_sekolah') {
            $targetAdmin->role = $request->role;
        }

        if ($request->filled('password')) {
            $targetAdmin->password = Hash::make($request->password);
        }

        $targetAdmin->save();

        $dataBaru = [
            'nama' => $targetAdmin->nama_admin,
            'username' => $targetAdmin->username,
            'email' => $targetAdmin->email,
            'role' => $targetAdmin->role,
        ];

        LogAktivitas::log(
            'UPDATE_STAF',
            "{$currentAdmin->nama_admin} memperbarui profil akun staf: {$targetAdmin->nama_admin}",
            'admin',
            $targetAdmin->id_admin,
            $dataLama,
            $dataBaru,
            'SUCCESS',
            null,
            $currentAdmin->id_admin,
            $currentAdmin->role
        );

        return back()->with('success', "Data staf {$targetAdmin->nama_admin} berhasil diperbarui.");
    }

    /**
     * Hapus akun staf.
     */
    public function destroy($id)
    {
        $currentAdmin = auth('admin')->user();
        if ($currentAdmin->role !== 'kepala_sekolah') {
            abort(403, 'Akses ditolak. Hanya Kepala Sekolah yang memiliki wewenang menghapus akun staf.');
        }

        $targetAdmin = Admin::findOrFail($id);

        // 1. Tidak boleh menghapus akun yang sedang aktif digunakan login
        if ($targetAdmin->id_admin === $currentAdmin->id_admin) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif login.');
        }

        // 2. Pastikan minimal ada 1 Kepala Sekolah tersisa di sistem
        if ($targetAdmin->role === 'kepala_sekolah') {
            $totalKepsek = Admin::where('role', 'kepala_sekolah')->count();
            if ($totalKepsek <= 1) {
                return back()->with('error', 'Akun Kepala Sekolah terakhir tidak dapat dihapus demi kelangsungan sistem.');
            }
        }

        $namaHapus = $targetAdmin->nama_admin;
        $roleHapus = $targetAdmin->role;

        $targetAdmin->delete();

        LogAktivitas::log(
            'HAPUS_STAF',
            "{$currentAdmin->nama_admin} menghapus akun staf: {$namaHapus} ({$roleHapus})",
            'admin',
            $id,
            ['nama' => $namaHapus, 'role' => $roleHapus],
            null,
            'SUCCESS',
            null,
            $currentAdmin->id_admin,
            $currentAdmin->role
        );

        return back()->with('success', "Akun staf {$namaHapus} berhasil dihapus dari sistem.");
    }
}

