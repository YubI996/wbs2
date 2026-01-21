<x-mail::message>
# Update Status Laporan

Status laporan Anda telah diperbarui.

## Informasi Laporan

**Nomor Registrasi:** {{ $aduan->nomor_registrasi }}

**Status Baru:** <strong>{{ $statusLabel }}</strong>

@if($komentar)
## Catatan dari Pengelola

<x-mail::panel>
{{ $komentar }}
</x-mail::panel>
@endif

## Detail Laporan

- **Kategori:** {{ $aduan->jenisAduan->name }}
- **Tanggal Lapor:** {{ $aduan->created_at->format('d F Y H:i') }}

<x-mail::button :url="$url">
Cek Status Lengkap
</x-mail::button>

Terima kasih atas kesabaran Anda menunggu proses penanganan laporan.

Salam,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Jika Anda memiliki pertanyaan, silakan hubungi kami melalui email wbs@bontangkota.go.id
</x-mail::subcopy>
</x-mail::message>
