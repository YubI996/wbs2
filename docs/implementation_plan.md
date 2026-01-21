# Implementation Plan: WBS Application Rebuild - Laravel 12

## Overview

Rebuild WBS (Whistle Blowing System) for **Kota Bontang** using **Laravel 12 + Filament v5 + Livewire** with modern UI, WCAG AA compliance, and enhanced security features.

**Portal URL:** wbs.bontangkota.go.id  
**Deadline:** 26 January 2026 (1 week)

---

## Confirmed Decisions

| Decision | Choice |
|----------|--------|
| **Project Location** | `d:\apps\laragon\www\wbs-v2` |
| **UI Framework** | Filament v5 + Livewire (customized modern look) |
| **Database** | New database `wbs_v2` (clean start) |
| **PHP Version** | PHP 8.4.17 (latest stable) |
| **Language** | Bahasa Indonesia |
| **Timezone** | UTC+8 (Asia/Makassar) |
| **Accessibility** | WCAG AA compliant |
| **Push Notifications** | OneSignal (yes) |
| **Captcha** | Google reCAPTCHA v3 |
| **Architecture** | SPA with Livewire |

---

## Multi-Channel Reporting (dari Notulen Rapat)

Sistem WBS menyediakan beberapa saluran pelaporan:

| Kanal | Deskripsi | Implementasi |
|-------|-----------|--------------|
| **Website Form** | Form lengkap di wbs.bontangkota.go.id | ✅ Primary (built-in) |
| **WhatsApp** | Link/button ke WhatsApp pengelola | 🔗 External link (dummy) |
| **Instagram DM** | DM ke @itdabontang | 🔗 External link (dummy) |
| **SP4N LAPOR!** | Kanal nasional | 🔗 External link (dummy) |

---

## Jenis Aduan (Kategori Laporan)

| Slug | Nama Kategori |
|------|---------------|
| 1 | Pelanggaran Disiplin Pegawai |
| 2 | Penyalahgunaan Wewenang |
| 3 | Mal Administrasi dan Pemerasan/Penganiayaan |
| 4 | Perlakuan Amoral/Perselingkuhan |
| 5 | Korupsi |
| 6 | Pelanggaran dalam Pengadaan Barang dan Jasa |
| 7 | Pungutan Liar/Percaloan/Suap |
| 8 | Narkoba |

---

## Email & Queue Configuration

### SMTP Setup
```env
# .env configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=wbs@bontangkota.go.id
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=wbs@bontangkota.go.id
MAIL_FROM_NAME="WBS Kota Bontang"
```

### Queue Configuration
```env
# Use database queue for reliability
QUEUE_CONNECTION=database
```

### Queue Setup Commands
```bash
# Create jobs table
php artisan queue:table
php artisan migrate

# Run queue worker (development)
php artisan queue:work

# Run queue worker (production - supervisor recommended)
php artisan queue:work --daemon --tries=3 --timeout=90
```

### Email Notifications
| Event | Recipient | Content |
|-------|-----------|---------|
| Laporan berhasil dikirim | Pelapor | Nomor registrasi + password |
| Status berubah | Pelapor | Update status terbaru |
| Laporan baru masuk | Admin/Verifikator | Notifikasi laporan baru |

### Queue Jobs
```php
// Jobs to be created
App\Jobs\SendReportSubmittedEmail::class
App\Jobs\SendStatusUpdateEmail::class
App\Jobs\SendNewReportNotification::class
```

---

## Public Report Submission Flow (dari Notulen Rapat)

> **Catatan:** Pelapor tidak memilih saluran pelaporan di wizard. Form website adalah default.
> Saluran lain (WhatsApp, Instagram, SP4N) hanya ditampilkan sebagai link di landing page.

### Step 1: Identitas Pelapor
| Field | Wajib | Keterangan |
|-------|-------|------------|
| Nama Lengkap | ✅ Ya | Nama pelapor |
| No Handphone | ✅ Ya | Untuk verifikasi & komunikasi |
| Email | ❌ Opsional | Hanya jika ingin notifikasi email |

### Step 2: Opsi Kerahasiaan (Anonimitas)
- **Checkbox "Anonim"**: Jika dicentang, identitas pelapor akan **terenkripsi**
- Identitas tidak ditampilkan dalam proses penanganan
- Jika tidak dicentang = identitas terbuka

### Step 3: Notifikasi Email (Opsional)
- Checkbox "Terima notifikasi via email"
- Jika ya → input email wajib diisi
- Jika tidak → lanjut tanpa email

### Step 4: Substansi Laporan
| Field | Keterangan |
|-------|------------|
| **Kategori Laporan** | Dropdown dari jenis_aduans |
| **Identitas Terlapor** | Nama/jabatan pihak yang dilaporkan |
| **Kronologis (5W+1H)** | What, Who, When, Where, Why, How |

### Step 5: Upload Bukti Pendukung
| Jenis Bukti | Format |
|-------------|--------|
| Dokumen | PDF, DOC, DOCX |
| Foto/Gambar | JPG, PNG, WEBP |
| Lokasi | Text/koordinat (opsional) |

### Step 6: Preview & Konfirmasi
- Tampilkan ringkasan semua data yang diinput
- Pelapor dapat edit sebelum submit
- Tombol **"Kirim Laporan"**

### Step 7: Nomor Registrasi & Password
Setelah submit berhasil, sistem generate:
- **Nomor Registrasi**: Format `WBS-YYYY-NNNNN` (contoh: WBS-2026-00001)
- **Password**: Random 8 karakter (untuk tracking)
- Ditampilkan di layar + dikirim via SMS/Email (jika diisi)

### Step 8: Public Tracking (Tanpa Login)
- Menu **"Cek Status Laporan"** di halaman utama
- Input: Nomor Registrasi + Password
- Tampilkan: Status, timeline, komentar pengelola

---

## Technology Stack

| Component | Technology |
|-----------|------------|
| **PHP** | 8.4.17 |
| **Framework** | Laravel 12.x |
| **Admin Panel** | Filament v5 |
| **Reactivity** | Livewire 3 |
| **Styling** | TailwindCSS v4 |
| **Charts** | Filament Charts (ApexCharts) |
| **Excel** | maatwebsite/excel ^3.1 |
| **Captcha** | Google reCAPTCHA v3 |
| **Notifications** | OneSignal + Laravel Notifications |
| **File Validation** | MIME type detection (finfo) |
| **Encryption** | Laravel Crypt (untuk data anonim) |

---

## Database Schema

### Users Table (Pelapor Internal/ASN)
```
users
├── id (bigint, PK)
├── name (string)
├── email (string, unique)
├── password (string)
├── nip (string, nullable) -- untuk ASN
├── nik (string, nullable) -- untuk masyarakat
├── phone (string, nullable)
├── role_id (int, FK)
├── email_verified_at (timestamp)
├── created_at, updated_at
```

### Pelapors Table (Pelapor Publik - BARU)
```
pelapors
├── id (bigint, PK)
├── nama (string) -- nama pelapor
├── phone (string) -- no HP (wajib)
├── email (string, nullable) -- opsional
├── is_anonim (boolean, default: false)
├── encrypted_identity (text, nullable) -- jika anonim
├── notify_email (boolean, default: false)
├── created_at, updated_at
```

### Aduans Table (Laporan)
```
aduans
├── id (bigint, PK)
├── nomor_registrasi (string, unique) -- WBS-YYYY-NNNNN
├── tracking_password (string) -- hashed password untuk tracking
├── pelapor_id (bigint, FK, nullable) -- untuk pelapor publik
├── user_id (bigint, FK, nullable) -- untuk pelapor internal
├── jenis_aduan_id (string, FK)
├── kategori (string) -- kategori laporan
├── identitas_terlapor (text) -- nama/jabatan terlapor
├── kronologis (text) -- kronologis 5W+1H
├── what (text, nullable) -- apa yang terjadi
├── who (text, nullable) -- siapa yang terlibat
├── when_date (date, nullable) -- kapan terjadi
├── where_location (text, nullable) -- di mana terjadi
├── why (text, nullable) -- mengapa terjadi
├── how (text, nullable) -- bagaimana terjadi
├── lokasi_kejadian (string, nullable)
├── koordinat (string, nullable) -- lat,lng
├── status (enum)
├── channel (enum: website, whatsapp, instagram, sp4n)
├── created_at, updated_at
```

### Bukti Pendukung Table (BARU)
```
bukti_pendukungs
├── id (bigint, PK)
├── aduan_id (bigint, FK)
├── file_path (string)
├── file_name (string)
├── file_type (enum: dokumen, foto, lainnya)
├── mime_type (string)
├── file_size (int)
├── created_at, updated_at
```

### Status Enum
```php
enum AduanStatus: string
{
    case PENDING = 'pending';           // Baru masuk
    case VERIFIKASI = 'verifikasi';     // Sedang diverifikasi
    case PROSES = 'proses';             // Dalam proses penanganan
    case INVESTIGASI = 'investigasi';   // Dalam investigasi
    case SELESAI = 'selesai';           // Selesai ditangani
    case DITOLAK = 'ditolak';           // Ditolak
}
```

### Channel Enum (BARU)
```php
enum ReportChannel: string
{
    case WEBSITE = 'website';
    case WHATSAPP = 'whatsapp';
    case INSTAGRAM = 'instagram';
    case SP4N = 'sp4n';
}
```

---

## Security Enhancements

### Secure File Upload
```php
// Validate MIME type (prevent fake extensions)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$realMime = $finfo->file($file->getPathname());

if (!in_array($realMime, $allowedMimes)) {
    throw ValidationException::withMessages([
        'file' => 'Tipe file tidak valid (ekstensi palsu terdeteksi)'
    ]);
}
```

### Anonymous Identity Encryption
```php
// Encrypt sensitive data for anonymous reporters
if ($request->is_anonim) {
    $encryptedData = Crypt::encryptString(json_encode([
        'nama' => $request->nama,
        'phone' => $request->phone,
    ]));
    
    $pelapor->encrypted_identity = $encryptedData;
    $pelapor->nama = 'Anonim';
    $pelapor->phone = null;
}
```

### Registration Number Generation
```php
// Generate unique registration number
$year = date('Y');
$lastNumber = Aduan::whereYear('created_at', $year)->max('sequence') ?? 0;
$sequence = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
$nomorRegistrasi = "WBS-{$year}-{$sequence}";
```

### Protected Features
- ✅ reCAPTCHA v3 on public forms
- ✅ API Key authentication for SuperApps
- ✅ CSRF protection
- ✅ Rate limiting
- ✅ IDOR prevention (scoped queries)
- ✅ File MIME validation
- ✅ Input sanitization
- ✅ XSS prevention
- ✅ Validation messages in Bahasa Indonesia
- ✅ Anonymous identity encryption

---

## Proposed Phases

### Phase 1: Project Setup
```bash
cd d:\apps\laragon\www
composer create-project laravel/laravel wbs-v2
cd wbs-v2
composer require filament/filament:"^5.0"
php artisan filament:install --panels
```

### Phase 2: Database
| Migration | Description |
|-----------|-------------|
| `create_roles_table` | Role with slug (1-4) |
| `create_jenis_aduans_table` | Complaint categories |
| `create_users_table` | Extended with NIK, NIP, role_id |
| `create_pelapors_table` | Public reporters (BARU) |
| `create_aduans_table` | Complaints with status, channel |
| `create_bukti_pendukungs_table` | Evidence files (BARU) |
| `create_aduan_timelines_table` | Status history (BARU) |

### Phase 3: Models
| Model | Key Features |
|-------|--------------|
| Role | Primary key: slug |
| JenisAduan | Primary key: slug |
| User | NIK/NIP fields, role relationship |
| Pelapor | Anonymous support, encryption (BARU) |
| Aduan | 5W+1H fields, multi-channel, tracking (BARU) |
| BuktiPendukung | File attachments (BARU) |
| AduanTimeline | Status history (BARU) |

### Phase 4: Public Pages (Livewire)
| Component | Function |
|-----------|----------|
| `LandingPage` | Hero, stats, multi-channel buttons |
| `BuatLaporanWizard` | Multi-step form dengan preview |
| `CekStatusLaporan` | Public tracking by nomor + password |
| `StatisticsChart` | Interactive charts |

### Phase 5: Filament Admin Panels
| Panel | URL | Roles |
|-------|-----|-------|
| `/admin` | Admin Panel | Admin (3) |
| `/verifikator` | Verification Panel | Verifikator (2) |
| `/inspektur` | Inspection Panel | Inspektur (1) |
| `/pengadu` | Reporter Panel | Pengadu (4) |

### Phase 6: API Layer
| Endpoint | Description |
|----------|-------------|
| `POST /api/aduans` | Create complaint (SuperApps) |
| `GET /api/aduans/{nomor}` | Get complaint status by nomor registrasi |

### Phase 7: Notifications
- Email notifications on status change
- SMS notification for registration number (optional)
- OneSignal push notifications
- In-app notifications via Filament

---

## File Structure

```
wbs-v2/
├── app/
│   ├── Enums/
│   │   ├── AduanStatus.php
│   │   └── ReportChannel.php
│   ├── Filament/
│   │   ├── Admin/
│   │   ├── Verifikator/
│   │   ├── Inspektur/
│   │   └── Pengadu/
│   ├── Http/Controllers/Api/
│   ├── Livewire/
│   │   ├── LandingPage.php
│   │   ├── BuatLaporanWizard.php      # BARU
│   │   ├── CekStatusLaporan.php       # BARU
│   │   └── StatisticsChart.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Pelapor.php                # BARU
│   │   ├── Aduan.php
│   │   ├── BuktiPendukung.php         # BARU
│   │   └── AduanTimeline.php          # BARU
│   └── Services/
│       ├── FileValidationService.php
│       ├── RegistrationNumberService.php   # BARU
│       ├── AnonymousEncryptionService.php  # BARU
│       └── StatisticsService.php
├── resources/views/
│   ├── livewire/
│   │   ├── landing-page.blade.php
│   │   ├── buat-laporan-wizard.blade.php   # BARU
│   │   └── cek-status-laporan.blade.php    # BARU
│   └── welcome.blade.php
└── routes/
    ├── api.php
    └── web.php
```

---

## Buat Laporan Wizard (Multi-Step Form)

> **Catatan:** Saluran pelaporan lain (WhatsApp, IG, SP4N) ditampilkan di landing page sebagai link eksternal.
> Form ini adalah untuk pelaporan via website (channel = 'website' secara default).

### Step-by-Step UI Flow

```
┌────────────────────────────────────────────────────────────┐
│ STEP 1: Identitas Pelapor                                  │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Nama Lengkap *                                            │
│  ┌─────────────────────────────────────┐                   │
│  │                                     │                   │
│  └─────────────────────────────────────┘                   │
│                                                            │
│  No Handphone *                                            │
│  ┌─────────────────────────────────────┐                   │
│  │                                     │                   │
│  └─────────────────────────────────────┘                   │
│                                                            │
│  ☐ Rahasiakan identitas saya (Anonim)                      │
│                                                            │
│  ☐ Terima notifikasi via email                             │
│    ┌─────────────────────────────────────┐                 │
│    │ Email (wajib jika opsi ini dipilih) │                 │
│    └─────────────────────────────────────┘                 │
│                                                            │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ STEP 2: Substansi Laporan                                  │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  Kategori Laporan *                                        │
│  ┌─────────────────────────────────────┐                   │
│  │ Pilih kategori...               ▼  │                   │
│  └─────────────────────────────────────┘                   │
│                                                            │
│  Identitas Terlapor *                                      │
│  ┌─────────────────────────────────────┐                   │
│  │ Nama/Jabatan pihak yang dilaporkan  │                   │
│  └─────────────────────────────────────┘                   │
│                                                            │
│  Kronologis Kejadian (5W + 1H) *                           │
│  ┌─────────────────────────────────────┐                   │
│  │ Apa yang terjadi? (What)            │                   │
│  │                                     │                   │
│  │ Siapa yang terlibat? (Who)          │                   │
│  │                                     │                   │
│  │ Kapan kejadian? (When)              │                   │
│  │                                     │                   │
│  │ Di mana kejadian? (Where)           │                   │
│  │                                     │                   │
│  │ Mengapa terjadi? (Why)              │                   │
│  │                                     │                   │
│  │ Bagaimana kronologinya? (How)       │                   │
│  └─────────────────────────────────────┘                   │
│                                                            │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ STEP 3: Upload Bukti Pendukung                             │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  ┌─────────────────────────────────────┐                   │
│  │                                     │                   │
│  │     📁 Seret file ke sini           │                   │
│  │     atau klik untuk browse          │                   │
│  │                                     │                   │
│  │     Format: PDF, DOC, JPG, PNG      │                   │
│  │     Maks: 10MB per file             │                   │
│  │                                     │                   │
│  └─────────────────────────────────────┘                   │
│                                                            │
│  Lokasi Kejadian (opsional)                                │
│  ┌─────────────────────────────────────┐                   │
│  │ Alamat atau deskripsi lokasi        │                   │
│  └─────────────────────────────────────┘                   │
│                                                            │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ STEP 4: Preview & Konfirmasi                               │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  📋 RINGKASAN LAPORAN                                      │
│  ─────────────────────────────────────                     │
│  Nama      : [Anonim / Nama pelapor]                       │
│  No HP     : [Tersembunyi jika anonim]                     │
│  Email     : [email atau "-"]                              │
│  Kategori  : [Kategori laporan]                            │
│  Terlapor  : [Identitas terlapor]                          │
│  Kronologis: [Preview text...]                             │
│  Bukti     : 3 file terlampir                              │
│                                                            │
│  ☐ Saya menyatakan bahwa informasi yang                    │
│    saya sampaikan adalah benar                             │
│                                                            │
│  ┌─────────────────┐  ┌─────────────────┐                  │
│  │   ← Kembali     │  │  Kirim Laporan  │                  │
│  └─────────────────┘  └─────────────────┘                  │
│                                                            │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ STEP 5: Sukses                                             │
├────────────────────────────────────────────────────────────┤
│                                                            │
│              ✅ LAPORAN BERHASIL DIKIRIM                   │
│                                                            │
│  ┌─────────────────────────────────────┐                   │
│  │                                     │                   │
│  │   Nomor Registrasi                  │                   │
│  │   WBS-2026-00001                    │                   │
│  │                                     │                   │
│  │   Password Tracking                 │                   │
│  │   ********                          │                   │
│  │                                     │                   │
│  │   📋 Salin  |  📧 Kirim ke Email    │                   │
│  │                                     │                   │
│  └─────────────────────────────────────┘                   │
│                                                            │
│  ⚠️ PENTING: Simpan nomor registrasi dan password         │
│     untuk memantau status laporan Anda                     │
│                                                            │
│  ┌─────────────────────────────────────┐                   │
│  │     Cek Status Laporan →            │                   │
│  └─────────────────────────────────────┘                   │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

---

## WCAG AA Compliance Checklist

- [ ] Color contrast ratio ≥ 4.5:1 (text)
- [ ] Focus indicators visible
- [ ] Keyboard navigable
- [ ] ARIA labels on interactive elements
- [ ] Form error messages linked
- [ ] Skip to content link
- [ ] Responsive text sizing
- [ ] Form validation accessible

---

## Color Palette

```css
/* Primary - Government Blue */
--primary-500: #1e40af;
--primary-600: #1e3a8a;

/* Success */
--success-500: #16a34a;

/* Warning */
--warning-500: #ca8a04;

/* Danger */
--danger-500: #dc2626;

/* Neutral */
--gray-50: #f9fafb;
--gray-900: #111827;
```

---

## Verification Plan

### Automated Tests
```bash
php artisan test --filter=AuthTest
php artisan test --filter=AduanTest
php artisan test --filter=FileUploadSecurityTest
php artisan test --filter=AnonymousEncryptionTest
php artisan test --filter=PublicTrackingTest
```

### Security Tests
- Upload file with fake extension (jpg renamed to pdf)
- Upload executable disguised as image
- SQL injection attempts
- XSS injection attempts
- Decrypt anonymous data (should fail without key)

### Manual Tests
1. Submit laporan sebagai masyarakat (anonim)
2. Submit laporan sebagai masyarakat (identitas terbuka)
3. Track laporan dengan nomor registrasi + password
4. Verifikasi email notification terkirim
5. Complete verification → inspection → resolution flow
6. Test multi-channel (WhatsApp, IG, SP4N links work)
7. Test API with Postman/cURL
8. Test on mobile devices

---

## Timeline

| Day | Phase | Tasks |
|-----|-------|-------|
| 1 | Setup | Laravel 12 + Filament + Database |
| 2 | Core | Models, Migrations, Seeders, Enums |
| 3 | Public | Landing page, BuatLaporanWizard, CekStatusLaporan |
| 4 | Admin | Filament Resources, CRUD |
| 5 | Panels | Role-based panels, workflows |
| 6 | API | SuperApps integration, Notifications |
| 7 | Testing | Security, WCAG, polish |
