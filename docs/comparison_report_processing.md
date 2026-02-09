# Perbandingan Alur Proses dan UI/UX Pemrosesan Laporan
## WBS Lama vs WBS-v2

---

## 📋 Executive Summary

Dokumen ini membandingkan alur proses dan UI/UX pemrosesan laporan antara aplikasi WBS lama dengan WBS-v2 (versi baru). Perbandingan mencakup pengalaman pelapor, proses verifikasi, validasi, dan pengelolaan status laporan.

---

## 🎯 1. PROSES PELAPORAN (Reporter Experience)

### WBS Lama

#### Alur Pengiriman Laporan
1. **Halaman Welcome**
   - Landing page sederhana dengan link ke dokumentasi
   - Tidak ada wizard/form pelaporan publik
   - User harus **login terlebih dahulu** untuk membuat laporan
   
2. **Setelah Login → Halaman Create**
   - URL: `/aduans/create`
   - Form tradisional dengan semua field dalam satu halaman
   - Menggunakan Laravel Collective Forms
   - Pilihan jenis aduan dari dropdown
   - Upload file bukti dengan FilePond
   
3. **Field yang Diminta**
   ```
   - Jenis Aduan (required)
   - Nama Terlapor
   - Jabatan Terlapor  
   - Pangkat Terlapor
   - Instansi Terlapor
   - Unit Terlapor
   - Kota Terlapor
   - File Bukti (single file)
   ```

4. **Submission**
   - Submit langsung ke database
   - Redirect ke index page
   - Flash message: "Aduan saved successfully"
   - **TIDAK ADA** nomor registrasi otomatis
   - **TIDAK ADA** tracking password

#### Kekurangan
- ❌ Tidak ada form publik (harus login)
- ❌ Tidak ada wizard step-by-step
- ❌ Tidak ada progress indicator
- ❌ Form panjang dalam satu halaman (overwhelming)
- ❌ Tidak ada opsi anonim
- ❌ Tidak ada sistem tracking untuk pelapor
- ❌ Tidak ada notifikasi email
- ❌ UI sederhana, kurang guidance
- ❌ Tidak ada validasi kronologis 5W+1H

---

### WBS-v2 (Baru)

#### Alur Pengiriman Laporan
1. **Landing Page Modern**
   - URL: `/`
   - Hero section dengan CTA "Buat Laporan"
   - Statistik real-time laporan
   - Informasi lengkap tentang WBS
   - **Tidak perlu login** untuk melapor

2. **Wizard Multi-Step** (`/buat-laporan`)
   - Menggunakan Livewire untuk interaktivitas real-time
   - **5 Langkah Terstruktur** dengan progress bar
   - UI modern dengan rounded corners, shadows, gradients

#### Step-by-Step Breakdown

**Step 1: Identitas Pelapor**
```
- Nama Lengkap (required)
- No. Handphone (required)
- Checkbox: Rahasiakan identitas (Anonim)
  → Jika anonim: identitas dienkripsi
- Checkbox: Terima notifikasi via email
  → Jika ya: munculkan field email (required)
```

**Step 2: Substansi Laporan**
```
- Kategori Laporan (dropdown, required)
- Identitas Terlapor (textarea, required)
  → Panduan: "Nama dan jabatan pihak yang dilaporkan"
```

**Step 3: Kronologis 5W+1H**
```
- Apa yang terjadi? (What) - required, 5000 char max
- Siapa yang terlibat? (Who) - optional
- Kapan terjadi? (When) - date picker
- Lokasi Kejadian - text input
- Di mana? (Where) - textarea
- Mengapa terjadi? (Why) - textarea
- Bagaimana kronologisnya? (How) - textarea
```

**Step 4: Upload Bukti**
```
- Multiple file upload
- Drag & drop interface
- Loading indicator
- Preview file yang diupload
- Remove file capability
- Format: PDF, DOC, DOCX, JPG, PNG, WEBP
- Max 10MB per file
```

**Step 5: Preview & Konfirmasi**
```
- Review semua data yang diinput
- Checkbox: Pernyataan Kebenaran
  → "Saya menyatakan bahwa informasi yang saya sampaikan adalah benar..."
- Button: Kirim Laporan
```

3. **Success State**
   - **Nomor Registrasi** otomatis (format: WBS-YYYYMM-XXXX)
   - **Tracking Password** otomatis (6 digit random)
   - Copy button untuk menyalin keduanya
   - Warning box: "Simpan nomor registrasi dan password ini"
   - Button: "Cek Status Laporan"
   - Button: "Kembali ke Beranda"

4. **Notifikasi Email** (jika opted-in)
   - Email otomatis dikirim dengan:
     - Nomor registrasi
     - Password tracking
     - Link cek status
     - Detail laporan

#### Keunggulan
- ✅ Form publik tanpa login
- ✅ Wizard step-by-step mengurangi cognitive load
- ✅ Progress bar yang jelas
- ✅ Validasi per-step
- ✅ Opsi anonim dengan enkripsi identitas
- ✅ Sistem tracking dengan nomor registrasi + password
- ✅ Notifikasi email otomatis
- ✅ UI modern, responsive, user-friendly
- ✅ Kronologis terstruktur 5W+1H
- ✅ Multiple file upload
- ✅ Real-time feedback (Livewire)
- ✅ Mobile-friendly
- ✅ Copy-paste credentials dengan satu klik

---

## 🔍 2. PROSES VERIFIKASI (Verifikator Role)

### WBS Lama

#### Dashboard Verifikator
- **View**: Tabel sederhana dengan AdminLTE template
- **Kolom Tabel**:
  - Jenis Aduan
  - File Bukti (download link)
  - Nama Terlapor
  - Status (Disetujui/Ditolak/Belum diverifikasi)
  - Action (View, Edit, Delete)

#### Proses Verifikasi
1. Klik tombol **Edit** pada laporan
2. Redirect ke halaman edit (`/aduans/{id}/edit`)
3. Form menampilkan fields:
   ```php
   - Status Verifikasi (dropdown)
     → 1 = Verifikasi
     → 2 = Tolak
   - File Verifikator (upload)
   - Catatan Verifikasi (textarea)
   ```
4. Submit form
5. Update status ke database (field `status`)
6. Flash message: "Aduan telah di verifikasi"
7. Redirect kembali ke index

#### Kekurangan
- ❌ Tidak ada timeline/history perubahan status
- ❌ Tidak ada komentar untuk pelapor
- ❌ Tidak ada notifikasi ke pelapor
- ❌ Status hanya angka (1, 2) - kurang deskriptif
- ❌ Tidak ada filter laporan
- ❌ Tidak ada bulk actions
- ❌ UI tabel sederhana tanpa badge
- ❌ Tidak ada pencarian advanced
- ❌ File upload dengan FilePond terpisah

---

### WBS-v2 (Baru)

#### Dashboard Verifikator (Filament Panel)
- **URL**: `/verifikator`
- **Framework**: Filament PHP (admin panel modern)
- **View**: Resource table dengan fitur lengkap
  
**Kolom Tabel**:
```
- No. Registrasi (searchable, copyable, bold)
- Pelapor (searchable di relasi)
- Kategori (searchable, wrapped, limit 30 char)
- Status (badge dengan warna dinamis)
- Saluran (badge: Website/API/Superapps)
- Tanggal Lapor (sortable, format: d M Y H:i)
```

**Fitur Table**:
- ✅ Search global
- ✅ Sort per kolom
- ✅ Filter by:
  - Status
  - Kategori
  - Saluran
  - Trash status
- ✅ Pagination otomatis
- ✅ Default sort: created_at DESC
- ✅ Toggle columns visibility

#### Proses Update Status (Modal Action)
1. **Klik Action "Update Status"** (ikon arrow-path, warna warning)
2. **Modal muncul** dengan form:
   ```
   - Status Baru (enum select dengan label jelas)
   - Komentar (textarea 3 rows, optional)
   - Toggle: Tampilkan ke Pelapor (default: true)
   ```

3. **Submit Action**:
   ```php
   // Backend Logic
   $record->updateStatus(
       $newStatus,      // Enum AduanStatus
       $komentar,       // String
       auth()->user(),  // User yang update
       $is_public       // Boolean
   );
   
   // Create Timeline Entry
   Timeline::create([
       'aduan_id' => $record->id,
       'old_status' => $oldStatus,
       'new_status' => $newStatus,
       'komentar' => $komentar,
       'user_id' => auth()->id(),
       'is_public' => $is_public,
   ]);
   
   // Dispatch Email Job (jika is_public)
   if ($is_public) {
       SendStatusUpdateEmail::dispatch($record, $newStatus, $komentar);
   }
   
   // Clear cache
   Cache::forget('admin_stats');
   Cache::forget('landing_stats');
   ```

4. **Notifikasi Sukses**
5. **Refresh table** otomatis

#### View Detail Laporan
**Tab 1: Informasi Laporan** (Infolist)
```
Section: Informasi Laporan
- Nomor Registrasi (bold, large)
- Status (badge dengan warna)
- Saluran
- Tanggal Lapor

Section: Pelapor
- Nama Pelapor (atau "Anonim")
- No. HP
- Email

Section: Kategori & Terlapor
- Kategori
- Identitas Terlapor

Section: Kronologis
- Apa yang terjadi?
- Siapa yang terlibat?
- Kapan? (formatted date)
- Lokasi
- Di mana?
- Mengapa?
- Bagaimana?
```

**Tab 2: Bukti Pendukung** (Relation Manager)
- Tabel bukti dengan kolom:
  - File Name
  - File Type (foto/dokumen)
  - File Size
  - Uploaded At
  - Actions (view/download)
  
**Tab 3: Riwayat Status** (Timeline Relation Manager)
- Tabel timeline dengan kolom:
  - Waktu (sortable)
  - Status Lama (badge gray)
  - Status Baru (badge dengan warna dinamis)
  - Oleh (User atau "Sistem")
  - Komentar (limit 50, wrapped)
  - Publik (icon boolean)
- Default sort: created_at DESC

#### Keunggulan
- ✅ UI modern dengan Filament
- ✅ Timeline lengkap perubahan status
- ✅ Komentar untuk pelapor
- ✅ Notifikasi email otomatis
- ✅ Status dengan enum (type-safe)
- ✅ Filter & search advanced
- ✅ Badge dengan warna semantik
- ✅ Modal action (tidak perlu redirect)
- ✅ Relasi manager untuk bukti & timeline
- ✅ Audit trail (siapa, kapan update)
- ✅ Cache management otomatis
- ✅ Responsive & mobile-friendly

---

## 🔬 3. PROSES VALIDASI (Inspektur Role)

### WBS Lama

#### Dashboard Inspektur
- **Kolom Tabel**:
  - Jenis Aduan
  - Nama Terlapor
  - File Bukti (download)
  - **File Verifikator** (download dari verifikator)
  - Status (Disetujui/Ditolak/Belum di validasi)
  - Action (View, Edit, Delete)

#### Proses Validasi
1. Klik **Edit**
2. Halaman edit dengan fields:
   ```php
   - Status Validasi (dropdown)
     → 3 = Validasi
     → 4 = Tolak
   - File Inspektur (upload)
   - Catatan Validasi (textarea)
   ```
3. Submit, update `status` field
4. Flash message: "Aduan telah di Validasi"
5. Redirect ke index

#### Kekurangan
- Sama seperti verifikator:
  - ❌ Tidak ada timeline
  - ❌ Tidak ada notifikasi
  - ❌ Status angka saja
  - ❌ Tidak ada filter
  - ❌ UI basic

---

### WBS-v2 (Baru)

#### Dashboard Inspektur (Filament Panel)
- **URL**: `/inspektur`
- **Identik dengan Verifikator**, tetapi dengan:
  - Role-based access control
  - Melihat laporan yang sudah status ≥ "Diverifikasi"
  - Dapat update ke status lebih lanjut

#### Proses Update Status
- **Sama seperti Verifikator**
- Menggunakan **Action "Update Status"** yang sama
- Dapat memilih status lanjutan:
  - Dalam Pemeriksaan
  - Terbukti
  - Tidak Terbukti
  - Selesai

#### Keunggulan
- ✅ Konsisten dengan UX Verifikator
- ✅ Timeline terintegrasi
- ✅ Notifikasi otomatis
- ✅ Role-based visibility
- ✅ Audit trail lengkap

---

## 👨‍💼 4. PROSES ADMIN (Hasil Pemeriksaan)

### WBS Lama

#### Dashboard Admin
- View tabel dengan kolom standar
- Halaman edit dengan field:
  ```php
  - Hasil Pemeriksaan (dropdown)
    → 5 = Terbukti
    → 6 = Tidak terbukti
  ```
- Update `status` field
- Jika selesai: set `tgl_selesai`

#### Fitur Khusus Admin
1. **Mark as Selesai** (`/aduans/selesai/{id}`)
   - Set tanggal selesai
   - Send email notification (hardcoded to 'sayidyubi28@gmail.com')
   - Flash message: "Aduan telah ditandai sebagai telah selesai, dan email berhasil dikirim"

2. **Export Excel**
   - `/ekspor` - Export filtered data
   - `/ekspor-all` - Export all data

#### Kekurangan
- ❌ Email hardcoded ke satu alamat
- ❌ Tidak ada timeline
- ❌ Export tidak customizable

---

### WBS-v2 (Baru)

#### Dashboard Admin (Filament Panel)
- **URL**: `/admin`
- **Full access** ke semua laporan
- **Fitur tambahan**:
  - Manage Users
  - Manage Jenis Aduan
  - Manage Pelapor
  - View statistics

#### Export Data
- **Filament Export** (bawaan)
- Export ke Excel/CSV
- Filter data sebelum export
- Customizable columns

#### Notification System
- Email dikirim ke **pelapor** (jika ada email & notify_email = true)
- Template email profesional
- Queue job untuk performance
- Retry mechanism

#### Keunggulan
- ✅ Role-based access granular
- ✅ Email ke pelapor yang benar
- ✅ Export fleksibel
- ✅ Statistics dashboard
- ✅ User management terintegrasi

---

## 📊 5. STATUS FLOW COMPARISON

### WBS Lama - Status Flow

```
Status (Integer):
1 → Verifikasi (Disetujui oleh Verifikator)
2 → Ditolak oleh Verifikator
3 → Validasi (Disetujui oleh Inspektur)
4 → Ditolak oleh Inspektur
5 → Terbukti (Admin)
6 → Tidak Terbukti (Admin)
7 → Selesai (Admin - manual mark)
```

**Masalah**:
- ❌ Status sebagai integer → tidak type-safe
- ❌ Tidak ada status "Pending" atau "Dalam Proses"
- ❌ Tidak ada status "Dalam Pemeriksaan"
- ❌ Magic numbers di kode
- ❌ Tidak ada validasi transisi status

---

### WBS-v2 - Status Flow (Enum)

```php
enum AduanStatus: string
{
    case PENDING = 'pending';                    // Baru masuk
    case VERIFIED = 'verified';                  // Diverifikasi
    case REJECTED_VERIFICATION = 'rejected_verification';  // Ditolak verifikasi
    case UNDER_INVESTIGATION = 'under_investigation';  // Dalam pemeriksaan
    case REJECTED_VALIDATION = 'rejected_validation';  // Ditolak validasi
    case PROVEN = 'proven';                      // Terbukti
    case NOT_PROVEN = 'not_proven';             // Tidak terbukti
    case COMPLETED = 'completed';                // Selesai
    
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Menunggu Verifikasi',
            self::VERIFIED => 'Terverifikasi',
            self::REJECTED_VERIFICATION => 'Ditolak Verifikasi',
            self::UNDER_INVESTIGATION => 'Dalam Pemeriksaan',
            self::REJECTED_VALIDATION => 'Ditolak Validasi',
            self::PROVEN => 'Terbukti',
            self::NOT_PROVEN => 'Tidak Terbukti',
            self::COMPLETED => 'Selesai',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::VERIFIED => 'info',
            self::REJECTED_VERIFICATION, self::REJECTED_VALIDATION => 'danger',
            self::UNDER_INVESTIGATION => 'primary',
            self::PROVEN => 'success',
            self::NOT_PROVEN => 'gray',
            self::COMPLETED => 'success',
        };
    }
}
```

**Flow Normal**:
```
PENDING 
  → VERIFIED (Verifikator)
    → UNDER_INVESTIGATION (Inspektur)
      → PROVEN/NOT_PROVEN (Admin/Inspektur)
        → COMPLETED (Admin)
```

**Flow Rejected**:
```
PENDING 
  → REJECTED_VERIFICATION (Verifikator)
    [END]

PENDING → VERIFIED
  → REJECTED_VALIDATION (Inspektur)
    [END]
```

**Keunggulan**:
- ✅ Type-safe dengan enum
- ✅ Self-documenting code
- ✅ Label yang jelas untuk user
- ✅ Warna semantik
- ✅ Mudah add/remove status
- ✅ IDE autocomplete

---

## 🎨 6. UI/UX COMPARISON

### WBS Lama

**Tech Stack**:
- Laravel Blade templates
- AdminLTE 3 template
- Bootstrap 4
- jQuery
- Laravel Collective Forms
- FilePond for file upload

**Karakteristik UI**:
- ❌ Traditional admin panel design
- ❌ Full page refresh on every action
- ❌ No real-time validation
- ❌ Basic forms
- ❌ No modal dialogs
- ❌ Limited mobile responsiveness
- ❌ No dark mode
- ❌ Basic color scheme
- ❌ No animations/transitions

**User Journey**:
```
1. Login → 2. Navigate Menu → 3. Click Create → 4. Fill Long Form → 
5. Submit → 6. Redirect → 7. See Flash Message
```

---

### WBS-v2 (Baru)

**Tech Stack**:
- Laravel Livewire (for public forms)
- Filament PHP v3 (for admin panels)
- TailwindCSS
- Alpine.js
- OKLCH color system
- Modern CSS (glassmorphism, gradients)

**Karakteristik UI**:
- ✅ Modern, clean design
- ✅ SPA-like experience (no full refresh)
- ✅ Real-time validation
- ✅ Wizard/multi-step forms
- ✅ Modal dialogs
- ✅ Fully responsive
- ✅ Dark mode support (Filament)
- ✅ Custom color palette (OKLCH)
- ✅ Smooth animations/transitions
- ✅ Loading states
- ✅ Toast notifications
- ✅ Copy-to-clipboard functionality

**User Journey (Pelapor)**:
```
1. Landing Page → 2. Click "Buat Laporan" → 
3. Step 1 (Identitas) → Validate → 
4. Step 2 (Substansi) → Validate → 
5. Step 3 (Kronologis) → Validate → 
6. Step 4 (Bukti) → Upload → 
7. Step 5 (Preview) → Agree → Submit → 
8. Success Page with Credentials
```

**User Journey (Admin/Staff)**:
```
1. Login → Dashboard → 
2. See Table with Filters → 
3. Click Row → View Details (tabs) → 
4. Click "Update Status" → Modal → Fill Form → Submit → 
5. Toast Notification → Table Refresh → Email Sent
```

---

## 📧 7. NOTIFICATION SYSTEM

### WBS Lama

**Email**:
- ❌ Only on "Selesai" mark
- ❌ Hardcoded recipient email
- ❌ Basic mail template
- ❌ No job queue
- ❌ No retry mechanism

**Code**:
```php
// Hardcoded email
Mail::send('mail.email', ['nomor' => $a->id], function ($message) use ($a) {
    $message->subject("Pemberitahuan Atas Status Laporan");
    $message->from('Inspektorat@bontangkota.go.id', 'Inspektorat Kota Bontang');
    $message->to('sayidyubi28@gmail.com'); // HARDCODED!
});
```

---

### WBS-v2 (Baru)

**Email Events**:
1. **Report Submitted** (`SendReportSubmittedEmail.php`)
   - Triggered: After successful report submission
   - Sent to: Pelapor email (if notify_email = true)
   - Contains:
     - Nomor Registrasi
     - Tracking Password
     - Link to check status
     - Report category
     - Timestamp

2. **Status Updated** (`SendStatusUpdateEmail.php`)
   - Triggered: After admin/staff update status (if is_public = true)
   - Sent to: Pelapor email
   - Contains:
     - Nomor Registrasi
     - Old Status → New Status
     - Komentar dari staff
     - Link to check status
     - Timestamp

**Features**:
- ✅ Queue jobs (async processing)
- ✅ Retry on failure (3 attempts)
- ✅ Professional email templates (Blade)
- ✅ Personalized content
- ✅ Correct recipient (pelapor)
- ✅ Environment-aware (sandbox mode in dev)

**Code Example**:
```php
// Job Dispatch
SendStatusUpdateEmail::dispatch($aduan, $newStatus, $komentar);

// Job Class
class SendStatusUpdateEmail implements ShouldQueue
{
    public $tries = 3;
    public $backoff = [60, 120, 300]; // Retry after 1min, 2min, 5min
    
    public function handle()
    {
        if ($this->aduan->pelapor && $this->aduan->pelapor->email) {
            Mail::to($this->aduan->pelapor->email)
                ->send(new StatusUpdateMail($this->aduan, $this->newStatus, $this->komentar));
        }
    }
}
```

---

## 🔐 8. SECURITY & PRIVACY

### WBS Lama

**Security**:
- ✅ CSRF protection (Laravel default)
- ✅ Authentication required
- ❌ No role-based access control on routes
- ❌ No encryption for sensitive data
- ❌ Password hashing for users only

**Privacy**:
- ❌ No anonymous reporting
- ❌ Pelapor identity always visible to all staff
- ❌ No data encryption

---

### WBS-v2 (Baru)

**Security**:
- ✅ CSRF protection
- ✅ reCAPTCHA v3 on public forms
- ✅ Role-based access control (Filament policies)
- ✅ Encryption for anonymous reports
- ✅ Password hashing
- ✅ Tracking password (bcrypt)
- ✅ Sanctum for API (if needed)
- ✅ Rate limiting on routes

**Privacy**:
- ✅ **Anonymous reporting** option
- ✅ Identity encryption for anonymous reports
  ```php
  // Encryption
  $pelapor->encrypted_identity = encrypt([
      'nama' => $nama,
      'phone' => $phone
  ]);
  
  // Decryption (admin hanya bisa decrypt dengan permission)
  $identity = decrypt($pelapor->encrypted_identity);
  ```
- ✅ GDPR-compliant
- ✅ Soft delete (data tidak langsung dihapus)

---

## 📱 9. TRACKING SYSTEM

### WBS Lama

**Tracking**:
- ❌ **TIDAK ADA** sistem tracking untuk pelapor
- ❌ Pelapor tidak bisa cek status laporan sendiri
- ❌ Harus hubungi admin untuk tahu status
- ❌ Tidak ada nomor registrasi

**Code**:
```php
// Fetch function (tidak jelas fungsinya)
public function fetch(Request $request)
{
    $aduan = Aduan::select('*');
    if ($request->input('q')) {
        $aduan->find($request->nomor);
    }
    // ... switch case status
    return \compact('status');
}
```

---

### WBS-v2 (Baru)

**Tracking System**:
1. **Nomor Registrasi Otomatis**
   ```php
   // Format: WBS-YYYYMM-0001
   public function generateNomorRegistrasi()
   {
       $prefix = 'WBS-' . now()->format('Ym') . '-';
       $lastNumber = Aduan::where('nomor_registrasi', 'like', $prefix . '%')
           ->max('nomor_registrasi');
       
       $newNumber = $lastNumber 
           ? ((int) substr($lastNumber, -4)) + 1 
           : 1;
       
       $this->nomor_registrasi = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
   }
   ```

2. **Tracking Password**
   ```php
   // 6-digit random password, hashed in database
   public function generateTrackingPassword(): string
   {
       $plainPassword = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
       $this->tracking_password = bcrypt($plainPassword);
       return $plainPassword; // Return once to show user
   }
   ```

3. **Cek Status Page** (`/cek-status`)
   - Public access (no login required)
   - Form dengan:
     - Input: Nomor Registrasi
     - Input: Password Tracking
   - Submit → Verify password
   - Show:
     - Status badge
     - Timeline publik (only is_public = true)
     - Kategori
     - Tanggal lapor
     - Tanggal update terakhir
   - **Tidak menampilkan**:
     - Kronologis lengkap
     - Data sensitif lainnya
     - Timeline internal (is_public = false)

**Security**:
- ✅ Password hashed di database
- ✅ Rate limiting (prevent brute force)
- ✅ Only show public timeline
- ✅ No sensitive data exposed

---

## 🗄️ 10. DATABASE STRUCTURE COMPARISON

### WBS Lama

**Table: `aduans`**
```sql
- id
- jenis_aduan_id (FK to jenis_aduans)
- user_id (FK to users - pelapor yang login)
- nama_terlapor
- jabatan_terlapor
- pangkat_terlapor
- instansi_terlapor
- unit_terlapor
- kota_terlapor
- file_bukti (single file path)
- file_verifikator (path)
- file_inspektur (path)
- catatan_verifikasi (text)
- catatan_validasi (text)
- status (integer: 1-7)
- tgl_selesai (datetime)
- created_at
- updated_at
```

**Issues**:
- ❌ No separate Pelapor table
- ❌ No Timeline/History table
- ❌ No multiple files support
- ❌ No nomor_registrasi
- ❌ No tracking_password
- ❌ Status as integer
- ❌ No channel info
- ❌ No 5W+1H fields

---

### WBS-v2 (Baru)

**Table: `pelapors`** (Separate table)
```sql
- id
- nama
- phone
- email (nullable)
- notify_email (boolean)
- is_anonim (boolean)
- encrypted_identity (text, nullable) - for anonymous
- created_at
- updated_at
```

**Table: `aduans`**
```sql
- id
- nomor_registrasi (unique, indexed)
- tracking_password (hashed)
- pelapor_id (FK to pelapors, nullable)
- user_id (FK to users, nullable) - if submitted by logged-in user
- jenis_aduan_id (FK to jenis_aduans)
- channel (enum: website, api, superapps)
- identitas_terlapor (text)
- what (text)
- who (text, nullable)
- when_date (date, nullable)
- where_location (text, nullable)
- why (text, nullable)
- how (text, nullable)
- lokasi_kejadian (varchar, nullable)
- status (enum: pending, verified, rejected_verification, etc.)
- created_at
- updated_at
- deleted_at (soft delete)
```

**Table: `bukti_pendukungs`** (Multiple files)
```sql
- id
- aduan_id (FK to aduans)
- file_path (text)
- file_name (varchar)
- file_type (enum: foto, dokumen, lainnya)
- mime_type (varchar)
- file_size (bigint)
- created_at
- updated_at
```

**Table: `timelines`** (Status history)
```sql
- id
- aduan_id (FK to aduans)
- old_status (enum, nullable)
- new_status (enum)
- komentar (text, nullable)
- user_id (FK to users, nullable)
- is_public (boolean) - visible to pelapor?
- created_at
- updated_at
```

**Benefits**:
- ✅ Normalized structure
- ✅ Audit trail
- ✅ Multiple files support
- ✅ Separate pelapor data
- ✅ Timeline tracking
- ✅ Type-safe enums
- ✅ Soft delete
- ✅ Better indexing

---

## 📈 11. PERFORMANCE & SCALABILITY

### WBS Lama

**Performance**:
- ❌ No caching
- ❌ No job queues
- ❌ Synchronous email sending (blocks request)
- ❌ N+1 queries (no eager loading)
- ❌ No database indexing strategy

**Scalability**:
- ❌ Tightly coupled code
- ❌ No API
- ❌ Single file uploads only

---

### WBS-v2 (Baru)

**Performance**:
- ✅ Redis caching:
  ```php
  // Cache jenis_aduans for 1 hour
  Cache::remember('jenis_aduans_active', 3600, function () {
      return JenisAduan::active()->pluck('name', 'slug');
  });
  
  // Cache stats
  Cache::remember('landing_stats', 300, function () { ... });
  ```
- ✅ Queue jobs for emails (async)
- ✅ Eager loading:
  ```php
  ->with(['pelapor', 'user', 'jenisAduan'])
  ```
- ✅ Database indexes:
  - nomor_registrasi (unique)
  - status
  - created_at
  - jenis_aduan_id

**Scalability**:
- ✅ Loosely coupled (Services, Jobs, Events)
- ✅ RESTful API:
  ```
  POST /api/aduans - Create report
  GET /api/aduans/{nomor_registrasi} - Track status
  ```
- ✅ Multiple file uploads (cloud storage ready)
- ✅ Horizontal scaling ready (stateless)

---

## 🧪 12. TESTING & QUALITY

### WBS Lama

**Testing**:
- ❌ No tests included
- ❌ Manual testing only
- ❌ No CI/CD

**Code Quality**:
- ❌ Mixed concerns in controllers
- ❌ Magic numbers in code
- ❌ Hardcoded values
- ❌ No strict typing

---

### WBS-v2 (Baru)

**Testing**:
- ✅ Feature tests:
  - `AduanApiTest.php` - API endpoints
  - Form validation tests
- ✅ Unit tests (can be added for enums, models)
- ✅ PHPUnit configured
- ✅ CI/CD ready

**Code Quality**:
- ✅ Service layer separation
- ✅ Enum for type safety
- ✅ Strict typing (`declare(strict_types=1)`)
- ✅ PSR-12 coding standards
- ✅ SOLID principles
- ✅ DRY code

---

## 📊 13. SUMMARY TABLE

| Aspect | WBS Lama | WBS-v2 |
|--------|----------|--------|
| **Public Access** | ❌ Login required | ✅ Public form |
| **Form UX** | ❌ Single long form | ✅ Wizard (5 steps) |
| **Anonymous Reporting** | ❌ No | ✅ Yes (encrypted) |
| **Tracking System** | ❌ No | ✅ Yes (No.Reg + Password) |
| **Timeline/History** | ❌ No | ✅ Yes |
| **Email Notifications** | ❌ Hardcoded, basic | ✅ Dynamic, queued |
| **Status System** | ❌ Integer (1-7) | ✅ Enum (type-safe) |
| **Multiple Files** | ❌ No | ✅ Yes |
| **UI Framework** | AdminLTE + Bootstrap | Filament + Tailwind |
| **Responsiveness** | ⚠️ Limited | ✅ Fully responsive |
| **Real-time Validation** | ❌ No | ✅ Yes (Livewire) |
| **Modal Actions** | ❌ No | ✅ Yes |
| **Search & Filter** | ❌ Basic | ✅ Advanced |
| **Role-based Access** | ⚠️ Hardcoded roles | ✅ Policy-based |
| **API Support** | ❌ No | ✅ Yes (RESTful) |
| **Caching** | ❌ No | ✅ Redis |
| **Queue Jobs** | ❌ No | ✅ Yes |
| **Testing** | ❌ No tests | ✅ Feature tests |
| **Database Structure** | ⚠️ Single table | ✅ Normalized |
| **Code Quality** | ⚠️ Mixed concerns | ✅ SOLID, typed |

---

## 🎯 14. MIGRATION RECOMMENDATIONS

Jika ingin upgrade dari WBS lama ke WBS-v2:

### Data Migration
1. **Migrate Users**:
   - Map role_id ke role system baru
   
2. **Migrate Jenis Aduan**:
   - Transfer dengan slug generation
   
3. **Migrate Aduans**:
   - Create Pelapor record dari data user
   - Generate nomor_registrasi
   - Generate tracking_password
   - Map old status integer ke enum
   - Split terlapor fields ke identitas_terlapor
   - Create initial Timeline entry
   
4. **Migrate Files**:
   - Move file_bukti → BuktiPendukung record
   - Move file_verifikator → BuktiPendukung
   - Move file_inspektur → BuktiPendukung
   
5. **Send Migration Emails**:
   - Email all pelapor dengan nomor_registrasi baru
   - Include tracking password

### Training Requirements
1. **Staff Training** (2-4 hours):
   - Navigasi Filament panel
   - Update status via modal action
   - View timeline & bukti
   - Export data
   
2. **Public Awareness**:
   - Sosialisasi form baru
   - Cara tracking status
   - Tutorial video

---

## ✅ 15. CONCLUSION

### WBS Lama
**Strengths**:
- Simple, straightforward
- Works for basic needs

**Weaknesses**:
- Outdated UX
- No public access
- No tracking system
- Limited notifications
- Poor scalability
- No audit trail

### WBS-v2
**Strengths**:
- Modern, intuitive UX
- Public access with wizard
- Anonymous reporting
- Comprehensive tracking
- Full audit trail
- Email notifications
- Type-safe code
- Scalable architecture
- API support

**Weaknesses**:
- More complex to maintain
- Requires Redis, Queue worker
- Steeper learning curve untuk admin

---

## 🔗 16. REFERENCES

### WBS Lama Files
- `routes/web.php`
- `app/Http/Controllers/AduanController.php`
- `resources/views/aduans/*.blade.php`
- `resources/views/verifikator/fields.blade.php`
- `resources/views/inspektur/fields.blade.php`

### WBS-v2 Files
- `routes/web.php`
- `app/Livewire/BuatLaporanWizard.php`
- `resources/views/livewire/buat-laporan-wizard.blade.php`
- `app/Filament/Resources/AduanResource.php`
- `app/Filament/Resources/AduanResource/RelationManagers/TimelinesRelationManager.php`
- `app/Enums/AduanStatus.php`
- `app/Models/Aduan.php`
- `app/Jobs/SendStatusUpdateEmail.php`

---

**Dokumen ini dibuat pada**: 2026-02-09  
**Versi**: 1.0  
**Author**: AI Assistant Analysis
