<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Cek apakah user baru login (ada session flash dari login)
        $justLoggedIn = session()->has('login_success') || session()->pull('show_welcome', false);
        
        // Tetapkan periode hanya hari ini (real-time)
        $dateRange = $this->getTodayDateRange();
        
        // Statistik umum untuk hari ini
        $stats = $this->getStatistics($dateRange);
        
        // Data untuk grafik harian (per jam)
        $chartData = $this->getHourlyData($dateRange);
        
        $data = [
            'title' => 'Dashboard',
            'stats' => $stats,
            'chartData' => $chartData,
            'justLoggedIn' => $justLoggedIn,
            'content' => 'admin/dashboard/index'
        ];
        
        return view('admin.layouts.wrapper', $data);
    }
    
    private function getTodayDateRange()
    {
        $now = Carbon::now();
        
        return [
            'start' => $now->startOfDay(),
            'end' => $now->copy()->endOfDay(),
            'label' => 'Hari Ini'
        ];
    }
    
    private function getStatistics($dateRange)
    {
        // Query transaksi dalam rentang tanggal
        $transaksi = Transaksi::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'selesai');
        
        // Total pendapatan
        $totalPendapatan = $transaksi->sum('total');
        
        // Jumlah transaksi
        $jumlahTransaksi = $transaksi->count();
        
        // Transaksi terakhir hari ini
        $transaksiTerakhir = Transaksi::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', 'selesai')
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Total pengeluaran untuk periode yang sama
        $totalPengeluaran = Pengeluaran::whereBetween('tanggal', [$dateRange['start']->toDateString(), $dateRange['end']->toDateString()])
            ->sum('nominal');
        
        // Pendapatan bersih (pendapatan - pengeluaran)
        $pendapatanBersih = $totalPendapatan - $totalPengeluaran;
        
        // Produk terlaris
        $produkTerlaris = DB::table('transaksi_details')
            ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
            ->join('produks', 'transaksi_details.produk_id', '=', 'produks.id')
            ->whereBetween('transaksis.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('transaksis.status', 'selesai')
            ->select('produks.name', DB::raw('SUM(CAST(transaksi_details.qty AS UNSIGNED)) as total_terjual'))
            ->groupBy('produks.id', 'produks.name')
            ->orderBy('total_terjual', 'desc')
            ->first();
        
        return [
            'total_pendapatan' => $totalPendapatan,
            'pendapatan_bersih' => $pendapatanBersih,
            'jumlah_transaksi' => $jumlahTransaksi,
            'total_pengeluaran' => $totalPengeluaran,
            'produk_terlaris' => $produkTerlaris,
            'transaksi_terakhir' => $transaksiTerakhir,
            'periode_label' => $dateRange['label']
        ];
    }
    
    private function getHourlyData($dateRange)
    {
        $data = [];
        $labels = [];
        
        for ($hour = 0; $hour < 24; $hour++) {
            $hourStart = $dateRange['start']->copy()->setHour($hour)->setMinute(0)->setSecond(0);
            $hourEnd = $hourStart->copy()->setMinute(59)->setSecond(59);
            
            $pendapatan = Transaksi::whereBetween('created_at', [$hourStart, $hourEnd])
                ->where('status', 'selesai')
                ->sum('total');
            
            $labels[] = sprintf('%02d:00', $hour);
            $data[] = $pendapatan;
        }
        
        return [
            'labels' => $labels,
            'data' => $data,
            'title' => 'Pendapatan per Jam'
        ];
    }
}
