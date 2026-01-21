# API Documentation - WBS v2 SuperApps Integration

## Overview

API untuk integrasi aplikasi SuperApps dengan sistem Whistle Blowing System (WBS) Kota Bontang.

**Base URL:** `https://wbs.bontangkota.go.id/api`

**Version:** 2.0

**Last Updated:** Januari 2026

---

## Authentication

Semua request ke endpoint protected harus menyertakan API Key di header.

### Header Required:
```
X-API-Key: sk_live_wbs_superapps_2026
```

### Response Error (401):
```json
{
  "success": false,
  "message": "API key tidak ditemukan. Sertakan header X-API-Key."
}
```

---

## Endpoints

### 1. Get Jenis Aduan (Public)

Mendapatkan daftar kategori aduan yang tersedia.

**Endpoint:** `GET /api/jenis-aduans`

**Authentication:** Tidak diperlukan

#### Response:
```json
{
  "success": true,
  "data": [
    {
      "slug": 1,
      "name": "Pelanggaran Disiplin Pegawai",
      "description": "Pelanggaran terkait disiplin kerja PNS"
    },
    {
      "slug": 2,
      "name": "Penyalahgunaan Wewenang",
      "description": null
    }
  ]
}
```

---

### 2. Create Aduan (Protected)

Membuat aduan baru dari SuperApps.

**Endpoint:** `POST /api/aduans`

**Content-Type:** `multipart/form-data`

**Authentication:** Required (X-API-Key header)

#### Request Parameters:

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| email | string | Ya | Email pelapor |
| nama | string | Ya | Nama lengkap pelapor |
| phone | string | Tidak | Nomor telepon |
| nik | string | Tidak | NIK (untuk masyarakat umum) |
| nip | string | Tidak | NIP (untuk ASN) |
| jenis_aduan | integer | Ya | ID/slug jenis aduan |
| identitas_terlapor | string | Ya | Identitas pihak yang dilaporkan |
| what | string | Ya | Apa yang terjadi (max 5000 karakter) |
| who | string | Ya | Siapa yang terlibat |
| when_date | date | Ya | Tanggal kejadian (YYYY-MM-DD) |
| where_location | string | Ya | Lokasi kejadian |
| why | string | Tidak | Mengapa hal ini terjadi |
| how | string | Tidak | Bagaimana kronologis kejadian |
| lokasi_kejadian | string | Ya | Alamat lengkap lokasi |
| file_bukti[] | file | Tidak | File bukti (max 5 file, masing-masing max 10MB) |

#### File Upload Specifications:

**Allowed MIME Types:**
- Images: jpg, jpeg, png, gif, webp
- Documents: pdf, doc, docx, xls, xlsx, txt
- Videos: mp4, mov, avi, mkv, webm
- Audio: mp3, m4a, wav

**Max File Size:** 10 MB per file

**Max Files:** 5 files

**Security:** File divalidasi berdasarkan MIME type, bukan ekstensi. Ekstensi palsu akan terdeteksi.

#### Example Request (cURL):
```bash
curl -X POST https://wbs.bontangkota.go.id/api/aduans \
  -H "X-API-Key: sk_live_wbs_superapps_2026" \
  -F "email=pelapor@example.com" \
  -F "nama=John Doe" \
  -F "phone=081234567890" \
  -F "jenis_aduan=1" \
  -F "identitas_terlapor=Kepala Bagian X, Dinas Y" \
  -F "what=Terjadi penyalahgunaan anggaran..." \
  -F "who=Kepala Bagian X" \
  -F "when_date=2026-01-15" \
  -F "where_location=Kantor Dinas Y" \
  -F "why=Diduga untuk kepentingan pribadi" \
  -F "how=Kronologis lengkap..." \
  -F "lokasi_kejadian=Jl. Contoh No. 123, Bontang" \
  -F "file_bukti[]=@/path/to/bukti1.pdf" \
  -F "file_bukti[]=@/path/to/bukti2.jpg"
```

#### Success Response (201):
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

> ⚠️ **PENTING:** Simpan `nomor_registrasi` dan `tracking_password` untuk cek status!

---

### 3. Check Status Aduan (Protected)

Mengecek status aduan berdasarkan nomor registrasi.

**Endpoint:** `POST /api/aduans/status`

**Content-Type:** `application/json`

**Authentication:** Required (X-API-Key header)

#### Request Body:
```json
{
  "nomor_registrasi": "WBS-2026-00001",
  "tracking_password": "Ab3xK9pQ"
}
```

#### Success Response (200):
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

---

## Status Reference

| Status | Label | Deskripsi |
|--------|-------|-----------|
| pending | Menunggu Verifikasi | Laporan baru masuk |
| verifikasi | Sedang Diverifikasi | Dalam proses verifikasi |
| proses | Dalam Proses | Sedang ditangani |
| investigasi | Dalam Investigasi | Tahap investigasi |
| selesai | Selesai | Laporan selesai |
| ditolak | Ditolak | Laporan ditolak |

---

## Error Responses

### Validation Error (422):
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["Email wajib diisi."],
    "jenis_aduan": ["Jenis aduan tidak valid."],
    "file_bukti.0": ["Ekstensi file tidak sesuai dengan isi file (ekstensi palsu terdeteksi)."]
  }
}
```

### Not Found (404):
```json
{
  "success": false,
  "message": "Aduan tidak ditemukan."
}
```

### Server Error (500):
```json
{
  "success": false,
  "message": "Gagal menyimpan aduan. Silakan coba lagi."
}
```

---

## Rate Limiting

- **Limit:** 60 requests per minute per API key
- **Response:** `429 Too Many Requests`

---

## Security Notes

1. **File Validation:** Semua file divalidasi menggunakan MIME type detection, bukan ekstensi. Ekstensi palsu akan terdeteksi dan ditolak.

2. **API Key:** Simpan API key dengan aman, jangan expose di client-side code.

3. **HTTPS Only:** Selalu gunakan HTTPS.

---

## Support

- **Email:** inspektoratdaerah@bontangkota.go.id
- **Website:** https://wbs.bontangkota.go.id
