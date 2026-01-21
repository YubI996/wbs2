<?php

namespace App\Filament\Widgets;

use App\Enums\AduanStatus;
use App\Models\Aduan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    
    protected function getStats(): array
    {
        // Cache stats for 5 minutes for performance
        $stats = Cache::remember('admin_stats', 300, function () {
            return [
                'total' => Aduan::count(),
                'pending' => Aduan::where('status', AduanStatus::PENDING)->count(),
                'proses' => Aduan::whereIn('status', [
                    AduanStatus::VERIFIKASI,
                    AduanStatus::PROSES,
                    AduanStatus::INVESTIGASI,
                ])->count(),
                'selesai' => Aduan::where('status', AduanStatus::SELESAI)->count(),
                'ditolak' => Aduan::where('status', AduanStatus::DITOLAK)->count(),
                'today' => Aduan::whereDate('created_at', today())->count(),
                'this_month' => Aduan::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ];
        });
        
        return [
            Stat::make('Total Laporan', $stats['total'])
                ->description('Semua laporan')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),
                
            Stat::make('Menunggu Verifikasi', $stats['pending'])
                ->description('Perlu ditindaklanjuti')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
                
            Stat::make('Sedang Diproses', $stats['proses'])
                ->description('Dalam penanganan')
                ->descriptionIcon('heroicon-o-cog')
                ->color('info'),
                
            Stat::make('Selesai', $stats['selesai'])
                ->description('Telah diselesaikan')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
