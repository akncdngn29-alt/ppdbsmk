<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
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

    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('siswa.dashboard');
        }

        $isLocal = (app()->isLocal() || app()->runningUnitTests()) && config('app.debug') && $this->isLoopbackRequest($request);
        $sampleStudents = $isLocal ? $this->getDummyStudents() : collect([]);
        $adminTU = $isLocal ? Admin::where('role', 'tu')->first() : null;
        $kepsek = $isLocal ? Admin::where('role', 'kepala_sekolah')->first() : null;

        return view('auth.login', compact('sampleStudents', 'adminTU', 'kepsek', 'isLocal'));
    }

    /**
     * Dapatkan data akun siswa pendaftar untuk simulasi pintasan masuk di lingkungan lokal.
     */
    protected function getDummyStudents()
    {
        try {
            $students = User::with('pendaftar.jurusan1')
                ->orderBy('id_user', 'asc')
                ->take(25)
                ->get();

            if ($students->isNotEmpty()) {
                return $students->map(function ($u) {
                    $namaJurusan = 'Calon Siswa';
                    if ($u->pendaftar && $u->pendaftar->jurusan1) {
                        $namaJurusan = $u->pendaftar->jurusan1->nama_jurusan;
                    }

                    return (object)[
                        'id_user' => $u->id_user,
                        'nama' => $u->nama,
                        'nisn' => $u->nisn,
                        'email' => $u->email,
                        'nama_jurusan' => $namaJurusan,
                    ];
                });
            }
        } catch (\Throwable $e) {
            // Fallback jika database belum siap/tidak terjangkau
        }

        $demoUser = null;
        try {
            $demoUser = User::first();
        } catch (\Throwable $e) {
            // ignore
        }
        $demoId = $demoUser ? $demoUser->id_user : 1;

        return collect([
            (object)[
                'id_user' => $demoId,
                'nama' => 'Ahmad Fauzi (Akun Uji Coba)',
                'nisn' => '0051234567',
                'email' => 'siswa.demo1@smkib.test',
                'nama_jurusan' => 'Teknik Komputer & Jaringan',
            ],
            (object)[
                'id_user' => $demoId,
                'nama' => 'Siti Nurhaliza (Akun Uji Coba)',
                'nisn' => '0057654321',
                'email' => 'siswa.demo2@smkib.test',
                'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            ],
            (object)[
                'id_user' => $demoId,
                'nama' => 'Rizky Pratama (Akun Uji Coba)',
                'nisn' => '0059876543',
                'email' => 'siswa.demo3@smkib.test',
                'nama_jurusan' => 'Desain Komunikasi Visual',
            ],
        ]);
    }

    /**
     * Buat kunci unik rate limiting berdasarkan guard, identitas akun, dan IP pengguna.
     */
    protected function throttleKey(Request $request, string $guard): string
    {
        $identifier = Str::lower(trim((string) $request->input('login')));
        return 'login:' . $guard . ':' . Str::transliterate($identifier) . '|' . $request->ip();
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $throttleKey = $this->throttleKey($request, 'web');

        // Batasi maksimal 5 percobaan gagal dengan jeda 60 detik
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'login' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('login');
        }

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : (is_numeric($request->login) ? 'nisn' : 'username');

        // Login Calon Siswa (Web Guard)
        if (Auth::guard('web')->attempt([$field => $request->login, 'password' => $request->password], $request->filled('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            LogAktivitas::log('LOGIN', 'Calon Siswa login ke sistem: ' . Auth::user()->nama, 'user', Auth::id(), null, null, 'SUCCESS', Auth::id(), null, 'calon_siswa');
            return redirect()->intended(route('siswa.dashboard'))->with('success', 'Selamat datang kembali, ' . Auth::user()->nama . '!');
        }

        // Catat percobaan gagal ke RateLimiter
        RateLimiter::hit($throttleKey, 60);

        // Catat login gagal jika akun siswa ditemukan
        $failedUser = User::where($field, $request->login)->first();
        if ($failedUser) {
            LogAktivitas::log(
                'LOGIN_GAGAL',
                'Percobaan login gagal untuk akun calon siswa: ' . $failedUser->nama,
                'user',
                $failedUser->id_user,
                null,
                null,
                'FAILED',
                $failedUser->id_user,
                null,
                'calon_siswa'
            );
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
        $jurusan = Jurusan::where('status_aktif', true)->get();
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
        LogAktivitas::log('DAFTAR_AKUN', 'Pendaftaran akun calon siswa baru: ' . $user->nama, 'user', $user->id_user, null, null, 'SUCCESS', $user->id_user, null, 'calon_siswa');

        return redirect()->route('siswa.dashboard')->with('success', 'Akun berhasil dibuat! Silakan lengkapi formulir pendaftaran Anda.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            LogAktivitas::log('LOGOUT', 'Calon Siswa keluar dari sistem: ' . $user->nama, 'user', $user->id_user, null, null, 'SUCCESS', $user->id_user, null, 'calon_siswa');
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar.');
    }

    // === ADMIN & KEPALA SEKOLAH AUTH ===

    public function showAdminLoginForm(Request $request)
    {
        if (Auth::guard('admin')->check()) {
            $role = Auth::guard('admin')->user()->role;
            return $role === 'kepala_sekolah' ? redirect()->route('kepsek.dashboard') : redirect()->route('admin.dashboard');
        }

        $isLocal = (app()->isLocal() || app()->runningUnitTests()) && config('app.debug') && $this->isLoopbackRequest($request);
        $sampleStudents = $isLocal ? $this->getDummyStudents() : collect([]);
        $adminTU = $isLocal ? Admin::where('role', 'tu')->first() : null;
        $kepsek = $isLocal ? Admin::where('role', 'kepala_sekolah')->first() : null;

        return view('auth.admin_login', compact('sampleStudents', 'adminTU', 'kepsek', 'isLocal'));
    }

    /**
     * Verifikasi bahwa request berasal secara murni dari host dan antarmuka loopback lokal (localhost).
     * Mencegah akses via jaringan LAN, IP publik, atau tunnel proxy eksternal (ngrok, cloudflare tunnel, dll).
     */
    protected function isLoopbackRequest(Request $request): bool
    {
        $remoteAddr = $request->server('REMOTE_ADDR');
        $clientIp = $request->ip();

        // 1. Remote address fisik koneksi TCP dan client IP harus loopback
        $isLoopbackIp = in_array($remoteAddr, ['127.0.0.1', '::1'], true)
            && in_array($clientIp, ['127.0.0.1', '::1'], true);

        // 2. Host header harus localhost / 127.0.0.1 (menolak domain tunnel seperti *.ngrok-free.app, *.trycloudflare.com, dll)
        $host = strtolower($request->getHost());
        $isLoopbackHost = in_array($host, ['localhost', '127.0.0.1', '[::1]'], true);

        // 3. Mencegah spoofing jika request melewati tunnel/reverse proxy eksternal
        $forwardedFor = $request->header('X-Forwarded-For');
        if ($forwardedFor) {
            $ips = array_map('trim', explode(',', $forwardedFor));
            foreach ($ips as $ip) {
                if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
                    return false;
                }
            }
        }

        $forwardedHost = $request->header('X-Forwarded-Host');
        if ($forwardedHost) {
            $fHost = strtolower(trim(explode(':', $forwardedHost)[0]));
            if (!in_array($fHost, ['localhost', '127.0.0.1', '[::1]'], true)) {
                return false;
            }
        }

        return $isLoopbackIp && $isLoopbackHost;
    }

    /**
     * Jalur pintasan atau akses masuk cepat tanpa mengetik kata sandi (HANYA LOCALHOST & DEBUG MODE).
     * Ditolak mutlak secara fail-closed (HTTP 404) pada lingkungan nonlokal/production atau jika diakses dari luar localhost.
     */
    public function quickLogin(Request $request)
    {
        $isLocalEnv = (app()->isLocal() || app()->runningUnitTests())
            && app()->environment() !== 'production'
            && config('app.env') !== 'production';

        if (!$isLocalEnv || !config('app.debug') || !$this->isLoopbackRequest($request)) {
            abort(404, 'Halaman atau aksi pintasan tidak tersedia.');
        }

        $request->validate([
            'role' => 'required|in:admin,tu,kepala_sekolah,siswa',
            'user_id' => 'nullable|exists:user,id_user',
        ]);

        $role = $request->input('role');

        if (in_array($role, ['admin', 'tu'])) {
            $admin = Admin::where('role', 'tu')->first() ?? Admin::first();
            if (!$admin) {
                return back()->withErrors(['login' => 'Akun Petugas TU tidak ditemukan di database.']);
            }
            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();
            LogAktivitas::log('LOGIN', 'Pintasan Masuk Cepat: Petugas TU (' . $admin->nama_admin . ')', 'admin', $admin->id_admin, null, null, 'SUCCESS', null, $admin->id_admin, 'tu');
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Selamat bertugas di Panel TU, ' . $admin->nama_admin . ' (Akses Cepat).');
        }

        if ($role === 'kepala_sekolah') {
            $kepsek = Admin::where('role', 'kepala_sekolah')->first();
            if (!$kepsek) {
                return back()->withErrors(['login' => 'Akun Kepala Sekolah tidak ditemukan di database.']);
            }
            Auth::guard('admin')->login($kepsek);
            $request->session()->regenerate();
            LogAktivitas::log('LOGIN', 'Pintasan Masuk Cepat: Kepala Sekolah (' . $kepsek->nama_admin . ')', 'admin', $kepsek->id_admin, null, null, 'SUCCESS', null, $kepsek->id_admin, 'kepala_sekolah');
            return redirect()->intended(route('kepsek.dashboard'))->with('success', 'Selamat datang di Panel Eksekutif, Bapak/Ibu Kepala Sekolah (Akses Cepat).');
        }

        if ($role === 'siswa') {
            $userId = $request->input('user_id');
            $user = $userId ? User::find($userId) : User::first();
            if (!$user) {
                return back()->withErrors(['login' => 'Akun Calon Siswa tidak ditemukan di database.']);
            }
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
            LogAktivitas::log('LOGIN', 'Pintasan Masuk Cepat: Calon Siswa (' . $user->nama . ')', 'user', $user->id_user, null, null, 'SUCCESS', $user->id_user, null, 'calon_siswa');
            return redirect()->intended(route('siswa.dashboard'))->with('success', 'Selamat datang kembali, ' . $user->nama . ' (Akses Cepat)!');
        }

        return back()->withErrors(['login' => 'Peran akun tidak valid.']);
    }

    public function adminLogin(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $throttleKey = $this->throttleKey($request, 'admin');

        // Batasi maksimal 5 percobaan gagal dengan jeda 60 detik
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'login' => "Terlalu banyak percobaan login administrator. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('login');
        }

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::guard('admin')->attempt([$field => $request->login, 'password' => $request->password], $request->filled('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            $admin = Auth::guard('admin')->user();
            LogAktivitas::log('LOGIN', 'Admin login ke panel: ' . $admin->nama_admin, 'admin', $admin->id_admin, null, null, 'SUCCESS', null, $admin->id_admin, $admin->role);

            if ($admin->role === 'kepala_sekolah') {
                return redirect()->intended(route('kepsek.dashboard'))->with('success', 'Selamat datang di Panel Eksekutif, Bapak/Ibu Kepala Sekolah.');
            }
            return redirect()->intended(route('admin.dashboard'))->with('success', 'Selamat bertugas di Panel TU, ' . $admin->nama_admin . '.');
        }

        // Catat percobaan gagal ke RateLimiter
        RateLimiter::hit($throttleKey, 60);

        // Catat login gagal jika akun admin ditemukan
        $failedAdmin = Admin::where($field, $request->login)->first();
        if ($failedAdmin) {
            LogAktivitas::log(
                'LOGIN_GAGAL',
                'Percobaan login administrator gagal: ' . $failedAdmin->nama_admin,
                'admin',
                $failedAdmin->id_admin,
                null,
                null,
                'FAILED',
                null,
                $failedAdmin->id_admin,
                $failedAdmin->role
            );
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
        $admin = Auth::guard('admin')->user();
        if ($admin) {
            LogAktivitas::log('LOGOUT', 'Admin keluar dari sistem: ' . $admin->nama_admin, 'admin', $admin->id_admin, null, null, 'SUCCESS', null, $admin->id_admin, $admin->role);
        }
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

        if ($user) {
            $simulatedOtp = rand(100000, 999999);
            \Illuminate\Support\Facades\Log::info("Simulasi reset kata sandi akun {$user->username} (NISN: {$user->nisn}). OTP: {$simulatedOtp}");
        }

        return back()->with('success', 'Jika NISN atau Email terdaftar di sistem PPDB, tautan atau instruksi pemulihan kata sandi telah dikirimkan ke kontak terdaftar.');
    }
}

