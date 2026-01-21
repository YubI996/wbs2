<?php

namespace App\Livewire;

use App\Models\Aduan;
use App\Models\JenisAduan;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class LandingPage extends Component
{
    public array $stats = [];
    
    public function mount(): void
    {
        $this->loadStats();
    }
    
    protected function loadStats(): void
    {
        // Cache stats for 5 minutes for performance
        $this->stats = Cache::remember('landing_stats', 300, function () {
            return [
                'total_laporan' => Aduan::count(),
                'laporan_selesai' => Aduan::where('status', 'selesai')->count(),
                'dalam_proses' => Aduan::whereNotIn('status', ['selesai', 'ditolak'])->count(),
                'kategori' => JenisAduan::active()->count(),
            ];
        });
    }
    
    public function render()
    {
        return view('livewire.landing-page')
            ->layout('components.layouts.guest', ['title' => 'WBS Kota Bontang - Whistle Blowing System']);
    }
}
