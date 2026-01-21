<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\Produk;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminReportingController extends Controller
{
    public function index(Request $request)
    {
        // Filter berdasarkan periode - default ke "hari" jika tidak ada parameter
        $query = Transaksi::with(['details.produk', 'details'])
                         ->where('status', 'selesai'); // Hanya transaksi selesai untuk reporting

        $periode = $request->get('periode', 'hari'); // Default ke "hari"
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        if ($periode && $periode !== 'semua') {
            if ($periode === 'custom' && $customStart && $customEnd) {
                // Custom date range
                $query->whereBetween('created_at', [
                    Carbon::parse($customStart)->startOfDay(),
                    Carbon::parse($customEnd)->endOfDay()
                ]);
            } else {
                // Preset periods
                switch ($periode) {
                    case 'hari':
                        $query->whereDate('created_at', Carbon::today());
                        break;
                    case 'minggu':
                        $query->whereBetween('created_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        break;
                    case 'bulan':
                        $query->whereMonth('created_at', Carbon::now()->month)
                              ->whereYear('created_at', Carbon::now()->year);
                        break;
                    case '7hari':
                        $query->where('created_at', '>=', Carbon::now()->subDays(7));
                        break;
                    case '30hari':
                        $query->where('created_at', '>=', Carbon::now()->subDays(30));
                        break;
                }
            }
        }

        $transaksi = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Preserve filter parameters in pagination
        $transaksi->appends($request->all());

        // Hitung statistik untuk summary cards (includes expenses)
        $stats = $this->getStatistics($request);

        // Get pengeluaran data for the same period
        $pengeluaranQuery = Pengeluaran::query();
        if ($periode && $periode !== 'semua') {
            if ($periode === 'custom' && $customStart && $customEnd) {
                $pengeluaranQuery->whereBetween('tanggal', [
                    Carbon::parse($customStart)->startOfDay(),
                    Carbon::parse($customEnd)->endOfDay()
                ]);
            } else {
                switch ($periode) {
                    case 'hari':
                        $pengeluaranQuery->whereDate('tanggal', Carbon::today());
                        break;
                    case 'minggu':
                        $pengeluaranQuery->whereBetween('tanggal', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        break;
                    case 'bulan':
                        $pengeluaranQuery->whereMonth('tanggal', Carbon::now()->month)
                                        ->whereYear('tanggal', Carbon::now()->year);
                        break;
                    case '7hari':
                        $pengeluaranQuery->where('tanggal', '>=', Carbon::now()->subDays(7));
                        break;
                    case '30hari':
                        $pengeluaranQuery->where('tanggal', '>=', Carbon::now()->subDays(30));
                        break;
                }
            }
        }
        $pengeluaran = $pengeluaranQuery->orderBy('tanggal', 'desc')->paginate(10, ['*'], 'pengeluaran_page');

        $data = [
            'title' => 'Laporan Keuangan',
            'transaksi' => $transaksi,
            'pengeluaran' => $pengeluaran,
            'stats' => $stats,
            'content' => 'admin.reporting.index'
        ];

        return view('admin.layouts.wrapper', $data);
    }

    private function getStatistics(Request $request)
    {
        // Query yang sama untuk statistik
        $query = Transaksi::where('status', 'selesai');
        $pengeluaranQuery = Pengeluaran::query();

        $periode = $request->get('periode', 'hari'); // Default ke "hari"
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        // Apply filter berdasarkan periode
        if ($periode && $periode !== 'semua') {
            if ($periode === 'custom' && $customStart && $customEnd) {
                // Custom date range
                $query->whereBetween('created_at', [
                    Carbon::parse($customStart)->startOfDay(),
                    Carbon::parse($customEnd)->endOfDay()
                ]);
                $pengeluaranQuery->whereBetween('tanggal', [
                    Carbon::parse($customStart)->startOfDay(),
                    Carbon::parse($customEnd)->endOfDay()
                ]);
            } else {
                // Preset periods
                switch ($periode) {
                    case 'hari':
                        $query->whereDate('created_at', Carbon::today());
                        $pengeluaranQuery->whereDate('tanggal', Carbon::today());
                        break;
                    case 'minggu':
                        $query->whereBetween('created_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        $pengeluaranQuery->whereBetween('tanggal', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        break;
                    case 'bulan':
                        $query->whereMonth('created_at', Carbon::now()->month)
                              ->whereYear('created_at', Carbon::now()->year);
                        $pengeluaranQuery->whereMonth('tanggal', Carbon::now()->month)
                                        ->whereYear('tanggal', Carbon::now()->year);
                        break;
                    case '7hari':
                        $query->where('created_at', '>=', Carbon::now()->subDays(7));
                        $pengeluaranQuery->where('tanggal', '>=', Carbon::now()->subDays(7));
                        break;
                    case '30hari':
                        $query->where('created_at', '>=', Carbon::now()->subDays(30));
                        $pengeluaranQuery->where('tanggal', '>=', Carbon::now()->subDays(30));
                        break;
                }
            }
        }

        $transaksiData = $query->get();
        $pengeluaranData = $pengeluaranQuery->get();

        // Hitung statistik transaksi
        $totalPendapatan = $transaksiData->sum('total'); // Pendapatan kotor
        $jumlahTransaksi = $transaksiData->count();
        
        // Hitung pendapatan berdasarkan metode pembayaran
        $pendapatanCash = $transaksiData->where('metode_pembayaran', 'cash')->sum('total');
        $pendapatanQris = $transaksiData->where('metode_pembayaran', 'qris')->sum('total');
        $jumlahTransaksiCash = $transaksiData->where('metode_pembayaran', 'cash')->count();
        $jumlahTransaksiQris = $transaksiData->where('metode_pembayaran', 'qris')->count();
        
        // Hitung statistik pengeluaran
        $totalPengeluaran = $pengeluaranData->sum('nominal');
        $jumlahPengeluaran = $pengeluaranData->count();
        
        // Pendapatan bersih
        $pendapatanBersih = $totalPendapatan - $totalPengeluaran;

        // Produk terlaris
        $produkTerlaris = DB::table('transaksi_details')
            ->join('transaksis', 'transaksi_details.transaksi_id', '=', 'transaksis.id')
            ->join('produks', 'transaksi_details.produk_id', '=', 'produks.id')
            ->where('transaksis.status', 'selesai');
            
        if ($periode && $periode === 'hari') {
            $produkTerlaris->whereDate('transaksis.created_at', Carbon::today());
        }
        
        $produkTerlaris = $produkTerlaris->select('produks.name', DB::raw('SUM(transaksi_details.qty) as total_terjual'))
            ->groupBy('produks.id', 'produks.name')
            ->orderBy('total_terjual', 'desc')
            ->first();

        // Label periode
        $periodeLabel = $this->getPeriodeLabel($periode, $customStart, $customEnd);

        return [
            'total_pendapatan' => $totalPendapatan, // Pendapatan kotor (untuk backward compatibility)
            'pendapatan_kotor' => $totalPendapatan, // Pendapatan kotor (untuk view baru)
            'pendapatan_bersih' => $pendapatanBersih, // Pendapatan bersih
            'pendapatan_cash' => $pendapatanCash, // Pendapatan dari Cash
            'pendapatan_qris' => $pendapatanQris, // Pendapatan dari QRIS
            'total_pengeluaran' => $totalPengeluaran,
            'jumlah_transaksi' => $jumlahTransaksi, // Untuk backward compatibility
            'total_transaksi' => $jumlahTransaksi, // Untuk view baru
            'jumlah_transaksi_cash' => $jumlahTransaksiCash, // Jumlah transaksi Cash
            'jumlah_transaksi_qris' => $jumlahTransaksiQris, // Jumlah transaksi QRIS
            'jumlah_pengeluaran' => $jumlahPengeluaran,
            'produk_terlaris' => $produkTerlaris,
            'periode_label' => $periodeLabel
        ];
    }

    private function getPeriodeLabel($periode, $customStart, $customEnd)
    {
        // Default ke "hari" jika tidak ada periode
        if (!$periode) {
            $periode = 'hari';
        }
        
        if ($periode === 'semua') {
            return 'Semua Data';
        }

        switch ($periode) {
            case 'hari':
                return 'Hari Ini (' . Carbon::today()->format('d M Y') . ')';
            case 'minggu':
                return 'Minggu Ini (' . Carbon::now()->startOfWeek()->format('d M') . ' - ' . Carbon::now()->endOfWeek()->format('d M Y') . ')';
            case 'bulan':
                return 'Bulan Ini (' . Carbon::now()->format('F Y') . ')';
            case '7hari':
                return '7 Hari Terakhir (' . Carbon::now()->subDays(7)->format('d M') . ' - ' . Carbon::today()->format('d M Y') . ')';
            case '30hari':
                return '30 Hari Terakhir (' . Carbon::now()->subDays(30)->format('d M') . ' - ' . Carbon::today()->format('d M Y') . ')';
            case 'custom':
                if ($customStart && $customEnd) {
                    return 'Custom (' . Carbon::parse($customStart)->format('d M Y') . ' - ' . Carbon::parse($customEnd)->format('d M Y') . ')';
                }
                return 'Custom Period';
            default:
                return 'Hari Ini (' . Carbon::today()->format('d M Y') . ')';
        }
    }

    public function export(Request $request)
    {
        // Query data sesuai filter untuk export
        $query = Transaksi::with(['details.produk'])
                         ->where('status', 'selesai');

        $periode = $request->get('periode', 'hari'); // Default ke "hari"
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        $filterLabel = 'Hari Ini'; // Default label

        if ($periode && $periode !== 'semua') {
            if ($periode === 'custom' && $customStart && $customEnd) {
                $query->whereBetween('created_at', [
                    Carbon::parse($customStart)->startOfDay(),
                    Carbon::parse($customEnd)->endOfDay()
                ]);
                $filterLabel = "Custom: {$customStart} s/d {$customEnd}";
            } else {
                switch ($periode) {
                    case 'hari':
                        $query->whereDate('created_at', Carbon::today());
                        $filterLabel = 'Hari Ini';
                        break;
                    case 'minggu':
                        $query->whereBetween('created_at', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        $filterLabel = 'Minggu Ini';
                        break;
                    case 'bulan':
                        $query->whereMonth('created_at', Carbon::now()->month)
                              ->whereYear('created_at', Carbon::now()->year);
                        $filterLabel = 'Bulan Ini';
                        break;
                    case '7hari':
                        $query->where('created_at', '>=', Carbon::now()->subDays(7));
                        $filterLabel = '7 Hari Terakhir';
                        break;
                    case '30hari':
                        $query->where('created_at', '>=', Carbon::now()->subDays(30));
                        $filterLabel = '30 Hari Terakhir';
                        break;
                }
            }
        } else if ($periode === 'semua') {
            $filterLabel = 'Semua Data';
        }

        $transaksi = $query->orderBy('created_at', 'desc')->get();

        // Get pengeluaran data untuk export dengan filter yang sama
        $pengeluaranQuery = Pengeluaran::query();
        if ($periode && $periode !== 'semua') {
            if ($periode === 'custom' && $customStart && $customEnd) {
                $pengeluaranQuery->whereBetween('tanggal', [
                    Carbon::parse($customStart)->startOfDay(),
                    Carbon::parse($customEnd)->endOfDay()
                ]);
            } else {
                switch ($periode) {
                    case 'hari':
                        $pengeluaranQuery->whereDate('tanggal', Carbon::today());
                        break;
                    case 'minggu':
                        $pengeluaranQuery->whereBetween('tanggal', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        break;
                    case 'bulan':
                        $pengeluaranQuery->whereMonth('tanggal', Carbon::now()->month)
                                        ->whereYear('tanggal', Carbon::now()->year);
                        break;
                    case '7hari':
                        $pengeluaranQuery->where('tanggal', '>=', Carbon::now()->subDays(7));
                        break;
                    case '30hari':
                        $pengeluaranQuery->where('tanggal', '>=', Carbon::now()->subDays(30));
                        break;
                }
            }
        }
        $pengeluaran = $pengeluaranQuery->orderBy('tanggal', 'desc')->get();

        // Hitung statistik untuk header (comprehensive financial data)
        $pendapatanKotor = $transaksi->sum('total'); // Gross income
        $totalPengeluaran = $pengeluaran->sum('nominal'); // Total expenses
        $pendapatanBersih = $pendapatanKotor - $totalPengeluaran; // Net income
        $jumlahTransaksi = $transaksi->count();
        $jumlahPengeluaran = $pengeluaran->count();

        // Siapkan data untuk export - Format comprehensive financial report
        $data = [];
        
        // Header informasi
        $data[] = ['LAPORAN KEUANGAN COMPREHENSIVE', '', '', '', '', ''];
        $data[] = ['Generated at', ':', date('d-m-Y H:i:s'), '', '', ''];
        $data[] = ['Filter Periode', ':', $filterLabel, '', '', ''];
        if ($periode === 'custom' && $customStart && $customEnd) {
            $data[] = ['Rentang Tanggal', ':', $customStart . ' sampai ' . $customEnd, '', '', ''];
        }
        $data[] = ['', '', '', '', '', '']; // Empty row
        
        // Statistik ringkasan keuangan
        $data[] = ['RINGKASAN KEUANGAN', '', '', '', '', ''];
        $data[] = ['Keterangan', 'Jumlah (Rp)', 'Count', '', '', ''];
        $data[] = ['Pendapatan Kotor (Gross Income)', $pendapatanKotor, $jumlahTransaksi . ' transaksi', '', '', ''];
        $data[] = ['Total Pengeluaran (Expenses)', $totalPengeluaran, $jumlahPengeluaran . ' pengeluaran', '', '', ''];
        $data[] = ['Pendapatan Bersih (Net Income)', $pendapatanBersih, '', '', '', ''];
        $data[] = ['', '', '', '', '', '']; // Empty row
        
        // Header tabel data transaksi - 6 kolom dengan metode pembayaran
        $data[] = ['DETAIL TRANSAKSI', '', '', '', '', ''];
        $data[] = ['No', 'Tanggal', 'Total (Rp)', 'Metode Pembayaran', 'Jumlah Item', 'Detail Produk'];

        foreach ($transaksi as $index => $item) {
            $produkList = $item->details->map(function($detail) {
                return $detail->produk_name . ' (' . $detail->qty . 'x)';
            })->implode(', ');

            $data[] = [
                $index + 1,                                       // Kolom A: Nomor urut
                $item->created_at->format('d-m-Y H:i:s'),         // Kolom B: Tanggal
                $item->total,                                     // Kolom C: Total (angka murni)
                strtoupper($item->metode_pembayaran ?? 'CASH'),   // Kolom D: Metode Pembayaran
                $item->details->sum('qty'),                       // Kolom E: Jumlah Item
                $produkList                                       // Kolom F: Detail Produk
            ];
        }
        
        // Empty row sebelum data pengeluaran
        $data[] = ['', '', '', '', '', ''];
        
        // Header tabel data pengeluaran
        $data[] = ['DETAIL PENGELUARAN', '', '', '', '', ''];
        $data[] = ['Tanggal', 'Jenis Pengeluaran', 'Nominal (Rp)', 'Keterangan', '', ''];

        foreach ($pengeluaran as $item) {
            $data[] = [
                Carbon::parse($item->tanggal)->format('d-m-Y'),   // Kolom A: Tanggal
                $item->jenis_pengeluaran,                         // Kolom B: Jenis
                $item->nominal,                                   // Kolom C: Nominal (angka murni)
                $item->keterangan ?? '-',                         // Kolom D: Keterangan
                '',                                               // Kolom E: Kosong
                ''                                                // Kolom F: Kosong
            ];
        }

        // Generate filename berdasarkan filter
        $filename = 'Laporan_Keuangan';
        if ($periode == 'hari') {
            $filename .= '_Hari_Ini';
        } elseif ($periode == 'minggu') {
            $filename .= '_Minggu_Ini';
        } elseif ($periode == 'bulan') {
            $filename .= '_Bulan_Ini';
        } elseif ($periode == '7hari') {
            $filename .= '_7_Hari_Terakhir';
        } elseif ($periode == '30hari') {
            $filename .= '_30_Hari_Terakhir';
        } elseif ($periode == 'custom' && $customStart && $customEnd) {
            $filename .= '_' . str_replace('-', '_', $customStart) . '_sampai_' . str_replace('-', '_', $customEnd);
        } else {
            $filename .= '_Semua_Data';
        }
        $filename .= '_' . date('Y_m_d_H_i_s') . '.csv';

        // Set headers untuk download Excel-optimized
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ];

        // Generate CSV content optimized for Excel
        $callback = function() use ($data) {
            $output = fopen('php://output', 'w');
            
            // Add UTF-8 BOM untuk proper Excel encoding
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            foreach ($data as $row) {
                // Pastikan setiap row punya 6 kolom untuk format baru
                $row = array_pad($row, 6, '');
                
                // Gunakan semicolon sebagai delimiter (standard Excel Indonesia)
                fputcsv($output, $row, ';');
            }
            
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}