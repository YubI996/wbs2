<?php

namespace App\Filament\Widgets;

use App\Models\Aduan;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MonthlyReportsChart extends ChartWidget
{
    protected static ?string $heading = 'Laporan per Bulan';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Cache chart data for 10 minutes
        $data = Cache::remember('monthly_reports_chart', 600, function () {
            $reports = Aduan::select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('COUNT(*) as total')
                )
                ->whereYear('created_at', now()->year)
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy('month')
                ->pluck('total', 'month')
                ->toArray();
            
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
            $values = [];
            
            for ($i = 1; $i <= 12; $i++) {
                $values[] = $reports[$i] ?? 0;
            }
            
            return [
                'labels' => $months,
                'values' => $values,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Laporan ' . now()->year,
                    'data' => $data['values'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
