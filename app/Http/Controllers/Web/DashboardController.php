<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get statistics (hanya pesanan selesai yang dihitung)
        $totalPendapatan = Pesanan::where('user_id', $user->id)
            ->where('status', 'selesai')
            ->sum('total_harga');
            
        $jumlahTransaksi = Pesanan::where('user_id', $user->id)
            ->where('status', 'selesai')
            ->count();
            
        $jumlahMenu = Produk::where('user_id', $user->id)->count();
        $jumlahKategori = Kategori::where('user_id', $user->id)->count();
        
        // Get pending count for notification
        $pendingCount = Pesanan::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        
        // Get chart data for current month (from pesanan table - real-time)
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);
        
        // Get daily revenue from pesanan table
        $chartData = Pesanan::where('user_id', $user->id)
            ->where('status', 'selesai')
            ->whereYear('order_date', $year)
            ->whereMonth('order_date', $month)
            ->select(
                DB::raw('DAY(order_date) as tanggal'),
                DB::raw('SUM(total_harga) as pendapatan')
            )
            ->groupBy(DB::raw('DAY(order_date)'))
            ->orderBy('tanggal')
            ->get()
            ->map(function ($stat) {
                return [
                    'tanggal' => (int) $stat->tanggal,
                    'pendapatan' => (float) $stat->pendapatan
                ];
            });
        
        // Fill missing days with 0
        $daysInMonth = now()->setYear($year)->setMonth($month)->daysInMonth;
        $filledData = collect();
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $existingData = $chartData->firstWhere('tanggal', $day);
            
            $filledData->push([
                'tanggal' => $day,
                'pendapatan' => $existingData ? $existingData['pendapatan'] : 0
            ]);
        }
        
        return view('dashboard.index', compact(
            'totalPendapatan',
            'jumlahTransaksi',
            'jumlahMenu',
            'jumlahKategori',
            'pendingCount',
            'filledData'
        ));
    }
}
