<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminTransaksiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index()
    // {
    //     //
    //     $data = [
    //         'title'     => 'Manajemen Transaksi',
    //         'transaksi'  => Transaksi::orderBy('created_at', 'DESC')->paginate(10),
    //         'content'   => 'admin/transaksi/index'
    //     ];
    //     return view('admin.layouts.wrapper', $data);
    // }
    public function index(Request $request)
    {
        $query = \App\Models\Transaksi::query();

        // Filter berdasarkan periode seperti di dashboard
        $periode = $request->get('periode');
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        if ($periode) {
            if ($periode === 'custom' && $customStart && $customEnd) {
                // Custom date range
                $query->whereBetween('created_at', [
                    \Carbon\Carbon::parse($customStart)->startOfDay(),
                    \Carbon\Carbon::parse($customEnd)->endOfDay()
                ]);
            } else {
                // Preset periods
                switch ($periode) {
                    case 'hari':
                        $query->whereDate('created_at', \Carbon\Carbon::today());
                        break;
                    case 'minggu':
                        $query->whereBetween('created_at', [
                            \Carbon\Carbon::now()->startOfWeek(),
                            \Carbon\Carbon::now()->endOfWeek()
                        ]);
                        break;
                    case 'bulan':
                        $query->whereMonth('created_at', \Carbon\Carbon::now()->month)
                              ->whereYear('created_at', \Carbon\Carbon::now()->year);
                        break;
                    case '7hari':
                        $query->where('created_at', '>=', \Carbon\Carbon::now()->subDays(7));
                        break;
                    case '30hari':
                        $query->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30));
                        break;
                }
            }
        }

        $transaksi = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Preserve filter parameters in pagination
        $transaksi->appends($request->all());

        $data = [
            'title' => 'Data Transaksi',
            'transaksi' => $transaksi,
            'content' => 'admin.transaksi.index'
        ];

        return view('admin.layouts.wrapper', $data);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $data = [
            'user_id'   => auth()->user()->id,
            'kasir_name'   => auth()->user()->name,
            'total'     => 0,
        ];
        $transaksi = Transaksi::create($data);
        return redirect('/admin/transaksi/' . $transaksi->id . '/edit');
    }

    public function selesai(Request $request, $id)
{
    $transaksi = Transaksi::findOrFail($id);

    // Cek apakah transaksi memiliki detail
    $detailCount = TransaksiDetail::whereTransaksiId($id)->count();
    if ($detailCount == 0) {
        return redirect()->back()->with('error', 'Tidak dapat menyelesaikan transaksi. Belum ada item yang dipilih.');
    }

    // Update total berdasarkan detail transaksi yang ada
    $calculatedTotal = $transaksi->updateTotalFromDetails();

    if ($calculatedTotal <= 0) {
        return redirect()->back()->with('error', 'Tidak dapat menyelesaikan transaksi. Total transaksi tidak valid.');
    }

    // Validasi data pembayaran
    $dibayarkan = (int) $request->dibayarkan;
    $kembalian = (int) $request->kembalian;
    $metodePembayaran = $request->metode_pembayaran ?? 'cash';
    
    // Validasi untuk cash: dibayarkan harus >= total
    if ($metodePembayaran == 'cash' && $dibayarkan < $calculatedTotal) {
        return redirect()->back()->with('error', 'Jumlah dibayar tidak boleh kurang dari total transaksi.');
    }
    
    // Untuk QRIS: set dibayarkan = total dan kembalian = 0
    if ($metodePembayaran == 'qris') {
        $dibayarkan = $calculatedTotal;
        $kembalian = 0;
    }

    $transaksi->update([
        'status' => 'selesai',
        'dibayarkan' => $dibayarkan,
        'kembalian'  => $kembalian,
        'metode_pembayaran' => $metodePembayaran,
    ]);

    // Set session untuk SweetAlert sukses
    session()->flash('transaksi_selesai', [
        'total' => $calculatedTotal,
        'dibayarkan' => $dibayarkan,
        'kembalian' => $kembalian,
        'metode_pembayaran' => $metodePembayaran,
        'kode' => $transaksi->kode_transaksi ?? 'TRX-' . $transaksi->id
    ]);

    return redirect('/admin/transaksi')->with('success', 'Transaksi berhasil diselesaikan dengan total: Rp ' . number_format($calculatedTotal, 0, ',', '.'));
}




    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $transaksi = Transaksi::find($id);
        
        // Cek apakah transaksi sudah selesai
        if ($transaksi && $transaksi->status == 'selesai') {
            return redirect('/admin/transaksi')->with('error', 'Transaksi sudah selesai dan tidak dapat diedit lagi.');
        }

        $produk = Produk::get();

        $produk_id = request('produk_id');
        $p_detail = Produk::find($produk_id);

        $transaksi_detail = TransaksiDetail::whereTransaksiId($id)->get();

        $act = request('act');
        $qty = request('qty');
        if ($act == 'min') {
            if ($qty <= 1) {
                $qty = 1;
            } else {
                $qty = $qty - 1;
            }
        } else {
            $qty = $qty + 1;
        }

        $subtotal = 0;
        if ($p_detail) {
            $subtotal = $qty * $p_detail->harga;
        }

        // Hitung ulang total berdasarkan detail transaksi yang ada
        $calculatedTotal = $transaksi->calculateTotal();
        if ($calculatedTotal > 0 && $transaksi->total != $calculatedTotal) {
            $transaksi->updateTotalFromDetails();
            $transaksi->refresh(); // Refresh data transaksi
        }

        $dibayarkan = request('dibayarkan');
        $kembalian = $dibayarkan - $transaksi->total;

        $data = [
            'title'     => 'Edit Transaksi',
            'produk'    => $produk,
            'p_detail'  => $p_detail,
            'qty'       => $qty,
            'subtotal'       => $subtotal,
            'transaksi_detail'       => $transaksi_detail,
            'transaksi' => $transaksi,
            'kembalian' => $kembalian,
            'content'   => 'admin/transaksi/create'
        ];
        return view('admin.layouts.wrapper', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->delete();

        return redirect()->back()->with('success', 'Transaksi berhasil dihapus');
    }

    public function export(Request $request)
    {
        // Query data sesuai filter yang sama dengan index (menggunakan periode)
        $query = \App\Models\Transaksi::query();

        // Filter berdasarkan periode seperti di dashboard dan index
        $periode = $request->get('periode');
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        $filterLabel = 'Semua Data';

        if ($periode) {
            if ($periode === 'custom' && $customStart && $customEnd) {
                // Custom date range
                $query->whereBetween('created_at', [
                    \Carbon\Carbon::parse($customStart)->startOfDay(),
                    \Carbon\Carbon::parse($customEnd)->endOfDay()
                ]);
                $filterLabel = "Custom: {$customStart} s/d {$customEnd}";
            } else {
                // Preset periods
                switch ($periode) {
                    case 'hari':
                        $query->whereDate('created_at', \Carbon\Carbon::today());
                        $filterLabel = 'Hari Ini';
                        break;
                    case 'minggu':
                        $query->whereBetween('created_at', [
                            \Carbon\Carbon::now()->startOfWeek(),
                            \Carbon\Carbon::now()->endOfWeek()
                        ]);
                        $filterLabel = 'Minggu Ini';
                        break;
                    case 'bulan':
                        $query->whereMonth('created_at', \Carbon\Carbon::now()->month)
                              ->whereYear('created_at', \Carbon\Carbon::now()->year);
                        $filterLabel = 'Bulan Ini';
                        break;
                    case '7hari':
                        $query->where('created_at', '>=', \Carbon\Carbon::now()->subDays(7));
                        $filterLabel = '7 Hari Terakhir';
                        break;
                    case '30hari':
                        $query->where('created_at', '>=', \Carbon\Carbon::now()->subDays(30));
                        $filterLabel = '30 Hari Terakhir';
                        break;
                }
            }
        }

        $transaksi = $query->orderBy('created_at', 'desc')->get();

        // Hitung statistik
        $totalTransaksi = $transaksi->count();
        $totalNilai = $transaksi->sum('total');
        $transaksiSelesai = $transaksi->where('status', 'selesai')->count();
        $transaksiPending = $transaksi->where('status', 'pending')->count();

        // Siapkan data untuk export
        $data = [];
        
        // Header informasi - Menggunakan kolom A-G
        $data[] = ['LAPORAN DATA TRANSAKSI', '', '', '', '', '', ''];
        $data[] = ['Generated at', ':', date('d-m-Y H:i:s'), '', '', '', ''];
        $data[] = ['Filter Periode', ':', $filterLabel, '', '', '', ''];
        if ($periode === 'custom' && $customStart && $customEnd) {
            $data[] = ['Rentang Tanggal', ':', $customStart . ' sampai ' . $customEnd, '', '', '', ''];
        }
        $data[] = ['', '', '', '', '', '', '']; // Empty row
        
        // Statistik - Format tabel 2 kolom
        $data[] = ['RINGKASAN STATISTIK', '', '', '', '', '', ''];
        $data[] = ['Keterangan', 'Jumlah', '', '', '', '', ''];
        $data[] = ['Total Transaksi', $totalTransaksi, '', '', '', '', ''];
        $data[] = ['Total Nilai (Rp)', $totalNilai, '', '', '', '', '']; // Angka murni untuk Excel
        $data[] = ['Transaksi Selesai', $transaksiSelesai, '', '', '', '', ''];
        $data[] = ['Transaksi Pending', $transaksiPending, '', '', '', '', ''];
        $data[] = ['', '', '', '', '', '', '']; // Empty row
        
        // Header tabel data - 7 kolom terpisah
        $data[] = ['DATA TRANSAKSI', '', '', '', '', '', ''];
        $data[] = ['ID', 'Kode Transaksi', 'Kasir', 'Total (Rp)', 'Status', 'Metode Pembayaran', 'Tanggal'];

        foreach ($transaksi as $item) {
            $data[] = [
                $item->id,                                          // Kolom A: ID
                $item->kode_transaksi ?? 'TRX-'.$item->id,        // Kolom B: Kode
                $item->kasir_name ?? 'Unknown',                   // Kolom C: Kasir
                $item->total,                                     // Kolom D: Total (angka murni)
                ucfirst($item->status),                           // Kolom E: Status
                strtoupper($item->metode_pembayaran ?? 'CASH'),   // Kolom F: Metode Pembayaran
                $item->created_at->format('d-m-Y H:i:s')         // Kolom G: Tanggal
            ];
        }

        // Generate filename berdasarkan filter
        $filename = 'Laporan_Transaksi';
        if ($request->filter == 'today') {
            $filename .= '_Hari_Ini';
        } elseif ($request->filter == '7days') {
            $filename .= '_7_Hari';
        } elseif ($request->filter == '1month') {
            $filename .= '_1_Bulan';
        } elseif ($request->tanggal) {
            $filename .= '_' . str_replace('-', '_', $request->tanggal);
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
                // Pastikan setiap row punya 7 kolom
                $row = array_pad($row, 7, '');
                
                // Gunakan semicolon sebagai delimiter (standard Excel Indonesia)
                fputcsv($output, $row, ';');
            }
            
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print($id)
    {
        $transaksi = Transaksi::with('details')->findOrFail($id);
        
        // Pastikan transaksi sudah selesai
        if ($transaksi->status != 'selesai') {
            return redirect()->back()->with('error', 'Hanya transaksi yang selesai yang bisa dicetak.');
        }

        // Data untuk struk PDF
        $data = [
            'transaksi' => $transaksi,
            'details' => $transaksi->details,
            'total_qty' => $transaksi->details->sum('qty'),
            'company_name' => 'Warung Moro tresno',
            'company_address' => 'Bontomanai, Rumbia, Kab. Jeneponto',
            'company_phone' => '(085) 675465789',
            'kasir_name' => Auth::check() ? Auth::user()->name : 'Admin' // Ambil nama user yang sedang login
        ];

        // Generate PDF menggunakan Laravel DomPDF
        $pdf = Pdf::loadView('admin.transaksi.struk', $data);
        
        // Set paper size untuk struk (80mm x 200mm)
        $pdf->setPaper([0, 0, 226.77, 566.93], 'portrait');
        
        // Generate filename
        $filename = 'Struk_' . ($transaksi->kode_transaksi ?? 'TRX-'.$transaksi->id) . '_' . date('Y-m-d') . '.pdf';
        
        // Stream PDF
        return $pdf->stream($filename);
    }
}
