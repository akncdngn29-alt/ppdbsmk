# UI VERIFICATION REPORT — PPDB SMK Islam Bina Khalifah Bangsa

**Tanggal Audit:** 4 November 2026, 23:13–23:27 WIB  
**Mode:** READ-ONLY — Tidak ada perubahan kode, database, konfigurasi, maupun file proyek.  
**Auditor:** Antigravity Agentic AI  
**PHP Runtime:** 8.0.30 (XAMPP) — tidak kompatibel dengan requirement `^8.3` di `composer.json`, sehingga `php artisan` tidak dapat dijalankan. Audit dilakukan secara **static analysis** terhadap seluruh source code.

---

## Ringkasan Eksekutif

| # | Area Audit | Status |
|---|-----------|--------|
| 1 | Layout Calon Siswa (`layouts.siswa`) | ✅ VERIFIED |
| 2 | Sidebar Menu ↔ Route | ⚠️ PARTIALLY VERIFIED |
| 3 | Fitur Cetak (Formulir, Kartu Ujian, Kwitansi, SKL) | ⚠️ PARTIALLY VERIFIED |
| 4 | Badge Terkunci & Lunas dari Database | ✅ VERIFIED |
| 5 | Sidebar Mobile, Hamburger, Backdrop | ✅ VERIFIED |
| 6 | Dark Mode Konsistensi | ✅ VERIFIED |
| 7 | Panel Petugas TU & Kepala Sekolah | ⚠️ PARTIALLY VERIFIED |
| 8 | Navbar Publik (Guest & Auth) | ✅ VERIFIED |
| 9 | Potensi Error Route, Blade, JS, CSS, Konflik Layout | ⚠️ PARTIALLY VERIFIED |

---

## 1. Halaman Calon Siswa → `layouts.siswa`

### Metode
Grep `@extends` pada seluruh file `resources/views/siswa/*.blade.php`.

### Hasil

| View File | `@extends` | Baris | Status |
|-----------|-----------|-------|--------|
| `siswa/dashboard.blade.php` | `layouts.siswa` | L1 | ✅ |
| `siswa/formulir.blade.php` | `layouts.siswa` | L1 | ✅ |
| `siswa/berkas.blade.php` | `layouts.siswa` | L1 | ✅ |
| `siswa/pembayaran.blade.php` | `layouts.siswa` | L1 | ✅ |
| `siswa/daftar_ulang.blade.php` | `layouts.siswa` | L1 | ✅ |
| `siswa/pengaturan.blade.php` | `layouts.siswa` | L1 | ✅ |
| `siswa/cetak_formulir.blade.php` | *Standalone HTML* | — | ✅ By Design |
| `siswa/cetak_kartu_ujian.blade.php` | *Standalone HTML* | — | ✅ By Design |
| `siswa/cetak_kwitansi.blade.php` | *Standalone HTML* | — | ✅ By Design |
| `siswa/cetak_skl.blade.php` | *Standalone HTML* | — | ✅ By Design |

> [!NOTE]
> 4 halaman cetak (PDF via `window.print()`) sengaja tidak menggunakan layout apapun agar hasil cetak bersih tanpa sidebar/navbar. Ini **by design** dan benar.

### Status: ✅ VERIFIED

---

## 2. Sidebar Menu ↔ Route & Fitur

### Metode
Cross-reference setiap `<a href="{{ route('...') }}">` di `layouts/siswa.blade.php` (L110–L200) terhadap definisi route di `routes/web.php` (L38–L64) dan method controller di `SiswaDashboardController.php`.

### Hasil Mapping

| Menu Sidebar | Route Name | web.php | Controller Method | Status |
|-------------|-----------|---------|-------------------|--------|
| Dashboard Utama | `siswa.dashboard` | L39 | `index()` L33 | ✅ |
| Formulir Biodata | `siswa.formulir` | L40 | `formulir()` L60 | ✅ |
| Upload Berkas | `siswa.berkas` | L44 | `berkas()` L186 | ✅ |
| Pembayaran PPDB | `siswa.pembayaran` | L48 | `pembayaran()` L307 | ✅ |
| Cetak Formulir (PDF) | `siswa.cetak.formulir` | L54 | `cetakFormulir()` L504 | ✅ |
| Kartu Peserta Ujian | `siswa.cetak.kartu_ujian` | L55 | `cetakKartuUjian()` L515 | ✅ |
| Kwitansi Lunas (PDF) | `siswa.cetak.kwitansi` | L56 | `cetakKwitansi()` L526 | ✅ |
| Surat Kelulusan (SKL) | `siswa.cetak.skl` | L57 | `unduhSKL()` L537 | ✅ |
| Daftar Ulang | `siswa.daftar_ulang` | L60 | `daftarUlang()` L462 | ✅ |
| Pengaturan Akun | `siswa.pengaturan` | L62 | `pengaturanAkun()` L474 | ✅ |
| Keluar Akun | `logout` | L29 | `AuthController::logout()` | ✅ |

### Conditional Visibility di Sidebar

| Kondisi | Baris Layout | Sumber Data | Status |
|---------|-------------|-------------|--------|
| Menu cetak hanya muncul jika `$pendaftar` ada | L156 | `Auth::user()->pendaftar` (L59–L62) | ✅ |
| Kwitansi hanya jika `status_pembayaran === 'Paid'` | L167 | `$pendaftar->status_pembayaran` dari DB | ✅ |
| SKL hanya jika `status_kelulusan === 'Diterima'` | L174 | `$pendaftar->status_kelulusan` dari DB | ✅ |

### Route Highlight (Active State)

Setiap menu utama menggunakan `request()->routeIs('...')` untuk highlight active (L115, L121, L132, L143, L189, L195). ✅ Benar.

### Temuan Bug

> [!WARNING]
> **BUG-01: Link cetak dari Admin Detail menggunakan route `siswa.*` yang dilindungi middleware `auth` (guard web).**
> 
> File: [`admin/pendaftar/detail.blade.php`](file:///d:/BASIS_DATA_LANJUT/PPDB_SMK/resources/views/admin/pendaftar/detail.blade.php#L229-L241)
> 
> Admin TU yang login via guard `admin` mengakses `route('siswa.cetak.formulir')` + `?admin_view=1`. Route ini dilindungi middleware `auth` (guard `web`). Admin yang tidak memiliki session `web` akan di-redirect ke halaman login siswa. Parameter `admin_view` juga **tidak ditangani** di controller (`SiswaDashboardController` — tidak ada referensi `admin_view` sama sekali).
> 
> **Dampak:** Tombol "Lihat Formulir / Kartu Ujian / SKL" di halaman detail pendaftar milik admin **tidak akan berfungsi** — redirect ke login siswa.

### Status: ⚠️ PARTIALLY VERIFIED

---

## 3. Fitur Cetak (Formulir PDF, Kartu Ujian QR, Kwitansi PDF, SKL PDF)

### 3.1 Cetak Formulir Pendaftaran

| Aspek | Detail | Bukti | Status |
|-------|--------|-------|--------|
| Route | `siswa.cetak.formulir` → `GET /siswa/cetak/formulir` | `web.php` L54 | ✅ |
| Guard | `$pendaftar` harus ada, redirect jika null | Controller L506–L508 | ✅ |
| View | `siswa.cetak_formulir` — standalone HTML | Blade L1–L160 | ✅ |
| Mekanisme cetak | `window.print()` → "Save as PDF" browser | Blade L23 | ✅ |
| Data yang ditampilkan | NISN, nama, TTL, alamat, jurusan, ortu, dll | Blade L48–L127 | ✅ |
| Print CSS | `@page { size: A4 portrait; margin: 15mm; }`, `.no-print` | Blade L9–L13 | ✅ |

### 3.2 Kartu Peserta Ujian (QR Code)

| Aspek | Detail | Bukti | Status |
|-------|--------|-------|--------|
| Route | `siswa.cetak.kartu_ujian` → `GET /siswa/cetak/kartu-ujian` | `web.php` L55 | ✅ |
| Guard | `status_pembayaran` harus `Paid` | Controller L518 | ✅ |
| View | `siswa.cetak_kartu_ujian` — standalone HTML | Blade 131 baris | ✅ |
| Pas Foto | Mengambil dari `$pendaftar->berkas` jenis `pasfoto` | Blade L84–L91 | ✅ |

> [!CAUTION]
> **BUG-02: QR Code bukan QR Code asli — hanya simulasi teks.**
> 
> File: [`siswa/cetak_kartu_ujian.blade.php`](file:///d:/BASIS_DATA_LANJUT/PPDB_SMK/resources/views/siswa/cetak_kartu_ujian.blade.php#L93-L98)
> 
> Baris L93–L98 menampilkan kotak hitam berisi teks "QR VALID" + 7 digit terakhir no pendaftaran. **Ini bukan QR code yang scannable.** Tidak ada library QR code di `composer.json` (tidak ada `simplesoftwareio/simple-qrcode`, `endroid/qr-code`, atau `chillerlan/php-qrcode`). Tidak ada JavaScript QR library yang dimuat.
> 
> **Dampak:** Jika pengguna mencetak kartu ini, kotak QR tidak dapat dipindai oleh scanner QR manapun. Label "QR VALID" bersifat menyesatkan.

> [!CAUTION]
> **BUG-03: QR Code di SKL juga simulasi, bukan QR asli.**
> 
> File: [`siswa/cetak_skl.blade.php`](file:///d:/BASIS_DATA_LANJUT/PPDB_SMK/resources/views/siswa/cetak_skl.blade.php) — baris bawah (area tanda tangan)
> 
> Teks bertuliskan "QR DOKUMEN SAH" + 8 digit terakhir. Terdapat keterangan "Pindai QR Code untuk memverifikasi keaslian surat keputusan kelulusan ini secara online melalui portal sekolah." **Namun fitur verifikasi online tersebut tidak ada** (tidak ada route untuk verifikasi QR di `web.php`).

### 3.3 Kwitansi Lunas (PDF)

| Aspek | Detail | Bukti | Status |
|-------|--------|-------|--------|
| Route | `siswa.cetak.kwitansi` → `GET /siswa/cetak/kwitansi` | `web.php` L56 | ✅ |
| Guard | `$pendaftar->pembayaran->status_transaksi === 'settlement'` | Controller L529 | ✅ |
| Data keuangan | `total_bayar`, `kode_transaksi`, `metode_pembayaran` | Blade L41–L79 | ✅ |
| Watermark LUNAS | CSS rotated stamp overlay | Blade L29–L32 | ✅ |

### 3.4 Surat Kelulusan / SKL (PDF)

| Aspek | Detail | Bukti | Status |
|-------|--------|-------|--------|
| Route | `siswa.cetak.skl` → `GET /siswa/cetak/skl` | `web.php` L57 | ✅ |
| Guard | `$pendaftar->terima` harus ada (bukan null) | Controller L540 | ✅ |
| Sidebar guard | `status_kelulusan === 'Diterima'` | Layout L174 | ✅ |
| No surat kelulusan | `$pendaftar->terima->no_surat_kelulusan` | Blade SKL | ✅ |
| Tanda tangan Kepsek | `$pengaturan->nama_kepsek`, `$pengaturan->nip_kepsek` | Blade SKL | ✅ |

> [!NOTE]
> **Perbedaan guard**: Sidebar mengecek `status_kelulusan === 'Diterima'` (string field), controller mengecek `$pendaftar->terima` (relasi). Keduanya pada dasarnya selaras: record `Terima` hanya dibuat ketika Kepala Sekolah mengesahkan status "Diterima" (lihat `KepalaSekolahController@sahkanKelulusan` L84–L112).

### Status: ⚠️ PARTIALLY VERIFIED

**Alasan:** Fungsionalitas cetak via browser (`window.print()`) berfungsi secara mekanis. Namun QR Code pada Kartu Ujian dan SKL **bukan QR asli** (BUG-02, BUG-03), dan link cetak dari panel admin **tidak akan berfungsi** (BUG-01).

---

## 4. Badge "Terkunci" dan "Lunas" dari Database

### Metode
Trace sumber data badge di layout dan view.

### 4.1 Badge "Terkunci" (Formulir)

| Lokasi | File | Baris | Ekspresi | Sumber |
|--------|------|-------|----------|--------|
| Sidebar | `layouts/siswa.blade.php` | L126–L128 | `$pendaftar->is_locked` | DB kolom `is_locked`, cast `boolean` (Model L63) |
| Dashboard stepper | `siswa/dashboard.blade.php` | L58 | `$pendaftar->is_locked ? 'Terkunci' : 'Draft'` | Sama |
| Dashboard card badge | `siswa/dashboard.blade.php` | L102–L106 | `$pendaftar->is_locked` | Sama |

**Sumber asli:** `$pendaftar` di-load dari DB via `getUserPendaftar()` (Controller L25–L31) dan di layout via `Auth::user()->pendaftar` (Layout L61). Keduanya membaca dari tabel `pendaftar`.

### 4.2 Badge "Lunas" (Pembayaran)

| Lokasi | File | Baris | Ekspresi | Sumber |
|--------|------|-------|----------|--------|
| Sidebar | `layouts/siswa.blade.php` | L148–L150 | `$pendaftar->status_pembayaran === 'Paid'` | DB kolom `status_pembayaran` |
| Dashboard stepper | `siswa/dashboard.blade.php` | L59 | `$pendaftar->status_pembayaran === 'Paid' ? 'Lunas' : 'Menunggu'` | Sama |
| Dashboard card | `siswa/dashboard.blade.php` | L142–L144 | `$pendaftar->status_pembayaran === 'Paid'` | Sama |
| Halaman pembayaran | `siswa/pembayaran.blade.php` | L18–L21 | `$pendaftar->status_pembayaran === 'Paid'` | Sama |

**Semua badge mengambil status aktual dari kolom database**, bukan hardcoded.

### Status: ✅ VERIFIED

---

## 5. Sidebar Mobile, Hamburger Button, dan Backdrop

### Metode
Analisis HTML/CSS/JS di `layouts/siswa.blade.php` dan `layouts/admin.blade.php`.

### Layout Siswa (`layouts/siswa.blade.php`)

| Komponen | Bukti | Baris | Status |
|----------|-------|-------|--------|
| Backdrop overlay | `id="sidebar-backdrop"`, `class="fixed inset-0 ... hidden md:hidden"`, `onclick="toggleSidebar()"` | L66 | ✅ |
| Sidebar offcanvas | `id="sidebar"`, `-translate-x-full md:translate-x-0 md:static` | L69 | ✅ |
| Hamburger button | `onclick="toggleSidebar()"`, `data-lucide="menu"` | L223–L225 | ✅ |
| Close button (X) | `onclick="toggleSidebar()"`, `md:hidden`, `data-lucide="x"` | L81–L83 | ✅ |
| JS toggle function | `toggleSidebar()` — toggles `-translate-x-full` dan backdrop `hidden` | L325–L335 | ✅ |

### Layout Admin (`layouts/admin.blade.php`)

| Komponen | Bukti | Baris | Status |
|----------|-------|-------|--------|
| Backdrop overlay | `id="admin-sidebar-backdrop"`, `onclick="toggleAdminSidebar()"` | L65 | ✅ |
| Sidebar offcanvas | `-translate-x-full md:translate-x-0 md:static` | L68 | ✅ |
| Hamburger button | `onclick="toggleAdminSidebar()"`, `data-lucide="menu"` | L167–L169 | ✅ |
| Close button (X) | `onclick="toggleAdminSidebar()"`, `md:hidden` | L80–L82 | ✅ |
| JS toggle function | `toggleAdminSidebar()` — identik logikanya | L217–L227 | ✅ |

### Layout App / Publik (`layouts/app.blade.php`)

| Komponen | Bukti | Baris | Status |
|----------|-------|-------|--------|
| Mobile menu button | `id="mobile-menu-btn"`, `data-lucide="menu"` | L154–L156 | ✅ |
| Mobile drawer | `id="mobile-menu"`, `class="hidden md:hidden"` | L162 | ✅ |
| JS toggle | `mobileMenu.classList.toggle('hidden')` | L338–L341 | ✅ |

> [!NOTE]
> Layout publik menggunakan **drawer dropdown** (toggle `hidden`), bukan offcanvas slide. Ini berbeda dari layout siswa/admin tapi tetap fungsional.

### Status: ✅ VERIFIED

---

## 6. Dark Mode Konsistensi

### Metode
Verifikasi bahwa ketiga layout menggunakan mekanisme dark mode yang sama (`darkMode: 'class'`, `localStorage`, toggle button).

### Perbandingan Implementasi

| Aspek | `layouts/siswa.blade.php` | `layouts/admin.blade.php` | `layouts/app.blade.php` |
|-------|--------------------------|--------------------------|------------------------|
| Tailwind `darkMode` | `'class'` (L16) | `'class'` (L15) | `'class'` (L15) |
| Early init script | L38–L44 | L37–L43 | L44–L51 |
| Toggle button ID | `theme-toggle` (L246) | `theme-toggle` (L177) | `theme-toggle` (L112) |
| Icon IDs | `theme-toggle-dark-icon`, `theme-toggle-light-icon` (L247–L248) | Sama (L178–L179) | Sama (L113–L114) |
| `localStorage` key | `'theme'` (L357, L359) | `'theme'` (L249, L251) | `'theme'` (L327, L329) |
| `updateIcons()` logic | L342–L350 | L234–L242 | L313–L321 |
| `<body>` dark classes | `dark:bg-slate-950 dark:text-slate-100` | `dark:bg-slate-950 dark:text-slate-100` | `dark:bg-slate-900 dark:text-slate-100` |

> [!TIP]
> Minor: Body background di layout publik menggunakan `dark:bg-slate-900` sementara siswa/admin menggunakan `dark:bg-slate-950`. Ini **bukan bug** — perbedaan shade yang disengaja untuk membedakan konteks visual. Warna foreground konsisten (`dark:text-slate-100`).

### Halaman Cetak (Standalone)

Keempat halaman cetak (`cetak_formulir`, `cetak_kartu_ujian`, `cetak_kwitansi`, `cetak_skl`) **tidak memiliki dark mode** — ini benar karena dokumen cetak harus selalu menggunakan background putih.

### Status: ✅ VERIFIED

---

## 7. Panel Petugas TU dan Kepala Sekolah

### 7.1 Pembatasan Hak Akses (Middleware)

| Route Group | Middleware | Guard | Bukti | Status |
|------------|-----------|-------|-------|--------|
| `/admin/*` (TU) | `admin.role:tu` | `admin` | `web.php` L67 | ✅ |
| `/kepsek/*` (Kepsek) | `admin.role:kepala_sekolah` | `admin` | `web.php` L99 | ✅ |

**Middleware `AdminRoleMiddleware`** ([`app/Http/Middleware/AdminRoleMiddleware.php`](file:///d:/BASIS_DATA_LANJUT/PPDB_SMK/app/Http/Middleware/AdminRoleMiddleware.php)):
- L13: Cek `auth('admin')->check()` → redirect ke login jika gagal ✅
- L18: Cek `$admin->role` in `$roles` → abort 403 jika tidak cocok ✅

### 7.2 Sidebar Admin — Pemisahan Menu

File: [`layouts/admin.blade.php`](file:///d:/BASIS_DATA_LANJUT/PPDB_SMK/resources/views/layouts/admin.blade.php#L87-L144)

| Kondisi | Baris | Menu Ditampilkan | Status |
|---------|-------|-----------------|--------|
| `@if($isTU)` | L87 | Dashboard TU, Pendaftar, Seleksi, Master Jurusan/Gelombang/Voucher, Ekspor, Pengumuman, Pengaturan | ✅ |
| `@else` (Kepsek) | L129 | Statistik Eksekutif, Kelulusan, Laporan, Audit Trail | ✅ |

### 7.3 View → Layout Mapping

| View | `@extends` | Status |
|------|-----------|--------|
| `admin/dashboard.blade.php` | `layouts.admin` | ✅ |
| `admin/pendaftar/index.blade.php` | `layouts.admin` | ✅ |
| `admin/pendaftar/detail.blade.php` | `layouts.admin` | ✅ |
| `admin/seleksi/index.blade.php` | `layouts.admin` | ✅ |
| `admin/master/jurusan.blade.php` | `layouts.admin` | ✅ |
| `admin/master/gelombang.blade.php` | `layouts.admin` | ✅ |
| `admin/master/voucher.blade.php` | `layouts.admin` | ✅ |
| `admin/pengumuman/index.blade.php` | `layouts.admin` | ✅ |
| `admin/pengaturan/index.blade.php` | `layouts.admin` | ✅ |
| `admin/laporan/rekap_pdf.blade.php` | *Standalone* | ✅ By Design |
| `kepsek/dashboard.blade.php` | `layouts.admin` | ✅ |
| `kepsek/kelulusan.blade.php` | `layouts.admin` | ✅ |
| `kepsek/laporan.blade.php` | `layouts.admin` | ✅ |
| `kepsek/audit_log.blade.php` | `layouts.admin` | ✅ |

### 7.4 Kelulusan — Form Action via JavaScript

File: [`kepsek/kelulusan.blade.php`](file:///d:/BASIS_DATA_LANJUT/PPDB_SMK/resources/views/kepsek/kelulusan.blade.php#L222-L224)

Modal form action diset via JS: `form.action = '/kepsek/kelulusan/${id}'` (L224).  
Route target: `Route::post('/kelulusan/{id}', [KepalaSekolahController::class, 'sahkanKelulusan'])` (`web.php` L102).  
**Match.** ✅

> [!WARNING]
> **BUG-04: Hardcoded URL path di JavaScript.**
> 
> File: [`kepsek/kelulusan.blade.php`](file:///d:/BASIS_DATA_LANJUT/PPDB_SMK/resources/views/kepsek/kelulusan.blade.php#L224) L224
> 
> `form.action = '/kepsek/kelulusan/${id}'` — ini hardcoded, bukan menggunakan `{{ route() }}`. Jika app di-deploy di subdirectory (mis. `example.com/ppdb/`), URL ini akan salah. **Risiko rendah** jika deploy di root domain, tapi merupakan bad practice.

### Status: ⚠️ PARTIALLY VERIFIED

---

## 8. Navbar Publik (Guest & Logged-in User)

File: [`layouts/app.blade.php`](file:///d:/BASIS_DATA_LANJUT/PPDB_SMK/resources/views/layouts/app.blade.php#L82-L200)

### Desktop Navigation (L98–L107)

| Link | Target | Status |
|------|--------|--------|
| Beranda | `route('landing')` | ✅ |
| Program Keahlian | `route('landing') . '#jurusan'` | ✅ |
| Ekstrakurikuler | `route('landing') . '#ekskul'` | ✅ |
| Alur PPDB | `route('landing') . '#alur'` | ✅ |
| Cek Kelulusan | `route('landing') . '#cek-kelulusan'` | ✅ |

### Authenticated State (L117–L140)

| Komponen | Detail | Status |
|----------|--------|--------|
| User dropdown trigger | Link ke `siswa.dashboard`, tampilkan `Auth::user()->nama` | ✅ |
| Dropdown: Dashboard Siswa | `route('siswa.dashboard')` | ✅ |
| Dropdown: Formulir | `route('siswa.formulir')` | ✅ |
| Dropdown: Logout | Form POST ke `route('logout')` dengan `@csrf` | ✅ |

### Guest State (L141–L150)

| Komponen | Detail | Status |
|----------|--------|--------|
| Tombol Masuk | `route('login')` | ✅ |
| Tombol Daftar Sekarang | `route('register')` | ✅ |

### Mobile Drawer (L162–L199)

| State | Komponen | Status |
|-------|----------|--------|
| `@auth` | Info siswa + Panel Siswa button + nav links + Keluar | ✅ |
| `@else` (guest) | Nav links + Masuk/Daftar buttons | ✅ |

### Footer — Portal Admin

Link ke `route('admin.login')` di footer (L288). ✅

### Status: ✅ VERIFIED

---

## 9. Potensi Error Route, Blade, JavaScript, CSS, dan Konflik Layout

### 9.1 Route Orphans (Route di Views Tapi Tidak di web.php)

Semua 60+ named routes yang digunakan di Blade templates telah diverifikasi ada di `routes/web.php`. **Tidak ada orphan route.**

### 9.2 View Orphans (View Dikembalikan Controller Tapi File Tidak Ada)

Semua 16 view yang dikembalikan oleh controller telah diverifikasi memiliki file `.blade.php` yang sesuai. **Tidak ada missing view.**

### 9.3 Potensi Blade Error

| Issue | File | Baris | Deskripsi | Severity |
|-------|------|-------|-----------|----------|
| `$pendaftar->nisn` di layout | `layouts/siswa.blade.php` | L94 | `$user->pendaftar` bisa null jika relasi belum di-load. Namun `$pendaftar` sudah dicek `$user ? $user->pendaftar : null` di L61 dan ditampilkan via null-safe di L94 (`$user->nisn ?? '-'`). | ✅ Aman |
| `$pendaftar->gelombang` null | `siswa/cetak_formulir.blade.php` | L41 | Menggunakan ternary `$pendaftar->gelombang ? ... : '2026/2027'`. | ✅ Aman |
| `$pendaftar->terima->jurusan` | `siswa/dashboard.blade.php` | L231 | `$pendaftar->terima ? $pendaftar->terima->jurusan->nama_jurusan : ...` — aman karena dicek dulu. | ✅ Aman |

### 9.4 JavaScript Issues

| Issue | File | Baris | Deskripsi | Severity |
|-------|------|-------|-----------|----------|
| Hardcoded URL | `kepsek/kelulusan.blade.php` | L224 | `form.action = '/kepsek/kelulusan/${id}'` — tidak menggunakan route helper | ⚠️ Low |
| `lucide.createIcons()` | Semua layout | — | Dipanggil di akhir `<body>`. Bergantung pada CDN `unpkg.com/lucide@latest` yang bisa berubah kapan saja | ⚠️ Low |

### 9.5 CSS/Tailwind Issues

| Issue | File | Deskripsi | Severity |
|-------|------|-----------|----------|
| CDN Tailwind (`cdn.tailwindcss.com`) | Semua layout & cetak | Menggunakan Tailwind Play CDN, bukan build. Tidak direkomendasikan untuk produksi (performa, ketergantungan CDN) | ⚠️ Medium |
| CDN `unpkg.com/lucide@latest` | Semua layout | `@latest` tag bisa breaking jika Lucide merilis major version baru | ⚠️ Medium |

### 9.6 Layout Conflicts

**Tidak ditemukan konflik layout.** Setiap context menggunakan layout yang tepat:
- Halaman publik + auth → `layouts.app`
- Panel siswa → `layouts.siswa`
- Panel admin/kepsek → `layouts.admin`
- Halaman cetak → Standalone HTML
- Maintenance → Standalone HTML

### 9.7 Admin Cetak Cross-Guard Issue (BUG-01 Detail)

File: [`admin/pendaftar/detail.blade.php`](file:///d:/BASIS_DATA_LANJUT/PPDB_SMK/resources/views/admin/pendaftar/detail.blade.php#L229-L241)

Route `siswa.cetak.formulir`, `siswa.cetak.kartu_ujian`, dan `siswa.cetak.skl` berada di dalam group middleware `auth` (guard `web`). Admin TU login via guard `admin`, bukan guard `web`. Ketika admin mengklik link tersebut, mereka **tidak terautentikasi** di guard `web`, sehingga akan di-redirect ke halaman login siswa.

Selain itu, controller method (`cetakFormulir`, `cetakKartuUjian`, `unduhSKL`) menggunakan `Auth::user()` untuk menentukan data pendaftar mana yang ditampilkan via `getUserPendaftar()` — sehingga **bahkan jika admin bisa akses**, data yang ditampilkan akan salah (milik admin, bukan siswa yang sedang dilihat).

### Status: ⚠️ PARTIALLY VERIFIED

---

## Daftar Lengkap Temuan Bug

| ID | Severity | Deskripsi | File | Baris |
|----|----------|-----------|------|-------|
| **BUG-01** | 🔴 HIGH | Link cetak di panel admin (`siswa.cetak.*`) menggunakan guard `web` — admin tidak bisa mengaksesnya. Parameter `admin_view` tidak ditangani di controller. | `admin/pendaftar/detail.blade.php` | L229–L241 |
| **BUG-02** | 🟡 MEDIUM | QR Code di Kartu Peserta Ujian hanya simulasi teks (`"QR VALID"` + 7 digit), bukan QR code asli yang scannable. Tidak ada library QR terpasang. | `siswa/cetak_kartu_ujian.blade.php` | L93–L98 |
| **BUG-03** | 🟡 MEDIUM | QR Code di SKL juga simulasi teks (`"QR DOKUMEN SAH"`), dengan keterangan "Pindai QR Code untuk memverifikasi" yang menyesatkan. Route verifikasi online tidak ada. | `siswa/cetak_skl.blade.php` | Bagian signature |
| **BUG-04** | 🟢 LOW | Hardcoded URL path `/kepsek/kelulusan/${id}` di JavaScript form action. Tidak kompatibel jika deploy di subdirectory. | `kepsek/kelulusan.blade.php` | L224 |

---

## Catatan Tambahan

### PHP Version Mismatch

- `composer.json` require: `"php": "^8.3"`
- PHP terinstal di sistem: `8.0.30` (XAMPP)
- **Konsekuensi:** `php artisan` tidak dapat dijalankan, sehingga **runtime testing tidak memungkinkan**. Semua verifikasi dilakukan via static code analysis.

### Ketergantungan CDN Eksternal

Seluruh aset frontend (Tailwind CSS, Lucide Icons, Chart.js, Google Fonts) dimuat dari CDN publik. Jika CDN down, **seluruh antarmuka tidak akan ditampilkan dengan benar**.

---

## Kesimpulan Akhir

| Area | Status | Catatan |
|------|--------|---------|
| Layout Siswa | ✅ VERIFIED | 6/6 halaman panel + 4 cetak standalone |
| Sidebar → Route | ⚠️ PARTIALLY VERIFIED | 11/11 menu OK, tapi admin cross-guard (BUG-01) |
| Fitur Cetak | ⚠️ PARTIALLY VERIFIED | Mekanisme `window.print()` OK, QR palsu (BUG-02, BUG-03) |
| Badge DB | ✅ VERIFIED | `is_locked` dan `status_pembayaran` dari DB |
| Mobile Sidebar | ✅ VERIFIED | 3 layout, semua berfungsi |
| Dark Mode | ✅ VERIFIED | Konsisten di 3 layout |
| Panel Admin/Kepsek | ⚠️ PARTIALLY VERIFIED | Access control OK, hardcoded URL (BUG-04) |
| Navbar Publik | ✅ VERIFIED | Guest & auth state benar |
| Error Potensial | ⚠️ PARTIALLY VERIFIED | CDN dependency, cross-guard issue |

**Tidak ada perubahan kode, database, konfigurasi, maupun file proyek yang dilakukan selama audit ini.**

