<?php

namespace App\Http\Controllers;

use App\Models\DashboardStat;
use App\Models\StatistikHarian;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function getStatistics(Request $request)
    {
        $tokoId = Auth::user()->toko->id;
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        // Get or create monthly stats
        $stats = DashboardStat::firstOrCreate(
            [
                'toko_id' => $tokoId,
                'bulan' => $month,
                'tahun' => $year,
            ],
            [
                'total_pendapatan' => 0,
                'jumlah_transaksi' => 0,
                'jumlah_menu' => 0,
                'jumlah_kategori' => 0,
            ]
        );

        // Update stats from current data
        $this->updateMonthlyStats($tokoId, $month, $year, $stats);

        return response()->json([
            'success' => true,
            'statistics' => [
                'total_pendapatan' => $stats->total_pendapatan,
                'jumlah_transaksi' => $stats->jumlah_transaksi,
                'jumlah_menu' => $stats->jumlah_menu,
                'jumlah_kategori' => $stats->jumlah_kategori,
                'bulan' => $stats->bulan,
                'tahun' => $stats->tahun,
            ],
        ]);
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartData(Request $request)
    {
        $tokoId = Auth::user()->toko->id;
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        // Get daily statistics for the month
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $dailyStats = StatistikHarian::where('toko_id', $tokoId)
            ->dateRange($startDate, $endDate)
            ->orderBy('tanggal', 'asc')
            ->get();

        // Format data for chart
        $chartData = $dailyStats->map(function ($stat) {
            return [
                'date' => $stat->tanggal->format('Y-m-d'),
                'day' => $stat->tanggal->format('d'),
                'pendapatan' => (float) $stat->pendapatan,
                'transaksi' => $stat->jumlah_transaksi,
            ];
        });

        return response()->json([
            'success' => true,
            'chart_data' => $chartData,
        ]);
    }

    /**
     * Get yearly overview
     */
    public function getYearlyOverview(Request $request)
    {
        $tokoId = Auth::user()->toko->id;
        $year = $request->query('year', now()->year);

        $monthlyStats = DashboardStat::where('toko_id', $tokoId)
            ->byYear($year)
            ->orderBy('bulan', 'asc')
            ->get();

        // Fill missing months with zero values
        $allMonths = [];
        for ($i = 1; $i <= 12; $i++) {
            $stat = $monthlyStats->firstWhere('bulan', $i);
            $allMonths[] = [
                'bulan' => $i,
                'nama_bulan' => Carbon::create()->month($i)->format('F'),
                'total_pendapatan' => $stat ? (float) $stat->total_pendapatan : 0,
                'jumlah_transaksi' => $stat ? $stat->jumlah_transaksi : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'yearly_overview' => $allMonths,
            'total_yearly_revenue' => $monthlyStats->sum('total_pendapatan'),
            'total_yearly_transactions' => $monthlyStats->sum('jumlah_transaksi'),
        ]);
    }

    /**
     * Update monthly statistics
     */
    private function updateMonthlyStats($tokoId, $month, $year, $stats)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        // Calculate from pesanan
        $pesananData = Pesanan::where('toko_id', $tokoId)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(total_amount) as total_pendapatan'),
                DB::raw('COUNT(*) as jumlah_transaksi')
            )
            ->first();

        // Count menu and kategori
        $jumlahMenu = Produk::where('toko_id', $tokoId)->count();
        $jumlahKategori = Kategori::where('toko_id', $tokoId)->count();

        // Update stats
        $stats->update([
            'total_pendapatan' => $pesananData->total_pendapatan ?? 0,
            'jumlah_transaksi' => $pesananData->jumlah_transaksi ?? 0,
            'jumlah_menu' => $jumlahMenu,
            'jumlah_kategori' => $jumlahKategori,
        ]);

        // Update daily statistics
        $this->updateDailyStats($tokoId, $startDate, $endDate);
    }

    /**
     * Update daily statistics
     */
    private function updateDailyStats($tokoId, $startDate, $endDate)
    {
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dailyData = Pesanan::where('toko_id', $tokoId)
                ->whereDate('order_date', $currentDate)
                ->select(
                    DB::raw('SUM(total_amount) as pendapatan'),
                    DB::raw('COUNT(*) as jumlah_transaksi')
                )
                ->first();

            StatistikHarian::updateOrCreate(
                [
                    'toko_id' => $tokoId,
                    'tanggal' => $currentDate->format('Y-m-d'),
                ],
                [
                    'pendapatan' => $dailyData->pendapatan ?? 0,
                    'jumlah_transaksi' => $dailyData->jumlah_transaksi ?? 0,
                ]
            );

            $currentDate->addDay();
        }
    }

    /**
     * Filter statistics by month
     */
    public function filterByMonth(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        return $this->getStatistics($request);
    }
}