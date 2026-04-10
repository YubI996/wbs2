<?php

namespace App\Http\Controllers\Api;

use App\Enums\AduanStatus;
use App\Enums\ReportChannel;
use App\Http\Controllers\Controller;
use App\Models\Aduan;
use App\Models\JenisAduan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatistikController extends Controller
{
    /**
     * Get comprehensive statistics data.
     * GET /api/statistik
     *
     * Query params:
     *   - tahun (int, optional): Filter by year. Default: current year.
     *   - bulan_trend (int, optional): Number of months for trend data. Default: 12.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'tahun'       => 'nullable|integer|min:2000|max:2100',
            'bulan_trend' => 'nullable|integer|min:1|max:24',
        ]);

        $tahun      = (int) ($request->query('tahun', now()->year));
        $bulanTrend = (int) ($request->query('bulan_trend', 12));

        $cacheKey = "api_statistik_{$tahun}_{$bulanTrend}";

        $data = Cache::remember($cacheKey, 300, function () use ($tahun, $bulanTrend) {
            return [
                'ringkasan'           => $this->getRingkasan($tahun),
                'per_status'          => $this->getPerStatus(),
                'per_channel'         => $this->getPerChannel(),
                'per_jenis_aduan'     => $this->getPerJenisAduan(),
                'tingkat_penyelesaian'=> $this->getTingkatPenyelesaian(),
                'trend_bulanan'       => $this->getTrendBulanan($bulanTrend),
            ];
        });

        return response()->json([
            'success'      => true,
            'data'         => $data,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Ringkasan jumlah laporan: total, hari ini, bulan ini, tahun tertentu.
     */
    private function getRingkasan(int $tahun): array
    {
        return [
            'total'      => Aduan::count(),
            'hari_ini'   => Aduan::whereDate('created_at', today())->count(),
            'bulan_ini'  => Aduan::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'tahun'      => $tahun,
            'tahun_ini'  => Aduan::whereYear('created_at', $tahun)->count(),
        ];
    }

    /**
     * Jumlah laporan per status.
     */
    private function getPerStatus(): array
    {
        $counts = Aduan::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $result = [];
        foreach (AduanStatus::cases() as $status) {
            $result[$status->value] = [
                'label' => $status->label(),
                'total' => (int) ($counts[$status->value] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Jumlah laporan per channel.
     */
    private function getPerChannel(): array
    {
        $counts = Aduan::query()
            ->select('channel', DB::raw('count(*) as total'))
            ->groupBy('channel')
            ->pluck('total', 'channel')
            ->toArray();

        $result = [];
        foreach (ReportChannel::cases() as $channel) {
            $result[$channel->value] = [
                'label' => $channel->label(),
                'total' => (int) ($counts[$channel->value] ?? 0),
            ];
        }

        return $result;
    }

    /**
     * Jumlah laporan per jenis aduan (hanya yang aktif, diurutkan terbanyak).
     */
    private function getPerJenisAduan(): array
    {
        return JenisAduan::query()
            ->where('is_active', true)
            ->withCount('aduans')
            ->orderByDesc('aduans_count')
            ->get()
            ->map(fn ($j) => [
                'slug'  => $j->slug,
                'nama'  => $j->name,
                'total' => $j->aduans_count,
            ])
            ->toArray();
    }

    /**
     * Persentase laporan yang selesai dari total laporan yang ditutup
     * (selesai + ditolak).
     */
    private function getTingkatPenyelesaian(): array
    {
        $selesai = Aduan::where('status', AduanStatus::SELESAI)->count();
        $ditolak = Aduan::where('status', AduanStatus::DITOLAK)->count();
        $total   = Aduan::count();
        $ditutup = $selesai + $ditolak;

        return [
            'selesai'              => $selesai,
            'ditolak'              => $ditolak,
            'total_ditutup'        => $ditutup,
            'total'                => $total,
            'persentase_selesai'   => $ditutup > 0 ? round($selesai / $ditutup * 100, 2) : 0,
            'persentase_dari_total'=> $total > 0 ? round($selesai / $total * 100, 2) : 0,
        ];
    }

    /**
     * Trend laporan per bulan untuk N bulan terakhir.
     */
    private function getTrendBulanan(int $nBulan): array
    {
        // Generate list of month periods from N months ago until now
        $periods = collect();
        for ($i = $nBulan - 1; $i >= 0; $i--) {
            $periods->push(now()->startOfMonth()->subMonths($i));
        }

        // Aggregate by year-month from DB
        $rows = Aduan::query()
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as periode"),
                DB::raw('count(*) as total'),
                DB::raw("sum(case when status = 'selesai' then 1 else 0 end) as selesai"),
                DB::raw("sum(case when status = 'ditolak' then 1 else 0 end) as ditolak"),
                DB::raw("sum(case when status = 'pending' then 1 else 0 end) as pending"),
            )
            ->where('created_at', '>=', now()->startOfMonth()->subMonths($nBulan - 1))
            ->groupBy('periode')
            ->orderBy('periode')
            ->get()
            ->keyBy('periode');

        return $periods->map(function ($date) use ($rows) {
            $key = $date->format('Y-m');
            $row = $rows->get($key);

            return [
                'periode' => $key,
                'label'   => $date->translatedFormat('M Y'),
                'total'   => $row ? (int) $row->total   : 0,
                'selesai' => $row ? (int) $row->selesai : 0,
                'ditolak' => $row ? (int) $row->ditolak : 0,
                'pending' => $row ? (int) $row->pending : 0,
            ];
        })->values()->toArray();
    }
}
