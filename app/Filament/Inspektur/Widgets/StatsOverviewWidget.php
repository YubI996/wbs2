<?php

namespace App\Filament\Inspektur\Widgets;

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
        $stats = Cache::remember('inspektur_stats', 300, function () {
            return [
                'proses' => Aduan::where('status', AduanStatus::PROSES)->count(),
                'investigasi' => Aduan::where('status', AduanStatus::INVESTIGASI)->count(),
                'selesai' => Aduan::where('status', AduanStatus::SELESAI)->count(),
            ];
        });
        
        return [
            Stat::make('Menunggu Investigasi', $stats['proses'])
                ->description('Siap diinvestigasi')
                ->descriptionIcon('heroicon-o-cog')
                ->color('warning'),
                
            Stat::make('Dalam Investigasi', $stats['investigasi'])
                ->description('Sedang ditangani')
                ->descriptionIcon('heroicon-o-magnifying-glass')
                ->color('info'),
                
            Stat::make('Selesai', $stats['selesai'])
                ->description('Telah diselesaikan')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
