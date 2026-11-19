<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Pendaftar;
use App\Models\Jurusan;
use App\Models\Gelombang;
use App\Models\BerkasPendaftar;
use App\Models\Pembayaran;
use App\Models\PrestasiPendaftar;
use App\Models\PengaturanSekolah;
use App\Models\LogAktivitas;
use App\Services\MidtransService;
use Carbon\Carbon;

class SiswaDashboardController extends Controller
{
    private function getUserPendaftar()
    {
        $user = Auth::user();
        return Pendaftar::with(['jurusan1', 'jurusan2', 'berkas', 'pembayaran.voucher', 'pembayaranDaftarUlang', 'prestasi', 'nilaiSeleksi', 'terima.jurusan'])
            ->where('id_user', $user->id_user)
            ->first();
    }

    public function index()
    {
        $user = Auth::user();
        $pendaftar = $this->getUserPendaftar();
        $pengaturan = PengaturanSekolah::getPengaturan();

        // Calculate progress stage (1 to 7)
        // 1=Formulir, 2=Pembayaran, 3=Upload Berkas, 4=Verifikasi, 5=Tes Seleksi, 6=Kelulusan, 7=Daftar Ulang
        $stage = 1;
        $requiredDocs = ['ijazah', 'kk', 'pasfoto', 'akta'];
        $uploadedDocsCount = 0;
        $allDocsUploaded = false;
        if ($pendaftar) {
            $uploadedDocs = $pendaftar->berkas->pluck('jenis_berkas')->toArray();
            $uploadedDocsCount = count(array_intersect($requiredDocs, $uploadedDocs));
            $allDocsUploaded = $uploadedDocsCount >= count($requiredDocs);

            $stage = 2; // Form completed
            if ($pendaftar->status_pembayaran === 'Paid') {
                $stage = 3; // Paid → Upload Berkas
                if ($allDocsUploaded) {
                    $stage = 4; // All docs uploaded → Verifikasi
                    if ($pendaftar->status_verifikasi === 'Terverifikasi') {
                        $stage = 5; // Verified → Tes Seleksi
                        if ($pendaftar->status_kelulusan !== 'Diproses') {
                            $stage = 6; // Result announced → Kelulusan
                            if ($pendaftar->status_daftar_ulang === 'Selesai') {
                                $stage = 7; // Enrolled → Daftar Ulang complete
                            }
                        }
                    }
                }
            }
        }

        return view('siswa.dashboard', compact('user', 'pendaftar', 'pengaturan', 'stage', 'uploadedDocsCount', 'allDocsUploaded'));
    }

    public function formulir()
    {
        $user = Auth::user();
        $pendaftar = $this->getUserPendaftar();
        $jurusan = Jurusan::where('status_aktif', true)->get();

        // Tetap tampilkan jurusan lama yang sudah dipilih di draft jika kini dinonaktifkan
        if ($pendaftar && ($pendaftar->id_jurusan_1 || $pendaftar->id_jurusan_2)) {
            $selectedIds = array_filter([$pendaftar->id_jurusan_1, $pendaftar->id_jurusan_2]);
            $existingSelected = Jurusan::whereIn('id_jurusan', $selectedIds)->get();
            $jurusan = $jurusan->merge($existingSelected)->unique('id_jurusan');
        }

        $gelombang = Gelombang::where('status_aktif', true)->first();

        if ($pendaftar && $pendaftar->is_locked) {
            return redirect()->route('siswa.dashboard')->with('info', 'Formulir Anda sudah difinalisasi dan terkunci.');
        }

        return view('siswa.formulir', compact('user', 'pendaftar', 'jurusan', 'gelombang'));
    }

    public function simpanFormulir(Request $request)
    {
        $user = Auth::user();
        $pendaftar = $this->getUserPendaftar();

        if ($pendaftar && $pendaftar->is_locked) {
            return back()->with('error', 'Formulir Anda telah dikunci dan tidak dapat diubah.');
        }

        // Jika tombol Finalisasi ditekan dari formulir
        if ($request->input('action') === 'finalisasi') {
            return $this->prosesFinalisasi($request);
        }

        // Mode Simpan Draft: semua field boleh kosong (nullable) tapi jika diisi harus valid
        $validator = Validator::make($request->all(), [
            'id_jurusan_1' => 'nullable|exists:jurusan,id_jurusan',
            'id_jurusan_2' => 'nullable|different:id_jurusan_1|exists:jurusan,id_jurusan',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:Laki-laki,Perempuan',
            'agama' => 'nullable|string|max:50',
            'anak_ke' => 'nullable|integer|min:1',
            'jml_saudara' => 'nullable|integer|min:0',
            'hp_siswa' => 'nullable|string|max:20',
            'almt_siswa' => 'nullable|string',
            'berat_badan' => 'nullable|string|max:10',
            'tinggi_badan' => 'nullable|string|max:10',
            'golongan_darah' => 'nullable|string|max:5',
            'asal_sekolah' => 'nullable|string|max:100',
            'alamat_sekolah' => 'nullable|string',
            'nama_ayah' => 'nullable|string|max:100',
            'nama_ibu' => 'nullable|string|max:100',
            'nama_wali' => 'nullable|string|max:100',
            'almt_ortu' => 'nullable|string',
            'hp_ortu' => 'nullable|string|max:20',
            'kerja_ayah' => 'nullable|string|max:100',
            'kerja_ibu' => 'nullable|string|max:100',
            'kerja_wali' => 'nullable|string|max:100',
            'penghasilan_ortu' => 'nullable|string|max:50',
            'tanggungan_anak' => 'nullable|integer|min:1',
            'jalur_pendaftaran' => 'nullable|in:Reguler,Prestasi,Afirmasi,Zonasi',
            'ukuran_seragam' => 'nullable|in:S,M,L,XL,XXL,Custom',
            'ukuran_wearpack' => 'nullable|in:S,M,L,XL,XXL,Custom',
            'pilihan_ekskul' => 'nullable|array',
        ], [
            'id_jurusan_2.different' => 'Jurusan Pilihan 2 tidak boleh sama dengan Pilihan 1.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Gagal menyimpan draft: terdapat format isian yang tidak valid.');
        }

        // Cegah pemilihan jurusan baru yang berstatus nonaktif
        if ($request->filled('id_jurusan_1')) {
            $j1 = Jurusan::find($request->id_jurusan_1);
            if ($j1 && !$j1->status_aktif && (!$pendaftar || $pendaftar->id_jurusan_1 != $j1->id_jurusan)) {
                return back()->withErrors(['id_jurusan_1' => "Jurusan {$j1->nama_jurusan} saat ini berstatus nonaktif dan tidak menerima pendaftar baru."])->withInput();
            }
        }
        if ($request->filled('id_jurusan_2')) {
            $j2 = Jurusan::find($request->id_jurusan_2);
            if ($j2 && !$j2->status_aktif && (!$pendaftar || $pendaftar->id_jurusan_2 != $j2->id_jurusan)) {
                return back()->withErrors(['id_jurusan_2' => "Jurusan {$j2->nama_jurusan} saat ini berstatus nonaktif dan tidak menerima pendaftar baru."])->withInput();
            }
        }

        $validated = $validator->validated();

        // Cegah pemilihan jurusan baru yang berstatus nonaktif saat finalisasi
        if (!empty($validated['id_jurusan_1'])) {
            $j1 = Jurusan::find($validated['id_jurusan_1']);
            if ($j1 && !$j1->status_aktif && (!$pendaftar || $pendaftar->id_jurusan_1 != $j1->id_jurusan)) {
                return redirect()->route('siswa.formulir')
                    ->withErrors(['id_jurusan_1' => "Jurusan {$j1->nama_jurusan} saat ini berstatus nonaktif dan tidak menerima pendaftar baru."])
                    ->withInput()
                    ->with('error', "Jurusan {$j1->nama_jurusan} saat ini berstatus nonaktif.");
            }
        }
        if (!empty($validated['id_jurusan_2'])) {
            $j2 = Jurusan::find($validated['id_jurusan_2']);
            if ($j2 && !$j2->status_aktif && (!$pendaftar || $pendaftar->id_jurusan_2 != $j2->id_jurusan)) {
                return redirect()->route('siswa.formulir')
                    ->withErrors(['id_jurusan_2' => "Jurusan {$j2->nama_jurusan} saat ini berstatus nonaktif dan tidak menerima pendaftar baru."])
                    ->withInput()
                    ->with('error', "Jurusan {$j2->nama_jurusan} saat ini berstatus nonaktif.");
            }
        }
        $pilihanEkskulStr = !empty($validated['pilihan_ekskul']) ? implode(',', $validated['pilihan_ekskul']) : null;

        $gelombang = Gelombang::where('status_aktif', true)->first() ?: Gelombang::first();

        $kodeJurusan = !empty($validated['id_jurusan_1'])
            ? (Jurusan::where('id_jurusan', $validated['id_jurusan_1'])->value('kode_jurusan') ?? 'DFT')
            : 'DFT';

        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                DB::transaction(function () use ($pendaftar, $user, $gelombang, $kodeJurusan, $validated, $pilihanEkskulStr) {
                    if (!$pendaftar) {
                        $noPendaftaran = Pendaftar::generateNoPendaftaran($kodeJurusan);
                        $createData = array_merge($validated, [
                            'id_user' => $user->id_user,
                            'id_gelombang' => $gelombang ? $gelombang->id_gelombang : 1,
                            'no_pendaftaran' => $noPendaftaran,
                            'nisn' => $user->nisn,
                            'nama' => $user->nama,
                            'pilihan_ekskul' => $pilihanEkskulStr,
                            'status_pembayaran' => 'Pending',
                            'status_verifikasi' => 'Menunggu',
                            'status_kelulusan' => 'Diproses',
                            'status_daftar_ulang' => 'Belum',
                            'is_locked' => false,
                            'tgl_daftar' => Carbon::now(),
                        ]);

                        $newPendaftar = Pendaftar::create($createData);
                        LogAktivitas::log(
                            'SIMPAN_DRAFT',
                            'Siswa menyimpan draft formulir pendaftaran: ' . $noPendaftaran,
                            'pendaftar',
                            $newPendaftar->id_pendaftar,
                            null,
                            $validated,
                            'SUCCESS'
                        );
                    } else {
                        $oldData = $pendaftar->only(array_keys($validated));
                        $updateData = array_merge($validated, [
                            'pilihan_ekskul' => $pilihanEkskulStr,
                            'is_locked' => false,
                        ]);

                        if (str_contains($pendaftar->no_pendaftaran, '-DFT-') && !empty($validated['id_jurusan_1']) && $kodeJurusan !== 'DFT') {
                            $updateData['no_pendaftaran'] = Pendaftar::generateNoPendaftaran($kodeJurusan);
                        }

                        $pendaftar->update($updateData);
                        LogAktivitas::log(
                            'SIMPAN_DRAFT',
                            'Siswa memperbarui draft formulir pendaftaran: ' . $pendaftar->no_pendaftaran,
                            'pendaftar',
                            $pendaftar->id_pendaftar,
                            $oldData,
                            $validated,
                            'SUCCESS'
                        );
                    }
                });
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062 && str_contains($e->getMessage(), 'no_pendaftaran') && $attempts < $maxAttempts) {
                    usleep(50000);
                    continue;
                }
                throw $e;
            }
        }

        return redirect()->route('siswa.formulir')->with('success', 'Draft formulir berhasil disimpan! Anda dapat keluar atau melanjutkan pengisian kapan saja.');
    }

    public function finalisasi(Request $request)
    {
        return $this->prosesFinalisasi($request);
    }

    private function prosesFinalisasi(Request $request)
    {
        $user = Auth::user();
        $pendaftar = $this->getUserPendaftar();

        if ($pendaftar && $pendaftar->is_locked) {
            return redirect()->route('siswa.dashboard')->with('info', 'Formulir Anda sudah difinalisasi sebelumnya.');
        }

        // Gabungkan isian request dengan data draft yang tersimpan di database (jika ada)
        $inputData = $request->except(['_token', 'action']);
        if ($pendaftar) {
            $dbData = $pendaftar->attributesToArray();
            if ($pendaftar->pilihan_ekskul && !is_array($pendaftar->pilihan_ekskul)) {
                $dbData['pilihan_ekskul'] = explode(',', $pendaftar->pilihan_ekskul);
            }
            if ($pendaftar->tanggal_lahir instanceof Carbon || $pendaftar->tanggal_lahir instanceof \DateTimeInterface) {
                $dbData['tanggal_lahir'] = $pendaftar->tanggal_lahir->format('Y-m-d');
            }
            foreach ($dbData as $key => $val) {
                if (!isset($inputData[$key]) || $inputData[$key] === '' || $inputData[$key] === null) {
                    $inputData[$key] = $val;
                }
            }
        }

        $validator = Validator::make($inputData, [
            'id_jurusan_1' => 'required|exists:jurusan,id_jurusan',
            'id_jurusan_2' => 'nullable|different:id_jurusan_1|exists:jurusan,id_jurusan',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required|string|max:50',
            'anak_ke' => 'required|integer|min:1',
            'jml_saudara' => 'required|integer|min:0',
            'hp_siswa' => 'required|string|max:20',
            'almt_siswa' => 'required|string',
            'berat_badan' => 'required|string|max:10',
            'tinggi_badan' => 'required|string|max:10',
            'golongan_darah' => 'required|string|max:5',
            'asal_sekolah' => 'required|string|max:100',
            'alamat_sekolah' => 'nullable|string',
            'nama_ayah' => 'required|string|max:100',
            'nama_ibu' => 'required|string|max:100',
            'nama_wali' => 'nullable|string|max:100',
            'almt_ortu' => 'required|string',
            'hp_ortu' => 'required|string|max:20',
            'kerja_ayah' => 'required|string|max:100',
            'kerja_ibu' => 'required|string|max:100',
            'kerja_wali' => 'nullable|string|max:100',
            'penghasilan_ortu' => 'required|string|max:50',
            'tanggungan_anak' => 'required|integer|min:1',
            'jalur_pendaftaran' => 'required|in:Reguler,Prestasi,Afirmasi,Zonasi',
            'ukuran_seragam' => 'required|in:S,M,L,XL,XXL,Custom',
            'ukuran_wearpack' => 'required|in:S,M,L,XL,XXL,Custom',
            'pilihan_ekskul' => 'nullable|array',
        ], [
            'id_jurusan_2.different' => 'Jurusan Pilihan 2 tidak boleh sama dengan Pilihan 1.',
        ], [
            'id_jurusan_1' => 'Pilihan Jurusan 1',
            'id_jurusan_2' => 'Pilihan Jurusan 2',
            'tempat_lahir' => 'Tempat Lahir',
            'tanggal_lahir' => 'Tanggal Lahir',
            'jenis_kelamin' => 'Jenis Kelamin',
            'agama' => 'Agama',
            'anak_ke' => 'Anak Ke-',
            'jml_saudara' => 'Jumlah Saudara',
            'hp_siswa' => 'No. WhatsApp Siswa',
            'almt_siswa' => 'Alamat Siswa',
            'berat_badan' => 'Berat Badan',
            'tinggi_badan' => 'Tinggi Badan',
            'golongan_darah' => 'Golongan Darah',
            'asal_sekolah' => 'Asal Sekolah',
            'alamat_sekolah' => 'Alamat Sekolah',
            'nama_ayah' => 'Nama Ayah',
            'nama_ibu' => 'Nama Ibu',
            'almt_ortu' => 'Alamat Orang Tua',
            'hp_ortu' => 'No. WhatsApp Orang Tua',
            'kerja_ayah' => 'Pekerjaan Ayah',
            'kerja_ibu' => 'Pekerjaan Ibu',
            'penghasilan_ortu' => 'Penghasilan Orang Tua',
            'tanggungan_anak' => 'Tanggungan Anak',
            'jalur_pendaftaran' => 'Jalur Pendaftaran',
            'ukuran_seragam' => 'Ukuran Seragam',
            'ukuran_wearpack' => 'Ukuran Wearpack',
        ]);

        if ($validator->fails()) {
            LogAktivitas::log(
                'FINALISASI_GAGAL',
                'Percobaan finalisasi formulir gagal karena data wajib belum lengkap',
                'pendaftar',
                $pendaftar ? $pendaftar->id_pendaftar : null,
                null,
                ['errors' => array_keys($validator->errors()->toArray())],
                'FAILED'
            );

            return redirect()->route('siswa.formulir')
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Formulir belum dapat difinalisasi. Mohon lengkapi seluruh kolom wajib bertanda bintang (*).');
        }

        $validated = $validator->validated();

        // Cegah pemilihan jurusan baru yang berstatus nonaktif saat finalisasi
        if (!empty($validated['id_jurusan_1'])) {
            $j1 = Jurusan::find($validated['id_jurusan_1']);
            if ($j1 && !$j1->status_aktif && (!$pendaftar || $pendaftar->id_jurusan_1 != $j1->id_jurusan)) {
                return redirect()->route('siswa.formulir')
                    ->withErrors(['id_jurusan_1' => "Jurusan {$j1->nama_jurusan} saat ini berstatus nonaktif dan tidak menerima pendaftar baru."])
                    ->withInput()
                    ->with('error', "Jurusan {$j1->nama_jurusan} saat ini berstatus nonaktif.");
            }
        }
        if (!empty($validated['id_jurusan_2'])) {
            $j2 = Jurusan::find($validated['id_jurusan_2']);
            if ($j2 && !$j2->status_aktif && (!$pendaftar || $pendaftar->id_jurusan_2 != $j2->id_jurusan)) {
                return redirect()->route('siswa.formulir')
                    ->withErrors(['id_jurusan_2' => "Jurusan {$j2->nama_jurusan} saat ini berstatus nonaktif dan tidak menerima pendaftar baru."])
                    ->withInput()
                    ->with('error', "Jurusan {$j2->nama_jurusan} saat ini berstatus nonaktif.");
            }
        }
        $pilihanEkskulStr = !empty($validated['pilihan_ekskul']) ? implode(',', $validated['pilihan_ekskul']) : null;
        $gelombang = Gelombang::where('status_aktif', true)->first() ?: Gelombang::first();
        $jurusan1 = Jurusan::findOrFail($validated['id_jurusan_1']);

        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            $attempts++;
            try {
                DB::transaction(function () use (&$pendaftar, $user, $gelombang, $jurusan1, $validated, $pilihanEkskulStr) {
                    if (!$pendaftar) {
                        $noPendaftaran = Pendaftar::generateNoPendaftaran($jurusan1->kode_jurusan);
                        $pendaftar = Pendaftar::create(array_merge($validated, [
                            'id_user' => $user->id_user,
                            'id_gelombang' => $gelombang ? $gelombang->id_gelombang : 1,
                            'no_pendaftaran' => $noPendaftaran,
                            'nisn' => $user->nisn,
                            'nama' => $user->nama,
                            'pilihan_ekskul' => $pilihanEkskulStr,
                            'status_pembayaran' => 'Pending',
                            'status_verifikasi' => 'Menunggu',
                            'status_kelulusan' => 'Diproses',
                            'status_daftar_ulang' => 'Belum',
                            'is_locked' => true,
                            'tgl_daftar' => Carbon::now(),
                        ]));
                    } else {
                        $updateData = array_merge($validated, [
                            'pilihan_ekskul' => $pilihanEkskulStr,
                            'is_locked' => true,
                        ]);

                        if (str_contains($pendaftar->no_pendaftaran, '-DFT-')) {
                            $updateData['no_pendaftaran'] = Pendaftar::generateNoPendaftaran($jurusan1->kode_jurusan);
                        }

                        $pendaftar->update($updateData);
                    }

                    LogAktivitas::log(
                        'FINALISASI',
                        'Siswa mengunci dan memfinalisasi data formulir: ' . $pendaftar->no_pendaftaran,
                        'pendaftar',
                        $pendaftar->id_pendaftar,
                        ['is_locked' => false],
                        ['is_locked' => true],
                        'SUCCESS'
                    );
                });
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062 && str_contains($e->getMessage(), 'no_pendaftaran') && $attempts < $maxAttempts) {
                    usleep(50000);
                    continue;
                }
                throw $e;
            }
        }

        return redirect()->route('siswa.dashboard')->with('success', 'Data formulir berhasil difinalisasi! Anda sekarang dapat melakukan pembayaran dan mengunggah berkas.');
    }

    public function berkas()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return redirect()->route('siswa.formulir')->with('error', 'Silakan lengkapi formulir terlebih dahulu sebelum mengunggah berkas.');
        }

        $berkas = $pendaftar->berkas;
        $prestasi = $pendaftar->prestasi;

        return view('siswa.berkas', compact('pendaftar', 'berkas', 'prestasi'));
    }

    public function uploadBerkas(Request $request)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return back()->with('error', 'Formulir belum dibuat.');
        }

        $request->validate([
            'jenis_berkas' => 'required|in:ijazah,kk,pasfoto,akta,lainnya',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|mimetypes:image/jpeg,image/png,application/pdf|max:3072',
        ], [
            'file.max' => 'Ukuran file maksimal adalah 3 MB.',
            'file.mimes' => 'Format file yang diperbolehkan hanya JPG, PNG, atau PDF.',
            'file.mimetypes' => 'Tipe konten file tidak valid. Hanya gambar dan PDF yang diizinkan.',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        // Simpan dengan nama acak/unguessable hash untuk privasi dokumen kependudukan (UU PDP)
        $filename = $request->jenis_berkas . '_' . Str::random(32) . '.' . $ext;
        // Simpan ke private disk (storage/app/private/berkas_pendaftar)
        $path = $file->storeAs('berkas_pendaftar', $filename, 'local');

        // Check if existing file of this type exists
        $existing = BerkasPendaftar::where('id_pendaftar', $pendaftar->id_pendaftar)
            ->where('jenis_berkas', $request->jenis_berkas)
            ->first();

        $isUpdate = (bool) $existing;

        if ($existing) {
            // Hapus file lama dari private maupun public jika ada
            if (Storage::disk('local')->exists($existing->path_file)) {
                Storage::disk('local')->delete($existing->path_file);
            }
            if (Storage::disk('public')->exists($existing->path_file)) {
                Storage::disk('public')->delete($existing->path_file);
            }

            $existing->update([
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'status_berkas' => 'Menunggu',
                'catatan' => null,
            ]);
        } else {
            BerkasPendaftar::create([
                'id_pendaftar' => $pendaftar->id_pendaftar,
                'jenis_berkas' => $request->jenis_berkas,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'status_berkas' => 'Menunggu',
            ]);
        }

        LogAktivitas::log(
            $isUpdate ? 'PENGGANTIAN_BERKAS' : 'UNGGAH_BERKAS',
            'Siswa ' . ($isUpdate ? 'mengganti' : 'mengunggah') . ' berkas: ' . strtoupper($request->jenis_berkas),
            'berkas_pendaftar',
            $pendaftar->id_pendaftar,
            $isUpdate ? ['nama_file' => $existing->nama_file] : null,
            ['nama_file' => $file->getClientOriginalName(), 'jenis_berkas' => $request->jenis_berkas],
            'SUCCESS',
            $pendaftar->id_user,
            null,
            'calon_siswa'
        );

        return back()->with('success', 'Berkas ' . strtoupper($request->jenis_berkas) . ' berhasil diunggah dengan aman.');
    }

    public function uploadPrestasi(Request $request)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return back()->with('error', 'Formulir belum dibuat.');
        }

        $validated = $request->validate([
            'nama_prestasi' => 'required|string|max:150',
            'tingkat' => 'required|in:Kecamatan,Kabupaten/Kota,Provinsi,Nasional,Internasional',
            'kategori' => 'required|in:Keagamaan/Tahfiz,Akademik,Olahraga,Seni/Budaya',
            'peringkat' => 'required|string|max:50',
            'tahun' => 'required|integer|min:2020|max:' . date('Y'),
            'penyelenggara' => 'required|string|max:150',
            'file_sertifikat' => 'required|file|mimes:jpg,jpeg,png,pdf|mimetypes:image/jpeg,image/png,application/pdf|max:3072',
        ], [
            'file_sertifikat.max' => 'Ukuran file sertifikat maksimal adalah 3 MB.',
            'file_sertifikat.mimes' => 'Format file yang diperbolehkan hanya JPG, PNG, atau PDF.',
        ]);

        $file = $request->file('file_sertifikat');
        $ext = strtolower($file->getClientOriginalExtension());
        $filename = 'prestasi_' . Str::random(32) . '.' . $ext;
        $path = $file->storeAs('prestasi_pendaftar', $filename, 'local');

        // Score estimation
        $skor = 10;
        if ($validated['tingkat'] === 'Nasional' || $validated['tingkat'] === 'Internasional') {
            $skor = 30;
        } elseif ($validated['tingkat'] === 'Provinsi') {
            $skor = 20;
        }

        PrestasiPendaftar::create([
            'id_pendaftar' => $pendaftar->id_pendaftar,
            'nama_prestasi' => $validated['nama_prestasi'],
            'tingkat' => $validated['tingkat'],
            'kategori' => $validated['kategori'],
            'peringkat' => $validated['peringkat'],
            'tahun' => $validated['tahun'],
            'penyelenggara' => $validated['penyelenggara'],
            'berkas_sertifikat' => $path,
            'skor_bobot' => $skor,
            'status_verifikasi' => 'Menunggu',
        ]);

        LogAktivitas::log('INSERT', 'Siswa menambahkan sertifikat prestasi: ' . $validated['nama_prestasi'], 'prestasi_pendaftar', $pendaftar->id_pendaftar);

        return back()->with('success', 'Sertifikat prestasi berhasil ditambahkan dan menunggu verifikasi panitia.');
    }

    public function pembayaran()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return redirect()->route('siswa.formulir')->with('error', 'Silakan lengkapi formulir terlebih dahulu.');
        }

        $gelombang = $pendaftar->gelombang ?: Gelombang::where('status_aktif', true)->first();
        $pembayaran = $pendaftar->pembayaran;

        // Ambil daftar voucher promo aktif yang masih berlaku dan kuota masih ada
        $promoVouchers = \App\Models\VoucherPendaftaran::where('status_aktif', true)
            ->where(function ($q) {
                $q->whereNull('berlaku_sampai')
                  ->orWhere('berlaku_sampai', '>=', Carbon::today());
            })
            ->where(function ($q) {
                $q->whereNull('berlaku_mulai')
                  ->orWhere('berlaku_mulai', '<=', Carbon::today());
            })
            ->whereRaw('kuota_terpakai < kuota_voucher')
            ->orderBy('id_voucher', 'desc')
            ->get();

        return view('siswa.pembayaran', compact('pendaftar', 'gelombang', 'pembayaran', 'promoVouchers'));
    }

    public function prosesBayarTunai(Request $request)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return back()->with('error', 'Data pendaftar tidak ditemukan.');
        }

        $gelombang = $pendaftar->gelombang ?: (Gelombang::where('status_aktif', true)->first() ?: Gelombang::first());
        $nominalAsli = $gelombang ? (float)$gelombang->biaya_pendaftaran : 100000.00;
        $kodeVoucherInput = $request->filled('kode_voucher') ? strtoupper(trim($request->kode_voucher)) : null;

        try {
            $pembayaran = DB::transaction(function () use ($pendaftar, $gelombang, $nominalAsli, $kodeVoucherInput) {
                $potongan = 0.00;
                $voucherId = null;

                $existingTrx = Pembayaran::where('id_pendaftar', $pendaftar->id_pendaftar)
                    ->where('status_transaksi', 'pending')
                    ->first();

                if ($kodeVoucherInput) {
                    $voucher = \App\Models\VoucherPendaftaran::where('kode_voucher', $kodeVoucherInput)
                        ->lockForUpdate()
                        ->first();

                    if (!$voucher) {
                        throw new \InvalidArgumentException("Kode voucher '{$kodeVoucherInput}' tidak ditemukan.");
                    }

                    $check = $voucher->validateAvailability();
                    if (!$check['valid']) {
                        throw new \InvalidArgumentException($check['message']);
                    }

                    $voucherId = $voucher->id_voucher;
                    $potongan = $voucher->hitungPotongan($nominalAsli);

                    // Kelola kuota terpakai secara aman
                    if (!$existingTrx || $existingTrx->id_voucher !== $voucherId) {
                        if ($existingTrx && $existingTrx->id_voucher) {
                            $oldVoucher = \App\Models\VoucherPendaftaran::find($existingTrx->id_voucher);
                            if ($oldVoucher && $oldVoucher->kuota_terpakai > 0) {
                                $oldVoucher->decrement('kuota_terpakai');
                            }
                        }
                        $voucher->increment('kuota_terpakai');
                    }
                } else {
                    if ($existingTrx && $existingTrx->id_voucher) {
                        $oldVoucher = \App\Models\VoucherPendaftaran::find($existingTrx->id_voucher);
                        if ($oldVoucher && $oldVoucher->kuota_terpakai > 0) {
                            $oldVoucher->decrement('kuota_terpakai');
                        }
                    }
                }

                $totalBayar = max(0.00, $nominalAsli - $potongan);
                $kodeTransaksi = $existingTrx ? $existingTrx->kode_transaksi : Pembayaran::generateKodeTransaksi();

                $trx = Pembayaran::updateOrCreate(
                    ['id_pendaftar' => $pendaftar->id_pendaftar],
                    [
                        'id_gelombang' => $gelombang ? $gelombang->id_gelombang : 1,
                        'id_voucher' => $voucherId,
                        'kode_transaksi' => $kodeTransaksi,
                        'nominal' => $nominalAsli,
                        'potongan' => $potongan,
                        'total_bayar' => $totalBayar,
                        'tipe_bayar' => 'tunai_tu',
                        'metode_pembayaran' => 'Loket TU',
                        'status_transaksi' => 'pending',
                        'catatan' => 'Siswa memilih pembayaran tunai di loket TU sekolah' . ($kodeVoucherInput ? " (Voucher: {$kodeVoucherInput})" : '') . '.',
                    ]
                );

                LogAktivitas::log('INSERT', 'Siswa memilih opsi bayar tunai di loket TU: ' . $kodeTransaksi, 'pembayaran', $trx->id_pembayaran);

                return $trx;
            });
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat tagihan pembayaran tunai: ' . $e->getMessage())->withInput();
        }

        return back()->with('success', 'Nomor tagihan tunai telah dibuat: ' . $pembayaran->kode_transaksi . ' (Total: Rp ' . number_format($pembayaran->total_bayar, 0, ',', '.') . '). Silakan tunjukkan ke Petugas TU di sekolah.');
    }

    /**
     * Inisiasi transaksi Midtrans Snap resmi untuk pembayaran online siswa.
     */
    public function createSnapPayment(Request $request, MidtransService $midtransService)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan.'], 404);
            }
            return back()->with('error', 'Data pendaftar tidak ditemukan.');
        }

        $gelombang = $pendaftar->gelombang ?: (Gelombang::where('status_aktif', true)->first() ?: Gelombang::first());
        $nominalAsli = $gelombang ? (float)$gelombang->biaya_pendaftaran : 100000.00;
        $metode = $request->input('metode_pembayaran', 'Midtrans Snap');
        $kodeVoucherInput = $request->filled('kode_voucher') ? strtoupper(trim($request->kode_voucher)) : null;

        $channel = $request->input('channel');
        $enabledPayments = null;
        if (!empty($channel)) {
            $channelMap = [
                'bca_va' => ['bca_va'],
                'bni_va' => ['bni_va'],
                'bri_va' => ['bri_va'],
                'mandiri' => ['echannel'],
                'echannel' => ['echannel'],
                'permata_va' => ['permata_va'],
                'cimb_va' => ['cimb_va'],
                'danamon_va' => ['danamon_va'],
                'bsi_va' => ['other_va', 'bni_va'],
                'seabank_va' => ['other_va'],
                'qris' => ['qris'],
                'gopay' => ['gopay'],
                'shopeepay' => ['shopeepay'],
                'ovo' => ['qris'],
                'alfamart' => ['alfamart', 'cstore'],
                'indomaret' => ['indomaret', 'cstore'],
            ];
            if (isset($channelMap[$channel])) {
                $enabledPayments = $channelMap[$channel];
            }
        }

        try {
            $pembayaran = DB::transaction(function () use ($pendaftar, $gelombang, $nominalAsli, $metode, $kodeVoucherInput) {
                $potongan = 0.00;
                $voucherId = null;

                $existingTrx = Pembayaran::where('id_pendaftar', $pendaftar->id_pendaftar)
                    ->where('status_transaksi', 'pending')
                    ->first();

                // Validasi kuota voucher dengan row locking mencegah race condition
                if ($kodeVoucherInput) {
                    $voucher = \App\Models\VoucherPendaftaran::where('kode_voucher', $kodeVoucherInput)
                        ->lockForUpdate()
                        ->first();

                    if (!$voucher) {
                        throw new \InvalidArgumentException("Kode voucher '{$kodeVoucherInput}' tidak ditemukan.");
                    }

                    $check = $voucher->validateAvailability();
                    if (!$check['valid']) {
                        throw new \InvalidArgumentException($check['message']);
                    }

                    $voucherId = $voucher->id_voucher;
                    $potongan = $voucher->hitungPotongan($nominalAsli);

                    // Kelola kuota secara aman
                    if (!$existingTrx || $existingTrx->id_voucher !== $voucherId) {
                        if ($existingTrx && $existingTrx->id_voucher) {
                            $oldVoucher = \App\Models\VoucherPendaftaran::find($existingTrx->id_voucher);
                            if ($oldVoucher && $oldVoucher->kuota_terpakai > 0) {
                                $oldVoucher->decrement('kuota_terpakai');
                            }
                        }
                        $voucher->increment('kuota_terpakai');
                    }
                } else {
                    if ($existingTrx && $existingTrx->id_voucher) {
                        $oldVoucher = \App\Models\VoucherPendaftaran::find($existingTrx->id_voucher);
                        if ($oldVoucher && $oldVoucher->kuota_terpakai > 0) {
                            $oldVoucher->decrement('kuota_terpakai');
                        }
                    }
                }

                $totalBayar = max(0.00, $nominalAsli - $potongan);
                $kodeTransaksi = $existingTrx ? $existingTrx->kode_transaksi : Pembayaran::generateKodeTransaksi();

                $trx = Pembayaran::updateOrCreate(
                    ['id_pendaftar' => $pendaftar->id_pendaftar],
                    [
                        'id_gelombang' => $gelombang ? $gelombang->id_gelombang : 1,
                        'id_voucher' => $voucherId,
                        'kode_transaksi' => $kodeTransaksi,
                        'nominal' => $nominalAsli,
                        'potongan' => $potongan,
                        'total_bayar' => $totalBayar,
                        'tipe_bayar' => 'midtrans',
                        'metode_pembayaran' => $metode,
                        'status_transaksi' => 'pending',
                        'waktu_transaksi' => Carbon::now(),
                        'catatan' => 'Tagihan online dibuat. Menunggu penyelesaian pembayaran via Midtrans Snap (' . $metode . ').',
                    ]
                );

                // JANGAN pernah mengubah status pendaftar menjadi Paid di sini!
                // Status pendaftar hanya boleh diubah via webhook terverifikasi atau loket TU.
                if ($pendaftar->status_pembayaran !== 'Paid') {
                    $pendaftar->update(['status_pembayaran' => 'Pending']);
                }

                LogAktivitas::log('INSERT', 'Siswa membuat tagihan pembayaran online Midtrans: ' . $kodeTransaksi, 'pembayaran', $trx->id_pembayaran);

                return $trx;
            });
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage())->withInput();
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
            }
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage())->withInput();
        }

        $snapData = $midtransService->createSnapTransaction(
            $pendaftar,
            (float)$pembayaran->total_bayar,
            $pembayaran->kode_transaksi,
            $enabledPayments
        );

        if (!empty($snapData['token'])) {
            $pembayaran->update(['snap_token' => $snapData['token']]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'order_id' => $pembayaran->kode_transaksi,
                'biaya_awal' => (float)$pembayaran->nominal,
                'potongan' => (float)$pembayaran->potongan,
                'total_bayar' => (float)$pembayaran->total_bayar,
                'snap_token' => $snapData['token'] ?? null,
                'redirect_url' => $snapData['redirect_url'] ?? null,
                'is_simulated' => $snapData['is_simulated'] ?? false,
                'message' => 'Tagihan online berhasil dibuat. Silakan selesaikan transaksi melalui Midtrans.',
            ]);
        }

        return redirect()->route('siswa.pembayaran')->with('info', 'Tagihan ' . $pembayaran->kode_transaksi . ' dibuat (Status: Menunggu Pembayaran). Selesaikan pembayaran untuk diverifikasi.');
    }

    /**
     * Endpoint simulasi yang diproteksi ketat:
     * - DITOLAK pada production (HTTP 403)
     * - Pada local/testing, browser request TIDAK PERNAH melunasi status pembayaran langsung.
     */
    public function prosesBayarMidtransSimulasi(Request $request, MidtransService $midtransService)
    {
        if (app()->isProduction()) {
            abort(403, 'Endpoint simulasi tidak dapat digunakan pada lingkungan production.');
        }

        // Jangan pernah mengubah status pembayaran menjadi lunas hanya karena siswa mengirim request dari browser.
        // Arahkan ke pembuatan transaksi pending Snap.
        return $this->createSnapPayment($request, $midtransService);
    }

    /**
     * Inisiasi transaksi QRIS Midtrans Sandbox resmi untuk siswa.
     * Mengambil qr_string dan action QR code resmi langsung dari API Midtrans.
     * Sesuai ketentuan: status pendaftar TIDAK berubah menjadi Paid di sini.
     */
    public function generateQrisPayment(Request $request, MidtransService $midtransService)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return response()->json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan.'], 404);
        }

        if ($pendaftar->status_pembayaran === 'Paid') {
            return response()->json([
                'success' => false,
                'is_paid' => true,
                'message' => 'Pembayaran formulir Anda sudah LUNAS.',
            ], 400);
        }

        $gelombang = $pendaftar->gelombang ?: (Gelombang::where('status_aktif', true)->first() ?: Gelombang::first());
        $nominalAsli = $gelombang ? (float)$gelombang->biaya_pendaftaran : 100000.00;
        $kodeVoucherInput = $request->filled('kode_voucher') ? strtoupper(trim($request->kode_voucher)) : null;

        try {
            $pembayaran = DB::transaction(function () use ($pendaftar, $gelombang, $nominalAsli, $kodeVoucherInput) {
                $potongan = 0.00;
                $voucherId = null;

                $existingTrx = Pembayaran::where('id_pendaftar', $pendaftar->id_pendaftar)
                    ->where('status_transaksi', 'pending')
                    ->first();

                if ($kodeVoucherInput) {
                    $voucher = \App\Models\VoucherPendaftaran::where('kode_voucher', $kodeVoucherInput)
                        ->lockForUpdate()
                        ->first();

                    if (!$voucher) {
                        throw new \InvalidArgumentException("Kode voucher '{$kodeVoucherInput}' tidak ditemukan.");
                    }

                    $check = $voucher->validateAvailability();
                    if (!$check['valid']) {
                        throw new \InvalidArgumentException($check['message']);
                    }

                    $voucherId = $voucher->id_voucher;
                    $potongan = $voucher->hitungPotongan($nominalAsli);

                    if (!$existingTrx || $existingTrx->id_voucher !== $voucherId) {
                        if ($existingTrx && $existingTrx->id_voucher) {
                            $oldVoucher = \App\Models\VoucherPendaftaran::find($existingTrx->id_voucher);
                            if ($oldVoucher && $oldVoucher->kuota_terpakai > 0) {
                                $oldVoucher->decrement('kuota_terpakai');
                            }
                        }
                        $voucher->increment('kuota_terpakai');
                    }
                } else {
                    if ($existingTrx && $existingTrx->id_voucher) {
                        $oldVoucher = \App\Models\VoucherPendaftaran::find($existingTrx->id_voucher);
                        if ($oldVoucher && $oldVoucher->kuota_terpakai > 0) {
                            $oldVoucher->decrement('kuota_terpakai');
                        }
                    }
                }

                $totalBayar = max(0.00, $nominalAsli - $potongan);
                $kodeTransaksi = $existingTrx ? $existingTrx->kode_transaksi : Pembayaran::generateKodeTransaksi();

                $trx = Pembayaran::updateOrCreate(
                    ['id_pendaftar' => $pendaftar->id_pendaftar],
                    [
                        'id_gelombang' => $gelombang ? $gelombang->id_gelombang : 1,
                        'id_voucher' => $voucherId,
                        'kode_transaksi' => $kodeTransaksi,
                        'nominal' => $nominalAsli,
                        'potongan' => $potongan,
                        'total_bayar' => $totalBayar,
                        'tipe_bayar' => 'midtrans',
                        'metode_pembayaran' => 'QRIS (Midtrans Sandbox)',
                        'status_transaksi' => 'pending',
                        'waktu_transaksi' => Carbon::now(),
                        'catatan' => 'Menunggu penyelesaian pembayaran QRIS via Midtrans Sandbox.',
                    ]
                );

                if ($pendaftar->status_pembayaran !== 'Paid') {
                    $pendaftar->update(['status_pembayaran' => 'Pending']);
                }

                LogAktivitas::log('INSERT', 'Siswa menginisiasi pembayaran QRIS Midtrans Sandbox: ' . $kodeTransaksi, 'pembayaran', $trx->id_pembayaran);

                return $trx;
            });
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }

        // Panggil API Midtrans untuk mendapatkan QRIS resmi
        $qrisResult = $midtransService->createQrisTransaction($pendaftar, (float)$pembayaran->total_bayar, $pembayaran->kode_transaksi);

        if (!$qrisResult['configured']) {
            return response()->json([
                'success' => false,
                'configured' => false,
                'order_id' => $pembayaran->kode_transaksi,
                'nominal' => (float)$pembayaran->nominal,
                'potongan' => (float)$pembayaran->potongan,
                'total_bayar' => (float)$pembayaran->total_bayar,
                'message' => $qrisResult['message'] ?? 'Konfigurasi Midtrans Server Key belum diatur di .env atau Pengaturan Admin.',
            ], 200);
        }

        if (!$qrisResult['success']) {
            return response()->json([
                'success' => false,
                'configured' => true,
                'order_id' => $pembayaran->kode_transaksi,
                'message' => $qrisResult['message'] ?? 'Gagal membuat QRIS di server Midtrans.',
            ], 422);
        }

        // Simpan QR string dan URL resmi ke database pembayaran
        $pembayaran->update([
            'qr_string' => $qrisResult['qr_string'],
            'qr_url' => $qrisResult['qr_code_url'],
        ]);

        return response()->json([
            'success' => true,
            'configured' => true,
            'order_id' => $pembayaran->kode_transaksi,
            'nominal' => (float)$pembayaran->nominal,
            'potongan' => (float)$pembayaran->potongan,
            'total_bayar' => (float)$pembayaran->total_bayar,
            'qr_string' => $qrisResult['qr_string'],
            'qr_code_url' => $qrisResult['qr_code_url'],
            'simulator_url' => $qrisResult['simulator_url'] ?? 'https://simulator.sandbox.midtrans.com/qris/index',
            'status' => 'pending',
            'message' => 'Kode QRIS resmi berhasil diperoleh dari Midtrans.',
        ]);
    }

    /**
     * Memeriksa status transaksi ke Midtrans (untuk localhost atau sinkronisasi langsung).
     */
    public function cekStatusMidtrans(Request $request, MidtransService $midtransService)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return response()->json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan.'], 404);
        }

        $pembayaran = $pendaftar->pembayaran;
        if (!$pembayaran) {
            return response()->json(['success' => false, 'message' => 'Belum ada transaksi pembayaran yang dibuat.'], 404);
        }

        if ($pembayaran->status_transaksi === 'settlement' || $pendaftar->status_pembayaran === 'Paid') {
            return response()->json([
                'success' => true,
                'is_paid' => true,
                'status' => 'settlement',
                'message' => 'Pembayaran Anda telah terverifikasi LUNAS.',
            ]);
        }

        if (!$midtransService->isConfigured()) {
            return response()->json([
                'success' => false,
                'configured' => false,
                'status' => $pembayaran->status_transaksi,
                'is_paid' => false,
                'message' => 'Midtrans Server Key belum dikonfigurasi pada sistem.',
            ]);
        }

        $statusData = $midtransService->checkTransactionStatus($pembayaran->kode_transaksi);
        if (!$statusData['success'] || empty($statusData['data'])) {
            return response()->json([
                'success' => false,
                'configured' => true,
                'status' => $pembayaran->status_transaksi,
                'is_paid' => false,
                'message' => $statusData['message'] ?? 'Transaksi belum ditemukan di server Midtrans.',
            ]);
        }

        // Proses notifikasi resmi dengan data dari API status Midtrans
        $midtransService->processNotification($statusData['data']);
        $pembayaran->refresh();
        $pendaftar->refresh();

        $isPaid = ($pembayaran->status_transaksi === 'settlement' && $pendaftar->status_pembayaran === 'Paid');

        $message = match ($pembayaran->status_transaksi) {
            'settlement' => 'Alhamdulillah, pembayaran telah berhasil diverifikasi dan berstatus LUNAS!',
            'expire' => 'Tagihan transaksi ini telah kedaluwarsa. Silakan lakukan pembayaran baru.',
            'cancel' => 'Pembayaran transaksi ini dibatalkan atau gagal diproses.',
            default => 'Status transaksi terkini: ' . strtoupper($pembayaran->status_transaksi) . ' (Menunggu Pembayaran).',
        };

        return response()->json([
            'success' => true,
            'configured' => true,
            'is_paid' => $isPaid,
            'status' => $pembayaran->status_transaksi,
            'message' => $message,
        ]);
    }

    // === DAFTAR ULANG & PENGATURAN AKUN ===

    public function daftarUlang()
    {
        $user = Auth::user();
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return redirect()->route('siswa.dashboard')->with('error', 'Silakan lengkapi formulir pendaftaran terlebih dahulu.');
        }

        $pengaturan = PengaturanSekolah::getPengaturan();
        $pembayaranDaftarUlang = $pendaftar->pembayaranDaftarUlang;
        return view('siswa.daftar_ulang', compact('user', 'pendaftar', 'pengaturan', 'pembayaranDaftarUlang'));
    }

    public function generateQrisDaftarUlang(Request $request, MidtransService $midtransService)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return response()->json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan.'], 404);
        }

        if ($pendaftar->status_kelulusan !== 'Diterima') {
            return response()->json(['success' => false, 'message' => 'Fitur daftar ulang hanya untuk siswa yang dinyatakan diterima.'], 403);
        }

        $pengaturan = PengaturanSekolah::getPengaturan();
        $nominal = (float) ($pengaturan->biaya_daftar_ulang ?? 1500000.00);

        try {
            $pembayaran = DB::transaction(function () use ($pendaftar, $nominal) {
                $existingTrx = $pendaftar->pembayaranDaftarUlang;
                $kodeTransaksi = $existingTrx ? $existingTrx->kode_transaksi : Pembayaran::generateKodeTransaksi('daftar_ulang');

                $trx = Pembayaran::updateOrCreate(
                    [
                        'id_pendaftar' => $pendaftar->id_pendaftar,
                        'jenis_pembayaran' => 'daftar_ulang',
                    ],
                    [
                        'id_gelombang' => $pendaftar->id_gelombang,
                        'kode_transaksi' => $kodeTransaksi,
                        'nominal' => $nominal,
                        'potongan' => 0.00,
                        'total_bayar' => $nominal,
                        'tipe_bayar' => 'midtrans',
                        'metode_pembayaran' => 'QRIS (Midtrans Sandbox)',
                        'status_transaksi' => ($existingTrx && $existingTrx->status_transaksi === 'settlement') ? 'settlement' : 'pending',
                        'waktu_transaksi' => Carbon::now(),
                        'catatan' => 'Pembayaran Daftar Ulang QRIS via Midtrans Sandbox.',
                    ]
                );

                LogAktivitas::log('INSERT', 'Siswa menginisiasi pembayaran QRIS Daftar Ulang Midtrans: ' . $kodeTransaksi, 'pembayaran', $trx->id_pembayaran);

                return $trx;
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }

        if ($pembayaran->status_transaksi === 'settlement') {
            return response()->json([
                'success' => true,
                'is_paid' => true,
                'status' => 'settlement',
                'order_id' => $pembayaran->kode_transaksi,
                'message' => 'Daftar ulang sudah lunas.',
            ]);
        }

        $qrisResult = $midtransService->createQrisTransaction(
            $pendaftar,
            (float) $pembayaran->total_bayar,
            $pembayaran->kode_transaksi,
            'Biaya Daftar Ulang PPDB SMK IB Khalifah Bangsa'
        );

        if (!$qrisResult['configured']) {
            return response()->json([
                'success' => false,
                'configured' => false,
                'order_id' => $pembayaran->kode_transaksi,
                'nominal' => (float)$pembayaran->nominal,
                'total_bayar' => (float)$pembayaran->total_bayar,
                'message' => $qrisResult['message'] ?? 'Konfigurasi Midtrans Server Key belum diatur di .env atau Pengaturan Admin.',
            ], 200);
        }

        if (!$qrisResult['success']) {
            return response()->json([
                'success' => false,
                'configured' => true,
                'order_id' => $pembayaran->kode_transaksi,
                'message' => $qrisResult['message'] ?? 'Gagal membuat QRIS di server Midtrans.',
            ], 422);
        }

        $pembayaran->update([
            'qr_string' => $qrisResult['qr_string'],
            'qr_url' => $qrisResult['qr_code_url'],
        ]);

        return response()->json([
            'success' => true,
            'configured' => true,
            'order_id' => $pembayaran->kode_transaksi,
            'nominal' => (float)$pembayaran->nominal,
            'total_bayar' => (float)$pembayaran->total_bayar,
            'qr_string' => $qrisResult['qr_string'],
            'qr_code_url' => $qrisResult['qr_code_url'],
            'simulator_url' => $qrisResult['simulator_url'] ?? 'https://simulator.sandbox.midtrans.com/qris/index',
            'status' => 'pending',
            'message' => 'Kode QRIS resmi Daftar Ulang berhasil dibuat.',
        ]);
    }

    public function prosesBayarDaftarUlangTunai(Request $request)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar || $pendaftar->status_kelulusan !== 'Diterima') {
            return back()->with('error', 'Konfirmasi daftar ulang hanya untuk siswa yang diterima.');
        }

        $pengaturan = PengaturanSekolah::getPengaturan();
        $nominal = (float) ($pengaturan->biaya_daftar_ulang ?? 1500000.00);

        $existingTrx = $pendaftar->pembayaranDaftarUlang;
        $kodeTransaksi = $existingTrx ? $existingTrx->kode_transaksi : Pembayaran::generateKodeTransaksi('daftar_ulang');

        Pembayaran::updateOrCreate(
            [
                'id_pendaftar' => $pendaftar->id_pendaftar,
                'jenis_pembayaran' => 'daftar_ulang',
            ],
            [
                'id_gelombang' => $pendaftar->id_gelombang,
                'kode_transaksi' => $kodeTransaksi,
                'nominal' => $nominal,
                'potongan' => 0.00,
                'total_bayar' => $nominal,
                'tipe_bayar' => 'tunai_tu',
                'metode_pembayaran' => 'Bayar Tunai di Loket TU',
                'status_transaksi' => 'pending',
                'waktu_transaksi' => Carbon::now(),
                'catatan' => 'Siswa memilih opsi pembayaran tunai daftar ulang di loket TU.',
            ]
        );

        LogAktivitas::log('UPDATE', 'Siswa memilih opsi pembayaran tunai daftar ulang di loket TU: ' . $kodeTransaksi, 'pembayaran', $pendaftar->id_pendaftar);

        return back()->with('success', 'Opsi pembayaran tunai daftar ulang berhasil dipilih. Silakan lakukan pembayaran di Loket TU Sekolah.');
    }

    public function cekStatusDaftarUlangMidtrans(Request $request, MidtransService $midtransService)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return response()->json(['success' => false, 'message' => 'Data pendaftar tidak ditemukan.'], 404);
        }

        $pembayaran = $pendaftar->pembayaranDaftarUlang;
        if (!$pembayaran) {
            return response()->json(['success' => false, 'message' => 'Belum ada transaksi daftar ulang yang dibuat.'], 404);
        }

        if ($pembayaran->status_transaksi === 'settlement' || $pendaftar->status_daftar_ulang === 'Selesai') {
            return response()->json([
                'success' => true,
                'is_paid' => true,
                'status' => 'settlement',
                'message' => 'Pembayaran daftar ulang Anda telah terverifikasi LUNAS.',
            ]);
        }

        if (!$midtransService->isConfigured()) {
            return response()->json([
                'success' => false,
                'configured' => false,
                'status' => $pembayaran->status_transaksi,
                'is_paid' => false,
                'message' => 'Midtrans Server Key belum dikonfigurasi.',
            ]);
        }

        $statusData = $midtransService->checkTransactionStatus($pembayaran->kode_transaksi);
        if (!$statusData['success'] || empty($statusData['data'])) {
            return response()->json([
                'success' => false,
                'configured' => true,
                'status' => $pembayaran->status_transaksi,
                'is_paid' => false,
                'message' => $statusData['message'] ?? 'Transaksi belum ditemukan di server Midtrans.',
            ]);
        }

        $midtransService->processNotification($statusData['data']);
        $pembayaran->refresh();
        $pendaftar->refresh();

        $isPaid = ($pembayaran->status_transaksi === 'settlement' && $pendaftar->status_daftar_ulang === 'Selesai');

        return response()->json([
            'success' => true,
            'configured' => true,
            'is_paid' => $isPaid,
            'status' => $pembayaran->status_transaksi,
            'message' => $isPaid ? 'Pembayaran daftar ulang berhasil diverifikasi LUNAS!' : 'Status: ' . strtoupper($pembayaran->status_transaksi),
        ]);
    }

    public function pengaturanAkun()
    {
        $user = Auth::user();
        $pendaftar = $this->getUserPendaftar();
        return view('siswa.pengaturan', compact('user', 'pendaftar'));
    }

    public function hapusBerkas($id)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return back()->with('error', 'Data pendaftar tidak ditemukan.');
        }

        if ($pendaftar->is_locked) {
            return back()->with('error', 'Formulir telah difinalisasi, berkas tidak dapat dihapus.');
        }

        $berkas = BerkasPendaftar::where('id_berkas', $id)
            ->where('id_pendaftar', $pendaftar->id_pendaftar)
            ->firstOrFail();

        $jenis = $berkas->jenis_berkas;

        if (Storage::disk('local')->exists($berkas->path_file)) {
            Storage::disk('local')->delete($berkas->path_file);
        }
        if (Storage::disk('public')->exists($berkas->path_file)) {
            Storage::disk('public')->delete($berkas->path_file);
        }

        $berkas->delete();

        LogAktivitas::log(
            'HAPUS_BERKAS',
            'Siswa menghapus berkas: ' . strtoupper($jenis),
            'berkas_pendaftar',
            $pendaftar->id_pendaftar,
            ['jenis_berkas' => $jenis],
            null,
            'SUCCESS',
            $pendaftar->id_user,
            null,
            'calon_siswa'
        );

        return back()->with('success', 'Berkas ' . strtoupper($jenis) . ' berhasil dihapus.');
    }

    public function simpanPengaturanAkun(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'hp_siswa' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $passwordChanged = false;
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
            $passwordChanged = true;
            LogAktivitas::log(
                'UBAH_PASSWORD',
                'Calon Siswa berhasil memperbarui kata sandi akun',
                'user',
                $user->id_user,
                null,
                null,
                'SUCCESS',
                $user->id_user,
                null,
                'calon_siswa'
            );
        }

        $pendaftar = $this->getUserPendaftar();
        if ($pendaftar && $request->filled('hp_siswa')) {
            $oldHp = $pendaftar->hp_siswa;
            $pendaftar->update(['hp_siswa' => $request->hp_siswa]);
            LogAktivitas::log(
                'UBAH_PROFIL',
                'Calon Siswa memperbarui nomor handphone akun',
                'pendaftar',
                $pendaftar->id_pendaftar,
                ['hp_siswa' => $oldHp],
                ['hp_siswa' => $request->hp_siswa],
                'SUCCESS',
                $user->id_user,
                null,
                'calon_siswa'
            );
        } elseif (!$passwordChanged) {
            LogAktivitas::log(
                'UBAH_PROFIL',
                'Calon Siswa memperbarui data pengaturan akun',
                'user',
                $user->id_user,
                null,
                null,
                'SUCCESS',
                $user->id_user,
                null,
                'calon_siswa'
            );
        }

        return back()->with('success', 'Pengaturan akun Anda berhasil diperbarui.');
    }

    // === CETAK DOKUMEN RESMI (PDF / PRINTABLE) ===

    public function cetakFormulir()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return redirect()->route('siswa.dashboard')->with('error', 'Data pendaftaran belum ada.');
        }

        LogAktivitas::log(
            'UNDUH_BUKTI_DAFTAR',
            'Siswa mengunduh/mencetak formulir pendaftaran: ' . $pendaftar->no_pendaftaran,
            'pendaftar',
            $pendaftar->id_pendaftar,
            null,
            null,
            'SUCCESS',
            $pendaftar->id_user,
            null,
            'calon_siswa'
        );

        $pengaturan = PengaturanSekolah::getPengaturan();
        return view('siswa.cetak_formulir', compact('pendaftar', 'pengaturan'));
    }

    public function jadwalSiswa()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar) {
            return redirect()->route('siswa.formulir')->with('info', 'Silakan lengkapi formulir pendaftaran terlebih dahulu.');
        }

        // Calon siswa HANYA dapat melihat jadwal miliknya sendiri
        $penempatan = \App\Models\PenempatanPeserta::with(['jadwal', 'ruang'])
            ->where('id_pendaftar', $pendaftar->id_pendaftar)
            ->get();

        $pengaturan = PengaturanSekolah::getPengaturan();

        return view('siswa.jadwal', compact('pendaftar', 'penempatan', 'pengaturan'));
    }

    public function cetakKartuUjian()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar || $pendaftar->status_pembayaran !== 'Paid') {
            return redirect()->route('siswa.dashboard')->with('error', 'Kartu ujian hanya dapat dicetak setelah biaya pendaftaran Lunas.');
        }

        LogAktivitas::log(
            'UNDUH_KARTU_UJIAN',
            'Siswa mengunduh/mencetak kartu peserta ujian: ' . $pendaftar->no_pendaftaran,
            'pendaftar',
            $pendaftar->id_pendaftar,
            null,
            null,
            'SUCCESS',
            $pendaftar->id_user,
            null,
            'calon_siswa'
        );

        $pengaturan = PengaturanSekolah::getPengaturan();
        $penempatan = \App\Models\PenempatanPeserta::with(['jadwal', 'ruang'])
            ->where('id_pendaftar', $pendaftar->id_pendaftar)
            ->first();

        return view('siswa.cetak_kartu_ujian', compact('pendaftar', 'pengaturan', 'penempatan'));
    }

    public function cetakKwitansi()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar || !$pendaftar->pembayaran || $pendaftar->pembayaran->status_transaksi !== 'settlement') {
            return redirect()->route('siswa.dashboard')->with('error', 'Kwitansi hanya tersedia untuk pembayaran yang telah Lunas.');
        }

        LogAktivitas::log(
            'UNDUH_KWITANSI',
            'Siswa mengunduh/mencetak kwitansi pembayaran: ' . $pendaftar->no_pendaftaran,
            'pembayaran',
            $pendaftar->pembayaran->id_pembayaran,
            null,
            null,
            'SUCCESS',
            $pendaftar->id_user,
            null,
            'calon_siswa'
        );

        $pengaturan = PengaturanSekolah::getPengaturan();
        return view('siswa.cetak_kwitansi', compact('pendaftar', 'pengaturan'));
    }

    public function unduhSKL()
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar || !$pendaftar->terima) {
            return redirect()->route('siswa.dashboard')->with('error', 'Surat Keterangan Lulus hanya tersedia bagi calon siswa yang telah dinyatakan Diterima.');
        }

        LogAktivitas::log(
            'UNDUH_SKL',
            'Siswa mengunduh/mencetak surat keterangan lulus (SKL): ' . $pendaftar->no_pendaftaran,
            'terima',
            $pendaftar->terima->id_terima,
            null,
            null,
            'SUCCESS',
            $pendaftar->id_user,
            null,
            'calon_siswa'
        );

        $pengaturan = PengaturanSekolah::getPengaturan();
        return view('siswa.cetak_skl', compact('pendaftar', 'pengaturan'));
    }

    public function konfirmasiDaftarUlang(Request $request)
    {
        $pendaftar = $this->getUserPendaftar();
        if (!$pendaftar || $pendaftar->status_kelulusan !== 'Diterima') {
            return back()->with('error', 'Konfirmasi daftar ulang hanya untuk siswa yang diterima.');
        }

        $pendaftar->update(['status_daftar_ulang' => 'Selesai']);
        LogAktivitas::log('UPDATE', 'Siswa mengonfirmasi pendaftaran ulang: ' . $pendaftar->nama, 'pendaftar', $pendaftar->id_pendaftar);

        return back()->with('success', 'Selamat! Anda telah resmi mengonfirmasi pendaftaran ulang sebagai Siswa Baru SMK Islam Bina Khalifah Bangsa.');
    }

    /**
     * Halaman Riwayat Aktivitas Portal Calon Siswa (Tahap 2).
     * Calon Siswa HANYA dapat melihat riwayat aktivitas milik akunnya sendiri.
     */
    public function riwayatAktivitas(Request $request)
    {
        $user = Auth::user();

        // Query khusus riwayat siswa yang bersangkutan
        $query = LogAktivitas::where('id_user', $user->id_user)
            ->where(function ($q) {
                // Tampilkan aktivitas dari sisi calon_siswa ATAU aktivitas sistem/admin yang berimbas ke akun siswa (verifikasi berkas, unlock formulir, kelulusan, dsb)
                $q->where('role', 'calon_siswa')
                  ->orWhereIn('aksi', [
                      'VERIFIKASI', 'VERIFIKASI_BERKAS', 'UNLOCK', 'PENGESAHAN_SKL',
                      'UPDATE', 'BAYAR_TUNAI', 'KONFIRMASI_BAYAR', 'LOGIN_GAGAL'
                  ]);
            });

        // 1. Filter Pencarian Teks Bebas
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('deskripsi', 'LIKE', "%{$q}%")
                    ->orWhere('aksi', 'LIKE', "%{$q}%")
                    ->orWhere('tabel_terkait', 'LIKE', "%{$q}%");
            });
        }

        // 2. Filter Berdasarkan Jenis / Kategori Aktivitas
        if ($request->filled('aksi')) {
            $query->where('aksi', $request->aksi);
        }

        // 3. Filter Berdasarkan Status Aktivitas
        if ($request->filled('status')) {
            $query->where('status', strtoupper($request->status));
        }

        // 4. Filter Berdasarkan Tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->tanggal);
        }

        $logs = $query->orderBy('id_log', 'desc')->paginate(15)->withQueryString();

        // Daftar aksi unik milik siswa ini untuk dropdown filter
        $daftarAksi = LogAktivitas::where('id_user', $user->id_user)
            ->distinct()
            ->pluck('aksi')
            ->filter()
            ->values();

        $stats = [
            'total' => LogAktivitas::where('id_user', $user->id_user)->count(),
            'login_terakhir' => LogAktivitas::where('id_user', $user->id_user)
                ->where('aksi', 'LOGIN')
                ->latest('created_at')
                ->value('created_at'),
            'total_unduhan' => LogAktivitas::where('id_user', $user->id_user)
                ->where('aksi', 'LIKE', 'UNDUH_%')
                ->count(),
        ];

        return view('siswa.riwayat_aktivitas', compact('logs', 'daftarAksi', 'stats'));
    }

    /**
     * Halaman Bantuan dan Pusat Informasi / FAQ Calon Siswa (Section 4.A.12).
     */
    public function bantuan()
    {
        $pengaturan = PengaturanSekolah::getPengaturan();
        $user = auth()->user();
        $pendaftar = $user ? $user->pendaftar : null;

        return view('siswa.bantuan', compact('pengaturan', 'user', 'pendaftar'));
    }
}

