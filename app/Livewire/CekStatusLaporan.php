<?php

namespace App\Livewire;

use App\Enums\AduanStatus;
use App\Models\Aduan;
use Livewire\Component;

class CekStatusLaporan extends Component
{
    public string $nomor_registrasi = '';
    public string $password = '';
    
    public ?Aduan $aduan = null;
    public bool $searched = false;
    public ?string $error = null;
    
    public function rules(): array
    {
        return [
            'nomor_registrasi' => 'required|string',
            'password' => 'required|string',
        ];
    }
    
    public function messages(): array
    {
        return [
            'nomor_registrasi.required' => 'Nomor registrasi wajib diisi',
            'password.required' => 'Password wajib diisi',
        ];
    }
    
    public function search(): void
    {
        $this->validate();
        
        $this->searched = true;
        $this->error = null;
        $this->aduan = null;
        
        // Find aduan by registration number
        $aduan = Aduan::with(['jenisAduan', 'publicTimelines', 'buktiPendukungs'])
            ->where('nomor_registrasi', strtoupper(trim($this->nomor_registrasi)))
            ->first();
        
        if (!$aduan) {
            $this->error = 'Laporan tidak ditemukan. Pastikan nomor registrasi benar.';
            return;
        }
        
        // Verify password
        if (!$aduan->verifyTrackingPassword($this->password)) {
            $this->error = 'Password salah. Silakan coba lagi.';
            return;
        }
        
        $this->aduan = $aduan;
    }
    
    public function resetSearch(): void
    {
        $this->nomor_registrasi = '';
        $this->password = '';
        $this->aduan = null;
        $this->searched = false;
        $this->error = null;
    }
    
    public function render()
    {
        return view('livewire.cek-status-laporan')
            ->layout('components.layouts.guest', ['title' => 'Cek Status Laporan - WBS Kota Bontang']);
    }
}
