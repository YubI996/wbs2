# LAPORAN PEKERJAAN
## Pengembangan Sistem Whistle Blowing System (WBS) v2
### Kota Bontang

---

| | |
|---|---|
| **Nama** | Yubi |
| **Periode** | Januari 2026 – April 2026 |
| **Sistem** | Whistle Blowing System (WBS) v2 |
| **Teknologi** | Laravel 12, Filament v5, MySQL, Docker, Nginx |
| **Tanggal Laporan** | 10 April 2026 |

---

## Ringkasan Eksekutif

Selama periode Januari hingga April 2026, telah dilaksanakan pembangunan dan pengembangan sistem Whistle Blowing System (WBS) versi 2 dari tahap awal hingga siap produksi. Sistem ini merupakan platform pelaporan pengaduan masyarakat berbasis web yang terintegrasi dengan berbagai kanal pengaduan (Website, WhatsApp, Instagram, SP4N LAPOR!, dan SuperApps).

Pekerjaan mencakup pembangunan sistem dari nol, perbaikan keamanan, pengembangan fitur, konfigurasi deployment, audit keamanan, dan pengembangan API untuk integrasi pihak eksternal.

---

## Rincian Pekerjaan

### 1. Pembangunan Sistem WBS v2 (Januari 2026)

Membangun sistem WBS versi 2 menggunakan Laravel 12 dan Filament v5 dari awal, menggantikan sistem versi sebelumnya.

**Fitur utama yang dibangun:**

- **Manajemen Aduan** — Sistem pengaduan lengkap dengan alur status: *Pending → Verifikasi → Proses → Investigasi → Selesai/Ditolak*
- **Multi-panel Admin** — Tiga panel terpisah untuk Admin, Verifikator, dan Inspektur, masing-masing dengan hak akses yang berbeda
- **Sistem Pelapor** — Mendukung pelapor terdaftar dan pelapor anonim dengan enkripsi identitas
- **Bukti Pendukung** — Upload file bukti (gambar, dokumen, video, audio) dengan validasi MIME type berbasis isi file
- **Riwayat Status (Timeline)** — Pencatatan setiap perubahan status dengan komentar, dibedakan antara catatan publik dan internal
- **Sistem Tracking** — Pelapor dapat memantau status laporan menggunakan nomor registrasi dan password tracking
- **Nomor Registrasi Otomatis** — Format `WBS-YYYY-NNNNN` digenerate otomatis
- **Dashboard Statistik** — Widget statistik real-time untuk setiap panel (Admin, Verifikator, Inspektur)
- **Multi-channel** — Mendukung laporan dari Website, WhatsApp, Instagram DM, SP4N LAPOR!, dan SuperApps

---

### 2. Perbaikan UI/UX dan Halaman Publik (Januari 2026)

- Pembaruan palet warna ke format OKLCH modern di seluruh halaman publik
- Perbaikan tombol *copy* nomor registrasi menggunakan Alpine.js dengan fallback untuk non-HTTPS
- Penyesuaian routing logout agar semua panel (Admin, Verifikator, Inspektur) diarahkan ke halaman login terpadu `/login`

---

### 3. Konfigurasi Docker untuk Deployment Produksi (Januari – Februari 2026)

Menyiapkan infrastruktur deployment berbasis Docker agar sistem dapat dideploy secara konsisten di server produksi.

**Yang dikerjakan:**
- Konfigurasi `Dockerfile` multi-stage (build + production) dengan PHP 8.3, Nginx, dan ekstensi yang diperlukan
- Konfigurasi `docker-compose.yml` untuk orkestrasi container (PHP-FPM, Nginx, MySQL)
- Konfigurasi Nginx (`docker/nginx/conf.d/`) untuk menangani request Laravel
- Konfigurasi PHP (`docker/php/local.ini`) untuk kebutuhan upload file dan batas memori
- `installer.sh` — skrip otomasi instalasi dan konfigurasi awal server
- Penyelesaian masalah koneksi MySQL, healthcheck container, dan mount path storage

---

### 4. Perbaikan Login dan Keamanan Sesi (Februari 2026)

- **Perbaikan error "Page Expired"** — Menyelesaikan masalah CSRF token yang kedaluwarsa saat form login dikirim, termasuk endpoint `/csrf-token` untuk refresh token tanpa reload halaman
- **Perbaikan silent failure** — Form login yang gagal tidak lagi diam tanpa respons; error kini ditampilkan dengan jelas
- **Middleware logout** — Membuat `LogoutResponse` khusus untuk menggantikan metode yang sudah tidak kompatibel dengan Filament v5

---

### 5. Perbaikan Keamanan — IDOR (Februari 2026)

Ditemukan dan diperbaiki celah keamanan **IDOR (Insecure Direct Object Reference)** pada sistem WBS.

- Sebelumnya, beberapa endpoint menggunakan ID integer yang dapat ditebak, memungkinkan pengguna mengakses data laporan milik orang lain
- Diperbaiki dengan menggunakan **UUID** sebagai parameter URL untuk semua akses data aduan dan bukti pendukung
- Migrasi database ditambahkan untuk menambahkan kolom UUID pada tabel `aduans` dan `bukti_pendukungs`

---

### 6. Peningkatan Halaman Beranda (Februari 2026)

- Penambahan logo instansi dan ilustrasi visual pada halaman utama
- Peningkatan tampilan keseluruhan (UI enhancement) untuk kesan lebih profesional
- Perbaikan landing page berulang berdasarkan masukan

---

### 7. Fitur Tambahan (Februari 2026)

- **Tabel Notifikasi** — Migrasi database untuk tabel `notifications` sebagai fondasi fitur notifikasi email/push di masa mendatang
- **Command SimulateAduanFlow** — Artisan command untuk mensimulasikan alur lengkap pengaduan dari awal hingga selesai, digunakan untuk keperluan testing dan demonstrasi sistem

---

### 8. Implementasi reCAPTCHA (Februari 2026)

Menambahkan proteksi bot pada form-form publik untuk mencegah spam dan penyalahgunaan sistem.

**Tahap implementasi:**
1. **reCAPTCHA v2** (Google "I'm not a robot" checkbox) — diterapkan pertama pada form Login dan form Buat Laporan
2. **Upgrade ke reCAPTCHA v3** — Diganti dengan reCAPTCHA v3 yang bekerja di latar belakang tanpa interaksi pengguna, memberikan pengalaman yang lebih mulus
3. Perbaikan spacing dan layout form login agar tampil rapi di berbagai ukuran layar

---

### 9. Audit Keamanan Komprehensif (April 2026)

Dilakukan audit keamanan menyeluruh terhadap tiga area utama sistem:

#### a. Audit Validasi Form Buat Laporan
Ditemukan **8 temuan** pada form pengaduan publik:

| # | Temuan | Tingkat |
|---|--------|---------|
| 1 | Tanggal kejadian tidak dibatasi, bisa diisi tanggal masa depan | Medium |
| 2 | Jumlah file tidak dibatasi di form web (berbeda dengan API) | Medium |
| 3 | Inkonsistensi field wajib antara form web dan API | Medium |
| 4 | Inkonsistensi batas karakter `identitas_terlapor` (1000 vs 500) | Low |
| 5 | Tidak ada panjang minimum pada field teks penting | Low |
| 6 | Format nomor HP tidak divalidasi | Low |
| 7 | Pesan error sistem tampil langsung ke pengguna | Low |
| 8 | Validasi file form web lebih lemah dari API | Low |

#### b. Audit Keamanan Sistem
Ditemukan **4 temuan** pada sistem otentikasi dan perlindungan data:

| # | Temuan | Tingkat |
|---|--------|---------|
| 1 | Role middleware tidak diterapkan di panel Inspektur (bypass akses dekripsi data anonim) | High |
| 2 | Login panel admin tidak memiliki rate limiting (rentan brute force) | Medium |
| 3 | Email pelapor tercatat plaintext di log aplikasi | Medium |
| 4 | Email pelapor anonim tersimpan plaintext di database | Low |

#### c. Audit Keamanan API
Ditemukan **7 temuan** pada layer API:

| # | Temuan | Tingkat |
|---|--------|---------|
| 1 | API key hardcoded di source code dan dokumentasi | High |
| 2 | Validasi API key rentan timing attack (tidak menggunakan `hash_equals`) | High |
| 3 | Tidak ada rate limiting di semua endpoint API | Medium |
| 4 | Pesan error `$e->getMessage()` bocor ke client saat debug aktif | Medium |
| 5 | Endpoint publik tanpa throttle (rentan flood/DDoS) | Medium |
| 6 | MIME type bocor di pesan error FileValidationService | Low |
| 7 | Tidak ada log saat autentikasi API gagal | Low |

Seluruh temuan audit telah didokumentasikan beserta rekomendasi perbaikan teknis yang spesifik.

---

### 10. Pengembangan API Statistik (April 2026)

Membuat endpoint API baru untuk kebutuhan dashboard statistik dan integrasi eksternal.

**Endpoint:** `GET /api/statistik`

**Data yang dikembalikan:**
- Ringkasan total laporan (hari ini, bulan ini, tahun ini, keseluruhan)
- Distribusi laporan per status (pending, verifikasi, proses, investigasi, selesai, ditolak)
- Distribusi laporan per kanal masuk (website, WhatsApp, Instagram, SP4N, SuperApps)
- Distribusi laporan per jenis aduan
- Tingkat penyelesaian laporan (persentase selesai dari total ditutup dan dari keseluruhan)
- Tren bulanan hingga 24 bulan ke belakang

Endpoint ini bersifat publik (tanpa API key) dengan cache server 5 menit untuk performa optimal.

---

### 11. Kelengkapan Dokumentasi API (April 2026)

Melengkapi dokumen `API_DOCUMENTATION.md` menjadi referensi teknis yang komprehensif untuk tim pengembang SuperApps dan pihak eksternal yang akan berintegrasi.

**Yang ditambahkan/diperbaiki:**
- Daftar isi dengan anchor link
- Dokumentasi lengkap endpoint `GET /api/statistik` (baru)
- Contoh request dan response untuk setiap endpoint
- Contoh error response spesifik per endpoint (sebelumnya hanya satu bagian global)
- Koreksi tipe data parameter `jenis_aduan` (dari integer ke string/slug)
- Diagram alur transisi status laporan
- Tabel HTTP status code lengkap
- Penambahan catatan keamanan (tracking password dan data anonim)

---

## Rekapitulasi

| Area Pekerjaan | Status |
|----------------|--------|
| Pembangunan sistem WBS v2 | Selesai |
| Konfigurasi deployment Docker | Selesai |
| Perbaikan login dan keamanan sesi | Selesai |
| Perbaikan kerentanan IDOR | Selesai |
| Peningkatan halaman publik | Selesai |
| Implementasi reCAPTCHA v3 | Selesai |
| Audit keamanan (3 area, 19 temuan) | Selesai (dokumentasi rekomendasi) |
| API statistik | Selesai |
| Dokumentasi API | Selesai |

---

## Catatan dan Tindak Lanjut

Berdasarkan hasil audit keamanan, terdapat beberapa item yang memerlukan tindak lanjut segera sebelum sistem digunakan di lingkungan produksi:

1. **Prioritas Tinggi:** Pindahkan API key dari source code ke environment variable (`.env`) dan rotasi key yang sudah terekspos
2. **Prioritas Tinggi:** Tambahkan middleware role pada panel Inspektur untuk mencegah akses tidak sah ke fitur dekripsi identitas anonim
3. **Prioritas Menengah:** Tambahkan rate limiting pada semua endpoint API dan form login panel admin
4. **Prioritas Menengah:** Mask email pelapor sebelum dicatat ke log aplikasi

---

*Laporan ini dibuat berdasarkan riwayat pengembangan sistem WBS v2 periode Januari – April 2026.*
