<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AdminPengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Data Pengeluaran';
        
        // Query pengeluaran dengan filter
        $query = Pengeluaran::query();

        // Filter berdasarkan periode
        $periode = $request->get('periode');
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        if ($periode) {
            if ($periode === 'custom' && $customStart && $customEnd) {
                $query->whereBetween('tanggal', [
                    Carbon::parse($customStart)->startOfDay(),
                    Carbon::parse($customEnd)->endOfDay()
                ]);
            } else {
                switch ($periode) {
                    case 'hari':
                        $query->whereDate('tanggal', Carbon::today());
                        break;
                    case 'minggu':
                        $query->whereBetween('tanggal', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        break;
                    case 'bulan':
                        $query->whereMonth('tanggal', Carbon::now()->month)
                              ->whereYear('tanggal', Carbon::now()->year);
                        break;
                    case '7hari':
                        $query->where('tanggal', '>=', Carbon::now()->subDays(7));
                        break;
                    case '30hari':
                        $query->where('tanggal', '>=', Carbon::now()->subDays(30));
                        break;
                }
            }
        }

        // Filter berdasarkan jenis pengeluaran
        if ($request->filled('jenis')) {
            $query->where('jenis_pengeluaran', 'like', '%' . $request->jenis . '%');
        }

        $pengeluaran = $query->orderBy('tanggal', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(15);

        // Total pengeluaran untuk periode ini
        $totalPengeluaran = $query->sum('nominal');

        $data = [
            'title' => $title,
            'content' => 'admin.pengeluaran.index',
            'pengeluaran' => $pengeluaran,
            'totalPengeluaran' => $totalPengeluaran
        ];

        return view('admin.layouts.wrapper', $data);
    }

    public function create()
    {
        $title = 'Tambah Pengeluaran';
        
        // Daftar jenis pengeluaran yang umum digunakan
        $jenisOptions = [
            'Operasional',
            'Transport',
            'Konsumsi',
            'Alat Tulis'
        ];

        $data = [
            'title' => $title,
            'content' => 'admin.pengeluaran.create',
            'jenisOptions' => $jenisOptions
        ];

        return view('admin.layouts.wrapper', $data);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_pengeluaran' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string'
        ], [
            'jenis_pengeluaran.required' => 'Jenis pengeluaran harus diisi',
            'nominal.required' => 'Nominal harus diisi',
            'nominal.numeric' => 'Nominal harus berupa angka',
            'nominal.min' => 'Nominal tidak boleh kurang dari 0'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            Pengeluaran::create([
                'jenis_pengeluaran' => $request->jenis_pengeluaran,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
                'tanggal' => now() // Menggunakan waktu sekarang otomatis
            ]);

            return redirect()->route('admin.pengeluaran.index')
                           ->with('success', 'Pengeluaran berhasil ditambahkan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $title = 'Edit Pengeluaran';
        $pengeluaran = Pengeluaran::findOrFail($id);
        
        $jenisOptions = [
            'Operasional',
            'Transport',
            'Konsumsi',
            'Alat Tulis'
        ];

        $data = [
            'title' => $title,
            'content' => 'admin.pengeluaran.edit',
            'pengeluaran' => $pengeluaran,
            'jenisOptions' => $jenisOptions
        ];

        return view('admin.layouts.wrapper', $data);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jenis_pengeluaran' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'tanggal' => 'required|date'
        ], [
            'jenis_pengeluaran.required' => 'Jenis pengeluaran harus diisi',
            'nominal.required' => 'Nominal harus diisi',
            'nominal.numeric' => 'Nominal harus berupa angka',
            'nominal.min' => 'Nominal tidak boleh kurang dari 0',
            'tanggal.required' => 'Tanggal harus diisi',
            'tanggal.date' => 'Format tanggal tidak valid'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $pengeluaran = Pengeluaran::findOrFail($id);
            $pengeluaran->update([
                'jenis_pengeluaran' => $request->jenis_pengeluaran,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
                'tanggal' => $request->tanggal
            ]);

            return redirect()->route('admin.pengeluaran.index')
                           ->with('success', 'Pengeluaran berhasil diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $pengeluaran = Pengeluaran::findOrFail($id);
            $pengeluaran->delete();

            return redirect()->route('admin.pengeluaran.index')
                           ->with('success', 'Pengeluaran berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('admin.pengeluaran.index')
                           ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        // Query data sesuai filter untuk export
        $query = Pengeluaran::query();

        $periode = $request->get('periode');
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');

        $filterLabel = 'Semua Data';

        if ($periode) {
            if ($periode === 'custom' && $customStart && $customEnd) {
                $query->whereBetween('tanggal', [
                    Carbon::parse($customStart)->startOfDay(),
                    Carbon::parse($customEnd)->endOfDay()
                ]);
                $filterLabel = "Custom ($customStart s/d $customEnd)";
            } else {
                switch ($periode) {
                    case 'hari':
                        $query->whereDate('tanggal', Carbon::today());
                        $filterLabel = 'Hari Ini';
                        break;
                    case 'minggu':
                        $query->whereBetween('tanggal', [
                            Carbon::now()->startOfWeek(),
                            Carbon::now()->endOfWeek()
                        ]);
                        $filterLabel = 'Minggu Ini';
                        break;
                    case 'bulan':
                        $query->whereMonth('tanggal', Carbon::now()->month)
                              ->whereYear('tanggal', Carbon::now()->year);
                        $filterLabel = 'Bulan Ini';
                        break;
                    case '7hari':
                        $query->where('tanggal', '>=', Carbon::now()->subDays(7));
                        $filterLabel = '7 Hari Terakhir';
                        break;
                    case '30hari':
                        $query->where('tanggal', '>=', Carbon::now()->subDays(30));
                        $filterLabel = '30 Hari Terakhir';
                        break;
                }
            }
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_pengeluaran', 'like', '%' . $request->jenis . '%');
        }

        $pengeluaran = $query->orderBy('tanggal', 'desc')->get();

        // Statistik
        $totalPengeluaran = $pengeluaran->sum('nominal');
        $jumlahPengeluaran = $pengeluaran->count();

        // Siapkan data untuk export - Format 4 kolom
        $data = [];
        
        // Header informasi
        $data[] = ['LAPORAN PENGELUARAN', '', '', ''];
        $data[] = ['Generated at', ':', date('d-m-Y H:i:s'), ''];
        $data[] = ['Filter Periode', ':', $filterLabel, ''];
        if ($periode === 'custom' && $customStart && $customEnd) {
            $data[] = ['Rentang Tanggal', ':', $customStart . ' sampai ' . $customEnd, ''];
        }
        $data[] = ['', '', '', '']; // Empty row
        
        // Statistik ringkasan
        $data[] = ['RINGKASAN PENGELUARAN', '', '', ''];
        $data[] = ['Keterangan', 'Jumlah', '', ''];
        $data[] = ['Total Pengeluaran (Rp)', $totalPengeluaran, '', ''];
        $data[] = ['Jumlah Transaksi', $jumlahPengeluaran, '', ''];
        $data[] = ['', '', '', '']; // Empty row
        
        // Header tabel data
        $data[] = ['DETAIL PENGELUARAN', '', '', ''];
        $data[] = ['Tanggal', 'Jenis Pengeluaran', 'Nominal (Rp)', 'Keterangan'];

        foreach ($pengeluaran as $item) {
            $data[] = [
                $item->tanggal->format('d-m-Y'),
                $item->jenis_pengeluaran,
                $item->nominal,
                $item->keterangan ?? '-'
            ];
        }

        // Generate filename berdasarkan filter
        $filename = 'Laporan_Pengeluaran';
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
                // Pastikan setiap row punya 4 kolom
                $row = array_pad($row, 4, '');
                
                // Gunakan semicolon sebagai delimiter (standard Excel Indonesia)
                fputcsv($output, $row, ';');
            }
            
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}