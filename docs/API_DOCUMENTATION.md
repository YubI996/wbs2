# API Documentation — WBS v2

## Overview

API untuk integrasi aplikasi eksternal (SuperApps, dashboard publik, dll.) dengan sistem **Whistle Blowing System (WBS) Kota Bontang**.

**Base URL:** `https://wbs.bontangkota.go.id/api`

**Version:** 2.1

**Last Updated:** April 2026

---

## Daftar Isi

1. [Authentication](#authentication)
2. [Endpoints](#endpoints)
   - [GET /jenis-aduans](#1-get-jenis-aduan-public)
   - [GET /statistik](#2-get-statistik-public)
   - [POST /aduans](#3-create-aduan-protected)
   - [POST /aduans/status](#4-cek-status-aduan-protected)
3. [Status Reference](#status-reference)
4. [Error Responses](#error-responses)
5. [Rate Limiting](#rate-limiting)
6. [Security Notes](#security-notes)

---

## Authentication

Endpoint **protected** memerlukan API Key di setiap request.

### Header

```
X-API-Key: <your-api-key>
```

### Response Error Autentikasi

**401 Unauthorized** — API Key tidak dikirim:
```json
{
  "success": false,
  "message": "API key tidak ditemukan. Sertakan header X-API-Key."
}
```

**403 Forbidden** — API Key tidak valid:
```json
{
  "success": false,
  "message": "API key tidak valid."
}
```

---

## Endpoints

### 1. Get Jenis Aduan (Public)

Mendapatkan daftar kategori aduan yang aktif.

**`GET /api/jenis-aduans`**

**Authentication:** Tidak diperlukan

#### Response 200

```json
{
  "success": true,
  "data": [
    {
      "slug": "pelanggaran-disiplin",
      "name": "Pelanggaran Disiplin Pegawai",
      "description": "Pelanggaran terkait disiplin kerja PNS"
    },
    {
      "slug": "penyalahgunaan-wewenang",
      "name": "Penyalahgunaan Wewenang",
      "description": null
    }
  ]
}
```

#### Contoh Request

```bash
curl https://wbs.bontangkota.go.id/api/jenis-aduans
```

---

### 2. Get Statistik (Public)

Mendapatkan data statistik laporan secara agregat. Cocok untuk dashboard publik atau monitoring.

**`GET /api/statistik`**

**Authentication:** Tidak diperlukan

**Cache:** Response di-cache selama **5 menit** di server.

#### Query Parameters

| Parameter | Type | Default | Keterangan |
|-----------|------|---------|------------|
| `tahun` | integer | tahun berjalan | Filter ringkasan per tahun (2000–2100) |
| `bulan_trend` | integer | `12` | Jumlah bulan untuk data trend (1–24) |

#### Contoh Request

```bash
# Data statistik default (tahun ini, 12 bulan trend)
curl https://wbs.bontangkota.go.id/api/statistik

# Filter tahun 2025, trend 6 bulan terakhir
curl "https://wbs.bontangkota.go.id/api/statistik?tahun=2025&bulan_trend=6"
```

#### Response 200

```json
{
  "success": true,
  "data": {
    "ringkasan": {
      "total": 150,
      "hari_ini": 3,
      "bulan_ini": 20,
      "tahun": 2026,
      "tahun_ini": 80
    },
    "per_status": {
      "pending": {
        "label": "Menunggu Verifikasi",
        "total": 20
      },
      "verifikasi": {
        "label": "Sedang Diverifikasi",
        "total": 15
      },
      "proses": {
        "label": "Dalam Proses",
        "total": 10
      },
      "investigasi": {
        "label": "Dalam Investigasi",
        "total": 5
      },
      "selesai": {
        "label": "Selesai",
        "total": 80
      },
      "ditolak": {
        "label": "Ditolak",
        "total": 20
      }
    },
    "per_channel": {
      "website": {
        "label": "Website",
        "total": 90
      },
      "whatsapp": {
        "label": "WhatsApp",
        "total": 30
      },
      "instagram": {
        "label": "Instagram DM",
        "total": 10
      },
      "sp4n": {
        "label": "SP4N LAPOR!",
        "total": 5
      },
      "superapps": {
        "label": "SuperApps",
        "total": 15
      }
    },
    "per_jenis_aduan": [
      {
        "slug": "pelanggaran-disiplin",
        "nama": "Pelanggaran Disiplin Pegawai",
        "total": 60
      },
      {
        "slug": "penyalahgunaan-wewenang",
        "nama": "Penyalahgunaan Wewenang",
        "total": 40
      }
    ],
    "tingkat_penyelesaian": {
      "selesai": 80,
      "ditolak": 20,
      "total_ditutup": 100,
      "total": 150,
      "persentase_selesai": 80.0,
      "persentase_dari_total": 53.33
    },
    "trend_bulanan": [
      {
        "periode": "2025-05",
        "label": "Mei 2025",
        "total": 10,
        "selesai": 7,
        "ditolak": 1,
        "pending": 2
      },
      {
        "periode": "2025-06",
        "label": "Jun 2025",
        "total": 14,
        "selesai": 9,
        "ditolak": 2,
        "pending": 3
      }
    ]
  },
  "generated_at": "2026-04-10T08:00:00+08:00"
}
```

#### Penjelasan Field

**`ringkasan`**

| Field | Keterangan |
|-------|------------|
| `total` | Total seluruh laporan sepanjang waktu |
| `hari_ini` | Laporan masuk hari ini |
| `bulan_ini` | Laporan masuk bulan berjalan |
| `tahun` | Tahun yang difilter (dari query param `tahun`) |
| `tahun_ini` | Laporan masuk pada tahun tersebut |

**`tingkat_penyelesaian`**

| Field | Keterangan |
|-------|------------|
| `persentase_selesai` | `selesai / (selesai + ditolak) × 100` — dari laporan yang sudah ditutup |
| `persentase_dari_total` | `selesai / total × 100` — dari seluruh laporan |

**`trend_bulanan`** — diurutkan dari bulan terlama ke terbaru

| Field | Keterangan |
|-------|------------|
| `periode` | Format `YYYY-MM` |
| `label` | Nama bulan dan tahun dalam Bahasa Indonesia |
| `total` | Total laporan masuk pada bulan tersebut |
| `selesai` | Laporan berstatus selesai saat data diambil, dibuat bulan ini |
| `ditolak` | Laporan berstatus ditolak saat data diambil, dibuat bulan ini |
| `pending` | Laporan berstatus pending saat data diambil, dibuat bulan ini |

#### Response Error 422

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "tahun": ["The tahun field must be between 2000 and 2100."],
    "bulan_trend": ["The bulan trend field must be between 1 and 24."]
  }
}
```

---

### 3. Create Aduan (Protected)

Membuat laporan/aduan baru dari SuperApps.

**`POST /api/aduans`**

**Content-Type:** `multipart/form-data`

**Authentication:** Required (`X-API-Key` header)

#### Request Parameters

| Parameter | Type | Required | Keterangan |
|-----------|------|----------|------------|
| `email` | string | Ya | Email pelapor |
| `nama` | string | Ya | Nama lengkap pelapor |
| `phone` | string | Tidak | Nomor telepon |
| `nik` | string | Tidak | NIK (untuk masyarakat umum) |
| `nip` | string | Tidak | NIP (untuk ASN) |
| `jenis_aduan` | string | Ya | Slug jenis aduan (dari endpoint `/jenis-aduans`) |
| `identitas_terlapor` | string | Ya | Identitas pihak yang dilaporkan (max 500 karakter) |
| `what` | string | Ya | Apa yang terjadi (max 5.000 karakter) |
| `who` | string | Ya | Siapa yang terlibat (max 500 karakter) |
| `when_date` | date | Ya | Tanggal kejadian format `YYYY-MM-DD` |
| `where_location` | string | Ya | Lokasi kejadian (max 500 karakter) |
| `why` | string | Tidak | Mengapa hal ini terjadi (max 2.000 karakter) |
| `how` | string | Tidak | Kronologis kejadian (max 2.000 karakter) |
| `lokasi_kejadian` | string | Ya | Alamat lengkap lokasi (max 500 karakter) |
| `file_bukti[]` | file | Tidak | File bukti (maks 5 file, maks 10 MB per file) |

#### Spesifikasi File Upload

**Tipe file yang diizinkan:**

| Kategori | Ekstensi |
|----------|----------|
| Gambar | jpg, jpeg, png, gif, webp |
| Dokumen | pdf, doc, docx, xls, xlsx, txt |
| Video | mp4, mov, avi, mkv, webm |
| Audio | mp3, m4a, wav |

> **Catatan:** Server mendeteksi tipe file berdasarkan isi file (MIME type), bukan ekstensi. File dengan ekstensi palsu akan **ditolak**.

**Batas:** Maks 5 file, maks 10 MB per file.

#### Contoh Request

```bash
curl -X POST https://wbs.bontangkota.go.id/api/aduans \
  -H "X-API-Key: <your-api-key>" \
  -F "email=pelapor@example.com" \
  -F "nama=John Doe" \
  -F "phone=081234567890" \
  -F "jenis_aduan=pelanggaran-disiplin" \
  -F "identitas_terlapor=Kepala Bagian X, Dinas Y" \
  -F "what=Terjadi penyalahgunaan anggaran pada kegiatan pengadaan barang..." \
  -F "who=Kepala Bagian X beserta bendahara" \
  -F "when_date=2026-01-15" \
  -F "where_location=Kantor Dinas Y" \
  -F "why=Diduga untuk kepentingan pribadi" \
  -F "how=Kronologis lengkap kejadian..." \
  -F "lokasi_kejadian=Jl. Contoh No. 123, Bontang" \
  -F "file_bukti[]=@/path/to/bukti1.pdf" \
  -F "file_bukti[]=@/path/to/foto.jpg"
```

#### Response 201 — Berhasil

```json
{
  "success": true,
  "data": {
    "id": 123,
    "nomor_registrasi": "WBS-2026-00001",
    "tracking_password": "Ab3xK9pQ",
    "status": "pending",
    "status_label": "Menunggu Verifikasi",
    "pelapor_id": 45,
    "files_uploaded": 2,
    "created_at": "2026-01-21T08:30:00+08:00"
  },
  "message": "Aduan berhasil disimpan."
}
```

> **PENTING:** Simpan `nomor_registrasi` dan `tracking_password` — keduanya diperlukan untuk mengecek status laporan.

#### Response Error 422 — Validasi Gagal

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Email wajib diisi."],
    "jenis_aduan": ["Jenis aduan tidak valid."],
    "file_bukti.0": ["Ekstensi file tidak sesuai dengan isi file (ekstensi palsu terdeteksi)."],
    "file_bukti": ["Maksimal 5 file bukti."]
  }
}
```

#### Response Error 500

```json
{
  "success": false,
  "message": "Gagal menyimpan aduan. Silakan coba lagi."
}
```

---

### 4. Cek Status Aduan (Protected)

Mengecek status laporan berdasarkan nomor registrasi dan password tracking.

**`POST /api/aduans/status`**

**Content-Type:** `application/json`

**Authentication:** Required (`X-API-Key` header)

#### Request Body

```json
{
  "nomor_registrasi": "WBS-2026-00001",
  "tracking_password": "Ab3xK9pQ"
}
```

| Field | Type | Required | Keterangan |
|-------|------|----------|------------|
| `nomor_registrasi` | string | Ya | Nomor registrasi dari response create aduan |
| `tracking_password` | string | Ya | Password tracking dari response create aduan |

#### Contoh Request

```bash
curl -X POST https://wbs.bontangkota.go.id/api/aduans/status \
  -H "X-API-Key: <your-api-key>" \
  -H "Content-Type: application/json" \
  -d '{
    "nomor_registrasi": "WBS-2026-00001",
    "tracking_password": "Ab3xK9pQ"
  }'
```

#### Response 200 — Berhasil

```json
{
  "success": true,
  "data": {
    "nomor_registrasi": "WBS-2026-00001",
    "status": "verifikasi",
    "status_label": "Sedang Diverifikasi",
    "jenis_aduan": "Pelanggaran Disiplin Pegawai",
    "created_at": "2026-01-21T08:30:00+08:00",
    "timeline": [
      {
        "status": "verifikasi",
        "komentar": "Laporan sedang diverifikasi oleh tim",
        "tanggal": "2026-01-22T09:00:00+08:00"
      },
      {
        "status": "pending",
        "komentar": "Laporan diterima melalui SuperApps",
        "tanggal": "2026-01-21T08:30:00+08:00"
      }
    ]
  }
}
```

> **Catatan:** `timeline` hanya menampilkan riwayat yang bersifat publik. Catatan internal tidak ditampilkan.

#### Response Error 401 — Password Salah

```json
{
  "success": false,
  "message": "Password tracking tidak valid."
}
```

#### Response Error 404 — Tidak Ditemukan

```json
{
  "success": false,
  "message": "Aduan tidak ditemukan."
}
```

---

## Status Reference

| Status | Label | Deskripsi |
|--------|-------|-----------|
| `pending` | Menunggu Verifikasi | Laporan baru masuk, belum diproses |
| `verifikasi` | Sedang Diverifikasi | Verifikator sedang memeriksa kelengkapan |
| `proses` | Dalam Proses | Sedang ditangani oleh tim |
| `investigasi` | Dalam Investigasi | Tahap investigasi lapangan |
| `selesai` | Selesai | Laporan telah selesai ditangani |
| `ditolak` | Ditolak | Laporan tidak memenuhi syarat atau tidak terbukti |

### Alur Status

```
pending → verifikasi → proses → investigasi → selesai
   └── ditolak          └── ditolak
```

---

## Error Responses

### Format Umum

Semua error mengikuti format berikut:

```json
{
  "success": false,
  "message": "Pesan error."
}
```

Error validasi (422) menggunakan format Laravel standar:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Pesan error untuk field ini."]
  }
}
```

### HTTP Status Codes

| Code | Keterangan |
|------|------------|
| `200` | OK — Request berhasil |
| `201` | Created — Resource berhasil dibuat |
| `401` | Unauthorized — API key tidak dikirim atau password salah |
| `403` | Forbidden — API key tidak valid |
| `404` | Not Found — Resource tidak ditemukan |
| `422` | Unprocessable Entity — Validasi gagal |
| `429` | Too Many Requests — Rate limit terlampaui |
| `500` | Internal Server Error — Kesalahan server |

---

## Rate Limiting

- **Limit:** 60 request per menit per API Key
- **Header Response:**

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
```

- **Response 429:**

```json
{
  "message": "Too Many Attempts."
}
```

---

## Security Notes

1. **File Validation:** Semua file divalidasi menggunakan deteksi MIME type berbasis isi file, bukan ekstensi. File dengan ekstensi palsu (misalnya `.jpg` berisi executable) akan ditolak.

2. **API Key:** Simpan API key di server-side. Jangan expose di kode client-side atau mobile app secara langsung.

3. **HTTPS Only:** Selalu gunakan HTTPS. Request via HTTP dapat diredirect atau ditolak.

4. **Tracking Password:** Password tracking di-hash di database (bcrypt). Tidak dapat dipulihkan jika hilang — gunakan nomor registrasi untuk identifikasi laporan di sistem internal.

5. **Data Pelapor Anonim:** Identitas pelapor anonim dienkripsi di database dan tidak dikembalikan di response API manapun.
