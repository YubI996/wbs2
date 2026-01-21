<?php

namespace App\Filament\Verifikator\Widgets;

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
        $stats = Cache::remember('verifikator_stats', 300, function () {
            return [
                'pending' => Aduan::where('status', AduanStatus::PENDING)->count(),
                'verifikasi' => Aduan::where('status', AduanStatus::VERIFIKASI)->count(),
                'proses' => Aduan::where('status', AduanStatus::PROSES)->count(),
            ];
        });
        
        return [
            Stat::make('Menunggu Verifikasi', $stats['pending'])
                ->description('Perlu ditindaklanjuti')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
                
            Stat::make('Sedang Diverifikasi', $stats['verifikasi'])
                ->description('Dalam proses verifikasi')
                ->descriptionIcon('heroicon-o-magnifying-glass')
                ->color('info'),
                
            Stat::make('Dalam Proses', $stats['proses'])
                ->description('Siap investigasi')
                ->descriptionIcon('heroicon-o-cog')
                ->color('primary'),
        ];
    }
}
