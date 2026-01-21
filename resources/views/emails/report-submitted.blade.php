<x-mail::message>
# Laporan Berhasil Dikirim

Terima kasih telah melaporkan! Laporan Anda telah berhasil dikirim dan akan segera diproses oleh tim kami.

## Informasi Laporan

**Nomor Registrasi:** {{ $aduan->nomor_registrasi }}

**Password Tracking:** {{ $trackingPassword }}

<x-mail::panel>
**Penting:** Simpan nomor registrasi dan password di atas untuk memantau status laporan Anda.
</x-mail::panel>

## Detail Laporan

- **Kategori:** {{ $aduan->jenisAduan->name }}
- **Tanggal Lapor:** {{ $aduan->created_at->format('d F Y H:i') }}
- **Status:** {{ $aduan->status->label() }}

<x-mail::button :url="$url">
Cek Status Laporan
</x-mail::button>

Terima kasih atas keberanian Anda untuk melaporkan. Laporan Anda sangat berharga untuk mewujudkan pemerintahan yang bersih dan transparan.

Salam,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Jika Anda memiliki pertanyaan, silakan hubungi kami melalui email wbs@bontangkota.go.id
</x-mail::subcopy>
</x-mail::message>
