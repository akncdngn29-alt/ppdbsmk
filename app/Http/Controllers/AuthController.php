<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;
use App\Models\Gelombang;
use App\Models\Jurusan;
use App\Models\Pendaftar;
use App\Models\LogAktivitas;
use Carbon\Carbon;

class AuthController extends Controller
{
    // === CALON SISWA AUTH ===

    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('siswa.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : (is_numeric($request->login) ? 'nisn' : 'username');

        // Login Calon Siswa (Web Guard)
        if (Auth::guard('web')->attempt([$field => $request->login, 'password' => $request->password], $request->filled('remember'))) {
            $request->session()->regenerate();
            LogAktivitas::log('LOGIN', 'Calon Siswa login ke sistem');
            return redirect()->intended(route('siswa.dashboard'))->with('success', 'Selamat datang kembali, ' . Auth::user()->nama . '!');
        }

        // Jika terdeteksi akun admin, tolak dan beri tahu halaman yang benar
        if (Admin::where('email', $request->login)->orWhere('username', $request->login)->exists()) {
            return back()->withErrors([
                'login' => 'Akun ini adalah akun Staf/Admin. Silakan masuk melalui tautan "Masuk sebagai Petugas TU / Kepala Sekolah" di bawah.',
            ])->onlyInput('login');
        }

        return back()->withErrors([
            'login' => 'NISN, Email, atau Kata Sandi tidak sesuai.',
        ])->onlyInput('login');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('siswa.dashboard');
        }
        $gelombang = Gelombang::where('status_aktif', true)->first();
        $jurusan = Jurusan::all();
        return view('auth.register', compact('gelombang', 'jurusan'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nisn' => 'required|string|size:10|unique:user,nisn',
            'username' => 'required|string|min:4|max:50|unique:user,username|alpha_dash',
            'email' => 'required|email|max:100|unique:user,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'nisn.size' => 'NISN harus tepat 10 digit angka.',
            'nisn.unique' => 'NISN ini sudah terdaftar sebelumnya.',
            'username.unique' => 'Username ini sudah dipakai, silakan gunakan username lain.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'nama' => $validated['nama'],
            'nisn' => $validated['nisn'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        LogAktivitas::log('INSERT', 'Pendaftaran akun calon siswa baru', 'user', $user->id_user);

        return redirect()->route('siswa.dashboard')->with('success', 'Akun berhasil dibuat! Silakan lengkapi formulir pendaftaran Anda.');
    }

    public function logout(Request $request)
    {
        LogAktivitas::log('LOGOUT', 'Calon Siswa keluar dari sistem');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar.');
    }

    // === ADMIN & KEPALA SEKOLAH AUTH ===

    public function showAdminLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            $role = Auth::guard('admin')->user()->role;
            return $role === 'kepala_sekolah' ? redirect()->route('kepsek.dashboard') : redirect()->route('admin.dashboard');
        }
        return view('auth.admin_login');
    }

    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::guard('admin')->attempt([$field => $request->login, 'password' => $request->password], $request->filled('remember'))) {
            $request->session()->regenerate();
            $admin = Auth::guard('admin')->user();
            LogAktivitas::log('LOGIN', 'Admin login ke panel: ' . $admin->nama_admin);

            if ($admin->role === 'kepala_sekolah') {
                return redirect()->intended(route('kepsek.dashboard'))->with('success', 'Selamat datang di Panel Eksekutif, Bapak/Ibu Kepala Sekolah.');
            }
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Selamat bertugas di Panel TU, ' . $admin->nama_admin . '.');
        }

        // Tolak jika akun terdeteksi sebagai akun Calon Siswa
        $studentField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : (is_numeric($request->login) ? 'nisn' : 'username');
        if (User::where($studentField, $request->login)->exists()) {
            return back()->withErrors([
                'login' => 'Akun ini adalah akun Calon Siswa. Silakan masuk melalui tautan "Kembali ke Portal Calon Siswa" di bawah.',
            ])->onlyInput('login');
        }

        return back()->withErrors([
            'login' => 'Kredensial login administrator tidak valid.',
        ])->onlyInput('login');
    }

    public function adminLogout(Request $request)
    {
        LogAktivitas::log('LOGOUT', 'Admin keluar dari sistem');
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login')->with('info', 'Sesi administrator telah berakhir.');
    }

    // === LUPA PASSWORD ===

    public function showForgotPassword()
    {
        return view('auth.forgot_password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $user = User::where('email', $request->identifier)
            ->orWhere('nisn', $request->identifier)
            ->first();

        if (!$user) {
            return back()->withErrors(['identifier' => 'Data pendaftar dengan Email atau NISN tersebut tidak ditemukan.']);
        }

        // Simulation OTP/Link
        $simulatedOtp = rand(100000, 999999);
        return back()->with('success', "Kode verifikasi reset sandi telah disimulasikan terkirim ke WhatsApp / Email ({$user->email}). Kode demo reset: {$simulatedOtp}");
    }
}

