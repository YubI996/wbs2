# 🛡️ WBS Kota Bontang - Whistle Blowing System

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.4">
  <img src="https://img.shields.io/badge/Filament-5.0-FDAE4B?style=for-the-badge&logo=filament&logoColor=white" alt="Filament 5">
  <img src="https://img.shields.io/badge/Livewire-3.0-FB70A9?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire 3">
</p>

Sistem Pelaporan Pengaduan (Whistle Blowing System) untuk Pemerintah Kota Bontang. Aplikasi ini memungkinkan masyarakat untuk melaporkan dugaan pelanggaran secara aman, rahasia, dan terpercaya.

## ✨ Fitur Utama

### 🌐 Public Pages
- **Landing Page** - Halaman utama dengan statistik dan informasi saluran pelaporan
- **Form Pelaporan** - Wizard 5 langkah untuk pengajuan aduan (5W+1H)
- **Cek Status Laporan** - Pantau status aduan dengan nomor registrasi
- **Multi-Channel** - Dukungan pelaporan via Website, WhatsApp, Instagram, SP4N LAPOR!

### 🔐 Panel Admin (Filament)
- **Dashboard** - Statistik dan grafik laporan
- **Manajemen User** - CRUD pengguna dengan role-based access
- **Manajemen Jenis Aduan** - Kategori pengaduan
- **Manajemen Aduan** - Kelola semua laporan masuk

### 👮 Panel Verifikator
- **Verifikasi Laporan** - Review dan verifikasi aduan baru
- **Timeline Aduan** - Catat progress penanganan

### 🕵️ Panel Inspektur
- **Investigasi Laporan** - Proses investigasi aduan terverifikasi
- **Penyelesaian** - Tutup kasus dengan catatan hasil

### 🔌 API Integration
- **SuperApps API** - Integrasi dengan aplikasi SuperApps
- **Auto-Registration** - Registrasi pelapor otomatis
- **Secure File Upload** - Validasi MIME type untuk mencegah fake extension

## 🛠️ Technology Stack

| Technology | Version | Purpose |
|------------|---------|---------|
| Laravel | 12.x | Backend Framework |
| PHP | 8.4+ | Runtime |
| Filament | 5.x | Admin Panel |
| Livewire | 3.x | Frontend Components |
| MySQL | 8.0+ | Database |
| Alpine.js | 3.x | JavaScript Framework |
| Tailwind CSS | 3.x | Styling |

## 📦 Installation

### Prerequisites
- PHP 8.4+
- Composer
- Node.js 18+
- MySQL 8.0+

### Steps

1. **Clone repository**
   ```bash
   git clone https://github.com/YubI996/wbs2.git
   cd wbs2
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Setup database**
   ```env
   DB_DATABASE=wbs_v2
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations & seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Create storage link**
   ```bash
   php artisan storage:link
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

8. **Start development server**
   ```bash
   php artisan serve
   npm run dev
   ```

## 👤 Default Users

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@bontangkota.go.id | password |
| Verifikator | verifikator@bontangkota.go.id | password |
| Inspektur | inspektur@bontangkota.go.id | password |

## 🔗 Routes

| Route | Description |
|-------|-------------|
| `/` | Landing Page |
| `/buat-laporan` | Form Pelaporan |
| `/cek-status` | Cek Status Laporan |
| `/login` | Login Page |
| `/admin` | Admin Panel |
| `/verifikator` | Verifikator Panel |
| `/inspektur` | Inspektur Panel |

## 📡 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/aduans` | Buat aduan baru |
| POST | `/api/aduans/status` | Cek status aduan |
| GET | `/api/jenis-aduans` | List jenis aduan |

> Dokumentasi API lengkap: [API_DOCUMENTATION.md](./API_DOCUMENTATION.md)

## 🔒 Security Features

- ✅ Role-Based Access Control (RBAC)
- ✅ CSRF Protection
- ✅ XSS Prevention
- ✅ Secure File Upload (MIME type validation)
- ✅ API Key Authentication
- ✅ Password Hashing (bcrypt)
- ✅ Encrypted Identity Data (untuk pelapor anonim)

## 📁 Project Structure

```
wbs-v2/
├── app/
│   ├── Enums/          # Status & Type Enums
│   ├── Filament/       # Admin Panels & Resources
│   ├── Http/           # Controllers & Middleware
│   ├── Livewire/       # Livewire Components
│   ├── Models/         # Eloquent Models
│   ├── Notifications/  # Email Notifications
│   └── Services/       # Business Logic Services
├── database/
│   ├── migrations/     # Database Migrations
│   └── seeders/        # Data Seeders
├── resources/
│   └── views/
│       ├── components/ # Blade Components
│       ├── emails/     # Email Templates
│       └── livewire/   # Livewire Views
└── routes/
    ├── web.php         # Web Routes
    └── api.php         # API Routes
```

## 🌐 Environment Variables

```env
# Application
APP_TIMEZONE=Asia/Makassar
APP_LOCALE=id

# API Key for SuperApps
WBS_API_KEY=your-secure-api-key

# reCAPTCHA (Optional)
RECAPTCHA_SITE_KEY=your-site-key
RECAPTCHA_SECRET_KEY=your-secret-key

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=wbs@bontangkota.go.id
MAIL_FROM_NAME="WBS Kota Bontang"
```

## 📄 License

This project is proprietary software developed for Pemerintah Kota Bontang.

## 👥 Contributors

- **Diskominfo Kota Bontang** - Development Team

---

<p align="center">
  <strong>WBS Kota Bontang</strong><br>
  <em>Sistem Pelaporan Pengaduan yang Aman, Rahasia, dan Terpercaya</em>
</p>
