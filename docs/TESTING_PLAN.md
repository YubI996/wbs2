# Rencana Testing Manual - WBS Kota Bontang

## Persiapan

### Akun yang Dibutuhkan
| Role | Email | Keterangan |
|------|-------|------------|
| Admin | (akun admin) | Akses penuh semua fitur |
| Verifikator | (akun verifikator) | Verifikasi & proses laporan |
| Inspektur | (akun inspektur) | Investigasi & penyelesaian |

### URL yang Digunakan
| Halaman | URL |
|---------|-----|
| Landing Page | `/` |
| Buat Laporan | `/buat-laporan` |
| Cek Status | `/cek-status` |
| Panel Admin | `/admin` |
| Panel Verifikator | `/verifikator` |
| Panel Inspektur | `/inspektur` |

---

## A. Halaman Publik (Tanpa Login)

### A1. Landing Page (`/`)
- [x] Halaman tampil dengan benar (logo, statistik, navigasi)
- [x] Statistik menampilkan angka: Total Laporan, Selesai, Dalam Proses, Kategori Aktif
- [x] Tombol "Buat Laporan" mengarah ke `/buat-laporan`
- [x] Tombol "Cek Status" mengarah ke `/cek-status`
- [x] Tampilan responsif di mobile

### A2. Buat Laporan - Wizard 5 Langkah (`/buat-laporan`)

**Langkah 1 - Identitas Pelapor:**
- [x] Isi nama, nomor HP, email → lanjut ke langkah 2[cat: nomor hp masih menerima huruf]
- [ ] Centang "Anonim" → field nama & HP tetap diisi tapi akan dienkripsi
- [ ] Centang "Notifikasi Email" → email wajib diisi
- [ ] Validasi: coba lanjut tanpa isi field wajib → muncul error
- [ ] Validasi: isi email format salah → muncul error

**Langkah 2 - Substansi Laporan:**
- [ ] Pilih kategori aduan dari dropdown (hanya kategori aktif yang tampil)
- [ ] Isi identitas terlapor
- [ ] Validasi: coba lanjut tanpa pilih kategori → muncul error

**Langkah 3 - Kronologi (5W+1H):**
- [o] Isi semua field: Apa, Siapa, Kapan (date picker), Dimana, Mengapa, Bagaimana[]
- [ ] Validasi: field wajib kosong → muncul error

**Langkah 4 - Bukti Pendukung:**
- [ ] Upload file PDF → berhasil
- [ ] Upload file gambar (JPG/PNG) → berhasil
- [ ] Upload file DOC/DOCX → berhasil
- [ ] Upload file > 10MB → ditolak dengan pesan error
- [ ] Upload file tipe tidak diizinkan (misal .exe) → ditolak
- [ ] Hapus file yang sudah diupload → file terhapus dari daftar
- [ ] Lanjut tanpa upload file (opsional) → berhasil

**Langkah 5 - Konfirmasi & Persetujuan:**
- [ ] Review ringkasan data yang diinput
- [ ] Centang persetujuan → tombol kirim aktif
- [ ] Kirim laporan → **CATAT nomor registrasi & password tracking**
- [ ] Format nomor registrasi: `WBS-YYYY-XXXXX`
- [ ] Jika notifikasi email dicentang → cek inbox email

**Navigasi Wizard:**
- [ ] Tombol "Kembali" berfungsi di setiap langkah
- [ ] Data tidak hilang saat kembali ke langkah sebelumnya
- [ ] Progress indicator menunjukkan langkah saat ini

### A3. Cek Status Laporan (`/cek-status`)
- [ ] Masukkan nomor registrasi + password tracking dari A2 → status tampil
- [ ] Tampil: kategori, status saat ini, timeline publik
- [ ] Masukkan nomor registrasi salah → pesan error
- [ ] Masukkan password salah → pesan error
- [ ] Timeline hanya menampilkan entry yang bersifat publik

---

## B. Login & Autentikasi

### B1. Halaman Login
- [x] Akses `/admin` tanpa login → redirect ke halaman login[halaman login salah, redirect ke halaman login utama]
- [x] Akses `/verifikator` tanpa login → redirect ke halaman login[halaman login salah, redirect ke halaman login utama]
- [x] Akses `/inspektur` tanpa login → redirect ke halaman login[halaman login salah, redirect ke halaman login utama]
- [x] Login dengan email & password benar → masuk ke dashboard
- [x] Login dengan password salah → pesan error "Email atau password salah"
- [x] Login dengan email tidak terdaftar → pesan error
- [] Login 5x gagal → rate limit (pesan throttle)
- [] Centang "Ingat saya" → session bertahan lebih lama

### B2. Akses Panel Sesuai Role
- [ ] Login sebagai Admin → bisa akses `/admin`
- [ ] Login sebagai Verifikator → bisa akses `/verifikator`, TIDAK bisa akses `/admin`
- [ ] Login sebagai Inspektur → bisa akses `/inspektur`, TIDAK bisa akses `/admin`
- [ ] Akses `/dashboard` → redirect otomatis ke panel sesuai role

### B3. Logout
- [ ] Klik logout → kembali ke halaman login
- [ ] Setelah logout, akses panel → redirect ke login

---

## C. Panel Admin (`/admin`)

### C1. Dashboard
- [ ] Statistik tampil: Total Laporan, Pending, Dalam Proses, Selesai
- [ ] Grafik bulanan tampil dengan benar (12 bulan)

### C2. Kelola Laporan (Aduan)
**Daftar:**
- [ ] Tabel menampilkan: No. Registrasi, Pelapor, Kategori, Status, Channel, Tanggal
- [ ] Filter berdasarkan Status → tabel terfilter
- [ ] Filter berdasarkan Kategori → tabel terfilter
- [ ] Filter berdasarkan Channel → tabel terfilter
- [ ] Pencarian berfungsi
- [ ] Pagination berfungsi

**Buat Laporan (dari Admin):**
- [ ] Klik tombol "Buat" → form muncul
- [ ] Isi semua field → laporan tersimpan
- [ ] Nomor registrasi ter-generate otomatis

**Lihat Detail:**
- [ ] Klik laporan → detail lengkap tampil
- [ ] Informasi pelapor tampil (jika anonim, admin bisa lihat identitas terdekripsi)
- [o] Kronologi 5W+1H tampil[pertanyaan tempat ditanyakan 2 kali]
- [ ] Bukti pendukung tampil
- [ ] Timeline status tampil

**Edit Laporan:**
- [ ] Edit field → perubahan tersimpan
- [ ] Ubah status → timeline baru tercatat

**Update Status:**
- [ ] Ubah status PENDING → VERIFIKASI → timeline tercatat
- [ ] Ubah status → PROSES → timeline tercatat
- [ ] Ubah status → INVESTIGASI → timeline tercatat
- [ ] Ubah status → SELESAI → timeline tercatat
- [ ] Ubah status → DITOLAK → wajib isi alasan

**Bukti Pendukung:**
- [ ] Download file → file terunduh
- [ ] Preview gambar → gambar tampil
- [ ] Hapus bukti → file terhapus

**Hapus Laporan:**
- [ ] Hapus laporan (soft delete) → masuk ke trash
- [ ] Restore laporan dari trash → laporan kembali

### C3. Kelola Kategori Aduan (Jenis Aduan)
- [ ] Daftar kategori tampil dengan jumlah laporan per kategori
- [ ] Buat kategori baru: isi kode, nama, deskripsi → tersimpan
- [ ] Edit kategori → perubahan tersimpan
- [ ] Non-aktifkan kategori → tidak muncul di form publik
- [ ] Aktifkan kembali → muncul lagi di form publik
- [ ] Hapus kategori (jika tidak ada laporan terkait)

### C4. Kelola Pelapor
- [ ] Daftar pelapor tampil
- [ ] Lihat detail: nama, HP, email, status anonim
- [ ] Edit data pelapor → tersimpan

### C5. Kelola User
- [ ] Daftar user tampil dengan role badge berwarna
- [ ] Buat user baru: isi nama, email, password, NIP, role → tersimpan
- [ ] Edit user: ubah data → tersimpan
- [ ] Filter berdasarkan role → terfilter
- [ ] Ubah role user → akses panel berubah sesuai role baru

---

## D. Panel Verifikator (`/verifikator`)

### D1. Dashboard
- [ ] Statistik role-specific tampil

### D2. Daftar Laporan
- [ ] Hanya tampil laporan dengan status: PENDING, VERIFIKASI, PROSES, DITOLAK
- [ ] Laporan status INVESTIGASI/SELESAI **TIDAK tampil**

### D3. Verifikasi Laporan
- [ ] Buka laporan PENDING → tombol "Verifikasi" tersedia
- [ ] Klik "Verifikasi" → status berubah ke VERIFIKASI, timeline tercatat
- [ ] Tombol "Verifikasi" **TIDAK muncul** untuk status selain PENDING

### D4. Proses Laporan
- [ ] Buka laporan VERIFIKASI → tombol "Proses" tersedia
- [ ] Klik "Proses" → status berubah ke PROSES, timeline tercatat

### D5. Tolak Laporan
- [ ] Buka laporan PENDING/VERIFIKASI → tombol "Tolak" tersedia
- [ ] Klik "Tolak" → form alasan muncul → isi alasan → status DITOLAK
- [ ] Tombol "Tolak" **TIDAK muncul** untuk status PROSES ke atas

### D6. Batasan Akses
- [ ] **TIDAK bisa** membuat laporan baru
- [ ] **TIDAK bisa** mengedit data laporan
- [ ] **TIDAK bisa** menghapus laporan
- [ ] **TIDAK bisa** mengakses menu User atau Kategori

---

## E. Panel Inspektur (`/inspektur`)

### E1. Dashboard
- [ ] Statistik investigasi tampil

### E2. Daftar Laporan
- [ ] Hanya tampil laporan dengan status: PROSES, INVESTIGASI, SELESAI
- [ ] Laporan status PENDING/VERIFIKASI/DITOLAK **TIDAK tampil**

### E3. Investigasi Laporan
- [ ] Buka laporan PROSES → tombol "Investigasi" tersedia
- [ ] Klik "Investigasi" → status berubah ke INVESTIGASI, timeline tercatat

### E4. Selesaikan Laporan
- [ ] Buka laporan PROSES/INVESTIGASI → tombol "Selesaikan" tersedia
- [ ] Klik "Selesaikan" → form kesimpulan muncul → isi → status SELESAI
- [ ] Timeline tercatat dengan kesimpulan

### E5. Batasan Akses
- [ ] **TIDAK bisa** membuat laporan baru
- [ ] **TIDAK bisa** mengedit data laporan
- [ ] **TIDAK bisa** menghapus laporan
- [ ] **TIDAK bisa** verifikasi atau tolak laporan

---

## F. Alur Lengkap End-to-End

### F1. Alur Normal (Laporan Selesai)
1. [ ] Buat laporan dari halaman publik → catat nomor & password
2. [ ] Cek status → status PENDING
3. [ ] Login Verifikator → verifikasi laporan → status VERIFIKASI
4. [ ] Cek status dari publik → status terupdate
5. [ ] Verifikator proses → status PROSES
6. [ ] Login Inspektur → investigasi → status INVESTIGASI
7. [ ] Inspektur selesaikan dengan kesimpulan → status SELESAI
8. [ ] Cek status dari publik → SELESAI dengan timeline lengkap

### F2. Alur Penolakan
1. [ ] Buat laporan → status PENDING
2. [ ] Login Verifikator → tolak dengan alasan
3. [ ] Cek status → DITOLAK dengan alasan

### F3. Alur Laporan Anonim
1. [ ] Buat laporan dengan centang Anonim
2. [ ] Login Admin → lihat detail → identitas terenkripsi bisa dilihat admin
3. [ ] Cek status publik → nama tampil sebagai "Anonim"

---

## G. Notifikasi Email

- [ ] Buat laporan dengan notifikasi email → email konfirmasi terkirim
- [ ] Update status (publik) → email notifikasi terkirim ke pelapor
- [ ] Buat laporan tanpa notifikasi → TIDAK ada email

---

## H. Keamanan

- [ ] Akses URL panel lain secara langsung (misal verifikator akses `/admin`) → ditolak/redirect
- [ ] Manipulasi URL bukti pendukung milik laporan lain → ditolak (403)
- [ ] Download bukti tanpa login → redirect ke login
- [ ] Cek status dengan password salah → tidak bisa lihat data
- [ ] Input HTML/script di form → tidak ter-render (XSS protection)
- [ ] reCAPTCHA v3 aktif di form publik (jika dikonfigurasi)

---

## I. Responsif & UI

- [ ] Landing page responsif di mobile/tablet
- [ ] Form buat laporan responsif
- [ ] Cek status responsif
- [ ] Panel admin/verifikator/inspektur responsif (sidebar collapse di mobile)
