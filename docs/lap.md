# Laporan Keamanan — Enkripsi Data Anonim

**Tanggal:** 2026-04-09  
**Scope:** Sistem enkripsi identitas pelapor anonim (`pelapors.encrypted_identity`)

---

# Laporan Audit Validasi Form Buat Laporan

**Tanggal:** 2026-04-09  
**File utama:** `app/Livewire/BuatLaporanWizard.php`  
**View:** `resources/views/livewire/buat-laporan-wizard.blade.php`

---

## Ringkasan Validasi

| # | Temuan | Lokasi | Severity |
|---|--------|--------|----------|
| 1 | `when_date` tidak dibatasi — tanggal masa depan diizinkan | Step 3 | **Medium** |
| 2 | Jumlah file tidak dibatasi di form web | Step 4 | **Medium** |
| 3 | Inkonsistensi field wajib antara form web dan API | Step 3 | **Medium** |
| 4 | Inkonsistensi `max` pada `identitas_terlapor` | Step 2 | **Low** |
| 5 | Tidak ada panjang minimum pada field teks penting | Step 1, 2, 3 | **Low** |
| 6 | Format nomor HP tidak divalidasi | Step 1 | **Low** |
| 7 | Error message `submit()` mengekspos pesan internal | Submit | **Low** |
| 8 | Validasi file: form web lebih lemah dari API | Step 4 | **Low** |

---

## Temuan V1 — `when_date` Tidak Dibatasi, Tanggal Masa Depan Diizinkan

**Severity:** Medium  
**File:** `app/Livewire/BuatLaporanWizard.php:90`

### Kondisi Saat Ini

```php
'when_date' => 'nullable|date',
```

Aturan `date` hanya memverifikasi format, tidak membatasi nilainya. Pelapor bisa mengisi tanggal kejadian jauh di masa depan (misal: tahun 2099), yang secara logika tidak masuk akal untuk sebuah laporan.

### Rekomendasi

```php
'when_date' => 'nullable|date|before_or_equal:today',
```

Tambahkan pesan validasi:
```php
'when_date.before_or_equal' => 'Tanggal kejadian tidak boleh di masa depan',
```

---

## Temuan V2 — Jumlah File Tidak Dibatasi di Form Web

**Severity:** Medium  
**File:** `app/Livewire/BuatLaporanWizard.php:97`

### Kondisi Saat Ini

```php
// Form web — tidak ada batas jumlah file
'bukti_files.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,webp',

// API (AduanController.php:55) — ada batas
'file_bukti' => 'nullable|array|max:5',
```

Form web tidak membatasi jumlah file yang bisa diupload. Pengguna bisa melampirkan puluhan file sekaligus, berpotensi membebani server.

### Rekomendasi

```php
'bukti_files'   => 'nullable|array|max:5',
'bukti_files.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,webp',
```

Tambahkan pesan validasi:
```php
'bukti_files.max' => 'Maksimal 5 file bukti',
```

---

## Temuan V3 — Inkonsistensi Field Wajib Antara Form Web dan API

**Severity:** Medium  
**File:** `app/Livewire/BuatLaporanWizard.php:87–94` vs `app/Http/Controllers/Api/AduanController.php:39–51`

### Kondisi Saat Ini

| Field | Form Web | API |
|-------|----------|-----|
| `who` | `nullable` | `required` |
| `where_location` | `nullable` | `required` |
| `lokasi_kejadian` | `nullable` | `required` |

Data yang masuk melalui form web bisa memiliki field-field ini kosong, sementara data dari API selalu terisi. Inkonsistensi data di database tergantung channel masuknya laporan.

### Rekomendasi

Tentukan standar bisnis, lalu seragamkan di keduanya. Pertimbangkan mengekstrak aturan validasi ke `FormRequest` agar satu sumber kebenaran.

---

## Temuan V4 — Inkonsistensi `max` pada `identitas_terlapor`

**Severity:** Low  
**File:** `app/Livewire/BuatLaporanWizard.php:85` vs `app/Http/Controllers/Api/AduanController.php:43`

### Kondisi Saat Ini

```php
// Form web
'identitas_terlapor' => 'required|string|max:1000',

// API
'identitas_terlapor' => 'required|string|max:500',
```

Form web memperbolehkan input hingga 1000 karakter, sementara API membatasi 500. Kolom database hanya satu.

### Rekomendasi

Seragamkan ke `max:500` (batasan yang lebih ketat).

---

## Temuan V5 — Tidak Ada Panjang Minimum pada Field Teks Penting

**Severity:** Low  
**File:** `app/Livewire/BuatLaporanWizard.php:77, 85, 88`

### Kondisi Saat Ini

```php
'nama'               => 'required|string|max:255',   // min tidak ada
'identitas_terlapor' => 'required|string|max:1000',  // min tidak ada
'what'               => 'required|string|max:5000',  // min tidak ada
```

Field dapat diisi satu karakter dan lolos validasi. Laporan dengan `what = "a"` akan masuk ke database.

### Rekomendasi

```php
'nama'               => 'required|string|min:3|max:255',
'identitas_terlapor' => 'required|string|min:10|max:500',
'what'               => 'required|string|min:20|max:5000',
```

---

## Temuan V6 — Format Nomor HP Tidak Divalidasi

**Severity:** Low  
**File:** `app/Livewire/BuatLaporanWizard.php:78`

### Kondisi Saat Ini

```php
'phone' => 'required|digits_between:8,15',
```

`digits_between` hanya memastikan input berupa angka 8–15 digit. Nomor seperti `12345678` (tidak diawali `0` atau `+62`) akan lolos.

### Rekomendasi

```php
'phone' => ['required', 'digits_between:10,15', 'regex:/^(\+62|62|0)[0-9]{8,13}$/'],
```

```php
'phone.regex' => 'Format nomor handphone tidak valid (contoh: 08xxxxxxxxxx)',
```

---

## Temuan V7 — Error Message `submit()` Mengekspos Pesan Internal

**Severity:** Low  
**File:** `app/Livewire/BuatLaporanWizard.php:244`

### Kondisi Saat Ini

```php
} catch (\Exception $e) {
    DB::rollBack();
    session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
}
```

Pesan exception asli ditampilkan langsung ke pengguna. Di production bisa mengekspos detail teknis (nama tabel, query, path file).

### Rekomendasi

```php
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('BuatLaporanWizard submit failed', ['error' => $e->getMessage()]);
    session()->flash('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
}
```

---

## Temuan V8 — Validasi File Form Web Lebih Lemah dari API

**Severity:** Low  
**File:** `app/Livewire/BuatLaporanWizard.php:97` vs `app/Http/Controllers/Api/AduanController.php:128–131`

### Kondisi Saat Ini

Form web menggunakan `mimes` yang berbasis **ekstensi file** (mudah dipalsukan). API menggunakan `FileValidationService` yang membaca **konten MIME type** aktual.

```php
// Form web — berbasis ekstensi
'bukti_files.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,webp',

// API — berbasis konten (lebih aman)
$fileInfo = $this->fileValidator->validate($file, 'file_bukti');
```

### Rekomendasi

Gunakan `mimetypes` sebagai pengganti `mimes`, atau terapkan `FileValidationService` yang sama di `submit()`:

```php
'bukti_files.*' => 'file|max:10240|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png,image/webp',
```

---

---

## Ringkasan

| # | Temuan | Severity |
|---|--------|----------|
| 1 | Role middleware tidak diterapkan di panel Inspektur | **High** |
| 2 | Login panel tidak memiliki rate limiting | **Medium** |
| 3 | Email pelapor tercatat plaintext di log | **Medium** |
| 4 | Email pelapor anonim tersimpan plaintext di database | **Low** |

---

## Temuan 1 — Role Middleware Tidak Diterapkan di Panel Inspektur

**Severity:** High  
**File:** `app/Providers/Filament/InspekturPanelProvider.php:57`

### Kondisi Saat Ini

`authMiddleware` panel Inspektur hanya berisi `Authenticate::class` — artinya hanya memverifikasi bahwa user sudah login, tanpa memeriksa apakah role-nya adalah Inspektur.

```php
// InspekturPanelProvider.php
->authMiddleware([
    Authenticate::class, // hanya cek login, tidak cek role
])
```

Pemeriksaan role via `canAccessPanel()` hanya terjadi saat proses **login** (`app/Filament/Pages/Auth/Login.php:94`). Jika user Verifikator yang sudah memiliki session aktif mengakses URL `/inspektur/aduans/{id}` secara langsung, tidak ada middleware yang memblokir.

### Dampak

User dengan role Verifikator dapat mengakses halaman `ViewAduan` panel Inspektur dan menggunakan tombol **Dekripsi** untuk melihat identitas asli pelapor anonim, cukup dengan memasukkan password akun mereka sendiri.

### Rekomendasi

Tambahkan custom middleware yang memvalidasi role pada `InspekturPanelProvider`:

```php
// app/Http/Middleware/EnsureInspekturRole.php
public function handle(Request $request, Closure $next): Response
{
    if (!auth()->user()?->isInspektur() && !auth()->user()?->isAdmin()) {
        abort(403);
    }
    return $next($request);
}

// InspekturPanelProvider.php
->authMiddleware([
    Authenticate::class,
    EnsureInspekturRole::class,
])
```

---

## Temuan 2 — Login Panel Tidak Memiliki Rate Limiting

**Severity:** Medium  
**File:** `app/Filament/Pages/Auth/Login.php`

### Kondisi Saat Ini

Login halaman publik (`app/Livewire/Auth/Login.php`) memiliki rate limiter (5 percobaan per 5 menit):

```php
// Livewire/Auth/Login.php — ADA rate limiting
$throttleKey = strtolower($this->email) . '|' . request()->ip();
if (RateLimiter::tooManyAttempts($throttleKey, 5)) { ... }
```

Namun login panel admin/inspektur/verifikator (`Filament/Pages/Auth/Login.php`) **tidak memiliki** mekanisme yang sama, sehingga bisa di-brute force tanpa batas.

### Dampak

Penyerang dapat mencoba kombinasi password secara terus-menerus terhadap akun internal (admin, verifikator, inspektur) tanpa terkena blokir.

### Rekomendasi

Tambahkan rate limiting yang sama ke `Filament/Pages/Auth/Login.php`:

```php
use Illuminate\Support\Facades\RateLimiter;

public function authenticate(): ?LoginResponse
{
    $throttleKey = strtolower($data['email']) . '|' . request()->ip();

    if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
        $seconds = RateLimiter::availableIn($throttleKey);
        throw ValidationException::withMessages([
            'data.email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    // ... proses login ...

    RateLimiter::hit($throttleKey, 300);
}
```

---

## Temuan 3 — Email Pelapor Tercatat Plaintext di Log

**Severity:** Medium  
**File:** `app/Jobs/SendReportSubmittedEmail.php:50`, `app/Jobs/SendStatusUpdateEmail.php:58`

### Kondisi Saat Ini

Kedua job email mencatat alamat email pelapor secara plaintext ke dalam log aplikasi:

```php
// SendReportSubmittedEmail.php
Log::info('Report submitted email sent', [
    'email' => $this->email, // plaintext
]);

// SendStatusUpdateEmail.php
Log::info('Status update email sent', [
    'email' => $pelapor->email, // plaintext
]);
```

File log tersimpan di `storage/logs/laravel.log` dan umumnya tidak dienkripsi.

### Dampak

Siapapun yang mendapat akses ke file log (via misconfigured web server, directory traversal, atau akses server) dapat mengumpulkan daftar email seluruh pelapor, termasuk pelapor yang melaporkan secara anonim.

### Rekomendasi

Mask email sebelum dicatat ke log:

```php
// Contoh helper mask: user@example.com → us***@example.com
$maskedEmail = preg_replace('/(?<=.{2}).(?=.*@)/', '*', $email);

Log::info('Report submitted email sent', [
    'email' => $maskedEmail,
]);
```

---

## Temuan 4 — Email Pelapor Anonim Tersimpan Plaintext di Database

**Severity:** Low  
**File:** `database/migrations/2026_01_19_000004_create_pelapors_table.php:18`, `app/Models/Pelapor.php:22`

### Kondisi Saat Ini

Kolom `email` di tabel `pelapors` disimpan plaintext, sementara `nama` dan `phone` pelapor anonim dienkripsi ke dalam `encrypted_identity`:

```php
// Pelapor.php — encryptAndStoreIdentity()
$this->encrypted_identity = Crypt::encryptString(json_encode([
    'nama' => $nama,
    'phone' => $phone,
    // email tidak ikut dienkripsi
]));
$this->nama  = 'Anonim';
$this->phone = '**********';
$this->email // tetap plaintext di kolom terpisah
```

### Dampak

Jika database bocor (dump, backup tidak aman, dsb.), email pelapor anonim dapat langsung dibaca meskipun identitas nama dan nomor telepon sudah terenkripsi. Email dapat digunakan untuk melacak atau mengidentifikasi pelapor.

### Rekomendasi

Enkripsi kolom email menggunakan cast `encrypted` bawaan Laravel, atau ikutkan email ke dalam `encrypted_identity` dan hapus dari kolom terpisah untuk pelapor anonim:

```php
// Opsi: gunakan Laravel encrypted cast
protected $casts = [
    'email' => 'encrypted',
];
```

> **Catatan:** Jika email dienkripsi, fitur pencarian by email (`Pelapor::where('email', ...)`) tidak akan berfungsi langsung dan perlu penyesuaian.

---

## Catatan Tambahan

Mekanisme enkripsi utama (`Crypt::encryptString`) bergantung sepenuhnya pada `APP_KEY` di file `.env`. Jika `APP_KEY` bocor, seluruh `encrypted_identity` dapat didekripsi secara langsung. Pastikan:

- File `.env` terdaftar di `.gitignore` dan tidak pernah di-commit
- `APP_KEY` dirotasi secara berkala
- Akses ke server dan backup database dibatasi ketat

---

# Laporan Audit Keamanan & Operasional API

**Tanggal:** 2026-04-09  
**File:** `routes/api.php`, `app/Http/Middleware/ApiKeyAuth.php`, `app/Http/Controllers/Api/AduanController.php`  
**Tool:** `docs/api-tester.html`

---

## Ringkasan

| # | Temuan | Severity |
|---|--------|----------|
| 1 | API key hardcoded di source code dan dokumentasi | **High** |
| 2 | Validasi API key rentan timing attack | **High** |
| 3 | Tidak ada rate limiting di semua endpoint API | **Medium** |
| 4 | Error detail `$e->getMessage()` bocor ke client | **Medium** |
| 5 | Endpoint publik tanpa throttle | **Medium** |
| 6 | MIME type bocor di pesan error FileValidationService | **Low** |
| 7 | Tidak ada log saat autentikasi gagal | **Low** |

---

## Temuan A1 — API Key Hardcoded di Source Code dan Dokumentasi

**Severity:** High  
**File:** `app/Http/Middleware/ApiKeyAuth.php:14`, `docs/API_DOCUMENTATION.md:22`

### Kondisi Saat Ini

```php
// ApiKeyAuth.php
protected array $validApiKeys = [
    'sk_live_wbs_superapps_2026', // hardcoded di source
];
```

API key production tersimpan langsung di source code. Siapapun yang bisa membaca repository atau dokumentasi memiliki API key yang valid. Key ini juga dicetak secara eksplisit di `API_DOCUMENTATION.md`.

### Rekomendasi

```php
// .env
SUPERAPPS_API_KEY=sk_live_...

// config/services.php
'superapps' => ['api_key' => env('SUPERAPPS_API_KEY')],

// ApiKeyAuth.php — hapus $validApiKeys, hanya gunakan config
$validKey = config('services.superapps.api_key');
if (!$validKey || !hash_equals($validKey, $apiKey)) {
    return response()->json([...], 401);
}
```

Rotasi key sekarang jika sudah pernah di-commit ke git.

---

## Temuan A2 — Validasi API Key Rentan Timing Attack

**Severity:** High  
**File:** `app/Http/Middleware/ApiKeyAuth.php:36`

### Kondisi Saat Ini

```php
if (!in_array($apiKey, $validKeys)) { ... }
```

`in_array()` menggunakan perbandingan string biasa yang berhenti segera setelah menemukan ketidakcocokan. Penyerang bisa mengukur perbedaan waktu respons untuk menyimpulkan karakter mana yang benar, memungkinkan brute force karakter per karakter.

### Rekomendasi

```php
// Gunakan hash_equals() yang constant-time
$valid = false;
foreach ($validKeys as $key) {
    if (hash_equals($key, $apiKey)) {
        $valid = true;
        break;
    }
}
if (!$valid) { return response()->json([...], 401); }
```

---

## Temuan A3 — Tidak Ada Rate Limiting di Semua Endpoint API

**Severity:** Medium  
**File:** `routes/api.php`, `bootstrap/app.php`

### Kondisi Saat Ini

Tidak ada `throttle` middleware terdaftar di routes API maupun di `bootstrap/app.php`. Semua endpoint bisa dipanggil tanpa batas:

- `POST /api/aduans/status` — bisa brute force `tracking_password` (hanya 8 karakter random)
- `POST /api/aduans` — bisa spam ratusan laporan palsu ke database
- `GET /api/jenis-aduans` — bisa scraping/flooding tanpa hambatan

### Rekomendasi

```php
// routes/api.php
Route::get('/jenis-aduans', ...)->middleware('throttle:120,1');

Route::middleware(['api.key', 'throttle:60,1'])->group(function () {
    Route::post('/aduans', ...);
    Route::post('/aduans/status', ...)->middleware('throttle:20,1');
});
```

---

## Temuan A4 — Error Detail Bocor ke Client

**Severity:** Medium  
**File:** `app/Http/Controllers/Api/AduanController.php:203`

### Kondisi Saat Ini

```php
return response()->json([
    'success' => false,
    'message' => 'Gagal menyimpan aduan.',
    'error' => config('app.debug') ? $e->getMessage() : null,
], 500);
```

Saat `APP_DEBUG=true` (yang sering terjadi di staging atau misconfigured production), pesan exception asli dikirim ke client, berpotensi mengekspos nama tabel, path file, atau detail query database.

### Rekomendasi

Hapus conditional `config('app.debug')` sepenuhnya. Log error di server, kirim pesan generik ke client:

```php
Log::error('API aduan creation failed', ['error' => $e->getMessage()]);
return response()->json(['success' => false, 'message' => 'Gagal menyimpan aduan.'], 500);
```

---

## Temuan A5 — Endpoint Publik Tanpa Throttle

**Severity:** Medium  
**File:** `routes/api.php:17`

### Kondisi Saat Ini

```php
Route::get('/jenis-aduans', [AduanController::class, 'jenisAduans'])
    ->name('api.jenis-aduans');
// tidak ada middleware apapun
```

Meski data diambil dari cache (1 jam), overhead koneksi dan framework tetap ada. Endpoint ini bisa dieksploitasi sebagai titik DDoS sederhana.

### Rekomendasi

```php
Route::get('/jenis-aduans', ...)->middleware('throttle:120,1');
```

---

## Temuan A6 — MIME Type Bocor di Pesan Error

**Severity:** Low  
**File:** `app/Services/FileValidationService.php:79`

### Kondisi Saat Ini

```php
throw ValidationException::withMessages([
    $fieldName => 'Tipe file tidak diizinkan. MIME type: ' . $realMimeType,
]);
```

Pesan error menyebutkan MIME type aktual file yang ditolak. Memberikan informasi kepada penyerang tentang jenis file apa yang diblokir dan memudahkan bypass.

### Rekomendasi

```php
throw ValidationException::withMessages([
    $fieldName => 'Tipe file tidak diizinkan.',
]);
```

---

## Temuan A7 — Tidak Ada Log Saat Autentikasi Gagal

**Severity:** Low  
**File:** `app/Http/Middleware/ApiKeyAuth.php`

### Kondisi Saat Ini

Request dengan key yang salah langsung ditolak dengan 401 tanpa mencatat ke log. Tidak ada cara mendeteksi percobaan brute force atau penyalahgunaan key.

### Rekomendasi

```php
Log::warning('API authentication failed', [
    'ip'         => $request->ip(),
    'user_agent' => $request->userAgent(),
    'key_prefix' => $apiKey ? substr($apiKey, 0, 8) . '...' : null,
]);
```

---

## Yang Sudah Baik

| Aspek | Keterangan |
|-------|------------|
| SQL Injection | Semua query via Eloquent ORM |
| Input Validation | `$request->validate()` lengkap di semua endpoint |
| File Security | `FileValidationService` membaca MIME type dari konten (bukan ekstensi) |
| Database Transaction | `DB::beginTransaction()` + rollback saat error |
| Password Hashing | `tracking_password` di-hash dengan bcrypt |
| File Naming | Nama file unik dengan timestamp + random string |
