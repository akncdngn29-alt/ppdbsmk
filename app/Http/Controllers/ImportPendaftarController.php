<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\LogAktivitas;
use Carbon\Carbon;
use ZipArchive;

class ImportPendaftarController extends Controller
{
    /**
     * Tampilkan halaman formulir import pendaftar Excel/CSV.
     */
    public function index()
    {
        $gelombangAktif = Gelombang::where('status_aktif', true)->first() ?: Gelombang::first();
        $daftarJurusan = Jurusan::all();
        $totalPendaftar = Pendaftar::count();

        return view('admin.pendaftar.import', compact('gelombangAktif', 'daftarJurusan', 'totalPendaftar'));
    }

    /**
     * Unduh berkas template import pendaftar resmi (format CSV).
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_pendaftar_ppdb.csv"',
        ];

        $columns = [
            'nama',
            'nisn',
            'email',
            'hp_siswa',
            'tempat_lahir',
            'tanggal_lahir',
            'jenis_kelamin',
            'agama',
            'asal_sekolah',
            'kode_jurusan_1',
            'nama_ayah',
            'nama_ibu',
            'hp_ortu',
        ];

        $sampleRow = [
            'Rizky Pratama',
            '0081234567',
            'rizky@example.com',
            '081234567890',
            'Kota Metro',
            '2010-05-20',
            'Laki-laki',
            'Islam',
            'SMP Negeri 2 Metro',
            'TKJ',
            'Bambang',
            'Siti Aminah',
            '081298765432',
        ];

        $callback = function () use ($columns, $sampleRow) {
            $handle = fopen('php://output', 'w');
            // Tulis BOM UTF-8 agar karakter terbaca rapi di Microsoft Excel
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $columns);
            fputcsv($handle, $sampleRow);
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Eksekusi parsing dan import berkas Excel/CSV ke basis data.
     */
    public function import(Request $request)
    {
        $request->validate([
            'berkas' => 'required|file|mimes:csv,txt,xlsx|max:5120', // Maksimal 5MB
            'id_gelombang' => 'required|exists:gelombang,id_gelombang',
        ], [
            'berkas.required' => 'Silakan pilih berkas CSV atau Excel yang akan diunggah.',
            'berkas.mimes' => 'Format berkas harus berupa .csv atau .xlsx.',
            'berkas.max' => 'Ukuran berkas tidak boleh melebihi 5 MB.',
            'id_gelombang.required' => 'Pilih gelombang pendaftaran tujuan.',
        ]);

        $file = $request->file('berkas');
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $file->getClientOriginalName();

        if (!in_array($extension, ['csv', 'txt', 'xlsx'])) {
            return back()->with('error', 'Format berkas tidak didukung. Harap unggah berkas berekstensi .csv atau .xlsx.');
        }

        $rows = [];
        if ($extension === 'xlsx') {
            $rows = $this->parseXlsx($file->getPathname());
        } else {
            $rows = $this->parseCsv($file->getPathname());
        }

        if (empty($rows)) {
            return back()->with('error', 'Berkas yang diunggah kosong atau format data tidak dapat dibaca.');
        }

        // Ambil header baris pertama
        $header = array_map(function ($col) {
            return strtolower(trim((string) $col));
        }, array_shift($rows));

        // Mapping index kolom yang diharapkan
        $expectedCols = [
            'nama' => array_search('nama', $header),
            'nisn' => array_search('nisn', $header),
            'email' => array_search('email', $header),
            'hp_siswa' => array_search('hp_siswa', $header),
            'tempat_lahir' => array_search('tempat_lahir', $header),
            'tanggal_lahir' => array_search('tanggal_lahir', $header),
            'jenis_kelamin' => array_search('jenis_kelamin', $header),
            'agama' => array_search('agama', $header),
            'asal_sekolah' => array_search('asal_sekolah', $header),
            'kode_jurusan_1' => array_search('kode_jurusan_1', $header),
            'nama_ayah' => array_search('nama_ayah', $header),
            'nama_ibu' => array_search('nama_ibu', $header),
            'hp_ortu' => array_search('hp_ortu', $header),
        ];

        if ($expectedCols['nama'] === false || $expectedCols['nisn'] === false || $expectedCols['kode_jurusan_1'] === false) {
            return back()->with('error', 'Struktur kolom berkas tidak sesuai. Kolom wajib: "nama", "nisn", dan "kode_jurusan_1" harus tersedia.');
        }

        $gelombang = Gelombang::findOrFail($request->id_gelombang);
        $allJurusan = Jurusan::all()->keyBy(function ($item) {
            return strtoupper(trim($item->kode_jurusan));
        });

        $totalDiproses = 0;
        $totalBerhasil = 0;
        $totalDilewati = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                $barisKe = $index + 2; // Baris 1 adalah header di spreadsheet

                // Abaikan baris kosong
                if (empty(array_filter($row, fn($v) => !is_null($v) && trim((string)$v) !== ''))) {
                    continue;
                }

                $totalDiproses++;

                $nama = trim((string) ($row[$expectedCols['nama']] ?? ''));
                $nisn = preg_replace('/[^0-9]/', '', (string) ($row[$expectedCols['nisn']] ?? ''));
                $kodeJurusan = strtoupper(trim((string) ($row[$expectedCols['kode_jurusan_1']] ?? '')));

                // Validasi data minimum
                if (empty($nama)) {
                    $errors[] = "Baris #{$barisKe}: Nama calon siswa kosong.";
                    $totalDilewati++;
                    continue;
                }

                if (empty($nisn) || strlen($nisn) < 10) {
                    $errors[] = "Baris #{$barisKe}: NISN '{$nisn}' tidak valid (wajib 10 digit angka).";
                    $totalDilewati++;
                    continue;
                }

                // Cek duplikasi NISN di tabel user & pendaftar
                if (User::where('nisn', $nisn)->exists() || Pendaftar::where('nisn', $nisn)->exists()) {
                    $errors[] = "Baris #{$barisKe}: NISN {$nisn} ({$nama}) sudah terdaftar di sistem. Dilewati.";
                    $totalDilewati++;
                    continue;
                }

                // Validasi kode jurusan
                if (!$allJurusan->has($kodeJurusan)) {
                    $errors[] = "Baris #{$barisKe}: Kode jurusan '{$kodeJurusan}' tidak ditemukan. Opsi tersedia: " . implode(', ', $allJurusan->keys()->toArray());
                    $totalDilewati++;
                    continue;
                }

                $jurusan = $allJurusan->get($kodeJurusan);

                // Siapkan email unik
                $email = strtolower(trim((string) ($row[$expectedCols['email']] ?? '')));
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || User::where('email', $email)->exists()) {
                    $email = "siswa_{$nisn}@smkib.sch.id";
                }

                // Siapkan username unik
                $baseUsername = Str::slug(explode(' ', $nama)[0], '');
                if (empty($baseUsername) || strlen($baseUsername) < 3) {
                    $baseUsername = 'siswa';
                }
                $username = $baseUsername . substr($nisn, -4);
                if (User::where('username', $username)->exists()) {
                    $username = 'siswa' . $nisn;
                }

                // Format jenis kelamin
                $jkRaw = strtolower(trim((string) ($row[$expectedCols['jenis_kelamin']] ?? '')));
                $jenisKelamin = 'Laki-laki';
                if (in_array($jkRaw, ['p', 'perempuan', 'female'])) {
                    $jenisKelamin = 'Perempuan';
                }

                // 1. Buat User Calon Siswa
                $user = User::create([
                    'nama' => $nama,
                    'nisn' => $nisn,
                    'email' => $email,
                    'username' => $username,
                    'password' => Hash::make('siswa123'), // Default password siswa
                ]);

                // 2. Buat Pendaftar
                $noPendaftaran = Pendaftar::generateNoPendaftaran($jurusan->kode_jurusan, date('Y'));

                Pendaftar::create([
                    'id_user' => $user->id_user,
                    'id_gelombang' => $gelombang->id_gelombang,
                    'id_jurusan_1' => $jurusan->id_jurusan,
                    'nisn' => $nisn,
                    'nama' => $nama,
                    'tempat_lahir' => trim((string) ($row[$expectedCols['tempat_lahir']] ?? 'Kota Metro')),
                    'tanggal_lahir' => !empty($row[$expectedCols['tanggal_lahir']]) ? date('Y-m-d', strtotime((string)$row[$expectedCols['tanggal_lahir']])) : '2010-01-01',
                    'jenis_kelamin' => $jenisKelamin,
                    'agama' => trim((string) ($row[$expectedCols['agama']] ?? 'Islam')),
                    'hp_siswa' => trim((string) ($row[$expectedCols['hp_siswa']] ?? '')),
                    'asal_sekolah' => trim((string) ($row[$expectedCols['asal_sekolah']] ?? 'SMP Asal')),
                    'nama_ayah' => trim((string) ($row[$expectedCols['nama_ayah']] ?? '')),
                    'nama_ibu' => trim((string) ($row[$expectedCols['nama_ibu']] ?? '')),
                    'hp_ortu' => trim((string) ($row[$expectedCols['hp_ortu']] ?? '')),
                    'no_pendaftaran' => $noPendaftaran,
                    'status_pembayaran' => 'Pending',
                    'status_verifikasi' => 'Menunggu',
                    'status_kelulusan' => 'Diproses',
                    'status_daftar_ulang' => 'Belum',
                    'is_locked' => false,
                    'tgl_daftar' => Carbon::now(),
                ]);

                $totalBerhasil++;
            }

            DB::commit();

            // Catat ke Audit Trail
            $admin = auth('admin')->user();
            LogAktivitas::log(
                'IMPORT_PENDAFTAR',
                "{$admin->nama_admin} mengimpor data pendaftar dari berkas '{$filename}': {$totalBerhasil} berhasil, {$totalDilewati} dilewati.",
                'pendaftar',
                null,
                null,
                ['file' => $filename, 'berhasil' => $totalBerhasil, 'dilewati' => $totalDilewati],
                'SUCCESS',
                null,
                $admin->id_admin,
                $admin->role
            );

            return back()->with([
                'success' => "Proses import selesai. Berhasil: {$totalBerhasil} calon siswa baru, Dilewati: {$totalDilewati}.",
                'import_errors' => $errors,
                'import_stats' => [
                    'diproses' => $totalDiproses,
                    'berhasil' => $totalBerhasil,
                    'dilewati' => $totalDilewati,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan sistem saat memproses berkas import: ' . $e->getMessage());
        }
    }

    /**
     * Helper parsing berkas CSV dengan deteksi otomatis pemisah koma / titik-koma.
     */
    protected function parseCsv(string $filePath): array
    {
        $rows = [];
        $content = file_get_contents($filePath);
        if (!$content) return [];

        // Hapus UTF-8 BOM jika ada
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Deteksi delimiter
        $firstLine = strtok($content, "\r\n");
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';

        $handle = fopen($filePath, 'r');
        if ($handle === false) return [];

        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            $rows[] = $data;
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Helper parsing berkas XLSX sederhana via ZipArchive (OpenXML reader).
     */
    protected function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Ambil shared strings jika ada
        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $sxml = simplexml_load_string($sharedXml);
            if ($sxml && isset($sxml->si)) {
                foreach ($sxml->si as $val) {
                    $sharedStrings[] = (string) ($val->t ?? ($val->r->t ?? ''));
                }
            }
        }

        // 2. Ambil worksheet sheet1.xml
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        $xml = simplexml_load_string($sheetXml);
        if (!$xml || !isset($xml->sheetData->row)) {
            return [];
        }

        $rows = [];
        foreach ($xml->sheetData->row as $r) {
            $rowCells = [];
            foreach ($r->c as $c) {
                $val = (string) $c->v;
                $type = (string) $c['t'];

                if ($type === 's' && isset($sharedStrings[(int) $val])) {
                    $val = $sharedStrings[(int) $val];
                }
                $rowCells[] = $val;
            }
            if (!empty($rowCells)) {
                $rows[] = $rowCells;
            }
        }

        return $rows;
    }
}

