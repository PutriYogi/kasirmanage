<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;

class AdminTransaksiDetailController extends Controller
{
    //
    function create(Request $request)
    {
        // dd($request->all());
        
        // Cek status transaksi sebelum allow modifikasi
        $transaksi_id = $request->transaksi_id;
        $transaksi = Transaksi::find($transaksi_id);
        if ($transaksi && $transaksi->status == 'selesai') {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi sudah selesai dan tidak dapat dimodifikasi'
                ], 403);
            }
            return redirect('/admin/transaksi')->with('error', 'Transaksi sudah selesai dan tidak dapat dimodifikasi.');
        }
        
        // Handle multiple products (from form submission)
        if (is_array($request->produk_id)) {
            $transaksi_id = $request->transaksi_id;
            
            for ($i = 0; $i < count($request->produk_id); $i++) {
                $produk_id = $request->produk_id[$i];
                $qty = $request->qty[$i];
                $subtotal = $request->subtotal[$i];
                
                $td = TransaksiDetail::whereProdukId($produk_id)->whereTransaksiId($transaksi_id)->first();
                
                if ($td == null) {
                    TransaksiDetail::create([
                        'produk_id' => $produk_id,
                        'produk_name' => $request->produk_name[$i],
                        'transaksi_id' => $transaksi_id,
                        'qty' => $qty,
                        'subtotal' => $subtotal,
                    ]);
                } else {
                    $td->update([
                        'qty' => $td->qty + $qty,
                        'subtotal' => $td->subtotal + $subtotal,
                    ]);
                }
            }
            
            // Update total transaksi berdasarkan semua detail
            $this->updateTransaksiTotal($transaksi_id);
            
            return redirect('/admin/transaksi/' . $transaksi_id . '/edit');
        }
        
        // Handle single product (legacy support & AJAX)
        $produk_id = $request->produk_id;
        $transaksi_id = $request->transaksi_id;

        $td = TransaksiDetail::whereProdukId($produk_id)->whereTransaksiId($transaksi_id)->first();

        if ($td == null) {
            // Buat detail baru
            TransaksiDetail::create([
                'produk_id' => $produk_id,
                'produk_name' => $request->produk_name,
                'transaksi_id' => $transaksi_id,
                'qty' => $request->qty,
                'subtotal' => $request->subtotal,
            ]);
        } else {
            // Update detail yang sudah ada (replace, bukan tambah)
            $td->update([
                'qty' => $request->qty, // Replace qty bukan tambah
                'subtotal' => $request->subtotal, // Replace subtotal bukan tambah
            ]);
        }
        
        // Update total transaksi berdasarkan semua detail
        $this->updateTransaksiTotal($transaksi_id);
        
        // Jika request AJAX, return JSON response
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Item berhasil ditambahkan'
            ]);
        }
        
        return redirect('/admin/transaksi/' . $transaksi_id . '/edit');
    }
    
    private function updateTransaksiTotal($transaksi_id)
    {
        $transaksi = Transaksi::find($transaksi_id);
        if ($transaksi) {
            $transaksi->updateTotalFromDetails();
        }
    }

    // Method untuk mendapatkan total transaksi via AJAX
    public function getTotals($id)
    {
        $transaksi = Transaksi::find($id);
        $qtyTotal = TransaksiDetail::whereTransaksiId($id)->sum('qty');
        
        return response()->json([
            'total' => $transaksi->total,
            'qty_total' => $qtyTotal
        ]);
    }

    // Method untuk hapus item berdasarkan produk_id via AJAX
    public function deleteByProduk()
    {
        $produk_id = request('produk_id');
        $transaksi_id = request('transaksi_id');
        
        // Cek status transaksi sebelum allow delete
        $transaksi = Transaksi::find($transaksi_id);
        if ($transaksi && $transaksi->status == 'selesai') {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus item. Transaksi sudah selesai.'
            ], 403);
        }
        
        $td = TransaksiDetail::whereProdukId($produk_id)->whereTransaksiId($transaksi_id)->first();
        
        if ($td) {
            $td->delete();
            $this->updateTransaksiTotal($transaksi_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Item tidak ditemukan'
        ]);
    }

    // Method untuk mendapatkan detail transaksi via AJAX  
    public function getDetails($transaksi_id)
    {
        $details = TransaksiDetail::whereTransaksiId($transaksi_id)->get();
        
        return response()->json([
            'success' => true,
            'details' => $details
        ]);
    }

    function delete()
    {
        $id = request('id');
        $td = TransaksiDetail::find($id);
        $transaksi_id = $td->transaksi_id;

        // Cek status transaksi sebelum allow delete
        $transaksi = Transaksi::find($transaksi_id);
        if ($transaksi && $transaksi->status == 'selesai') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus item. Transaksi sudah selesai.');
        }

        $td->delete();

        // Update total transaksi berdasarkan detail yang tersisa
        $this->updateTransaksiTotal($transaksi_id);

        return redirect()->back();
    }

    function done($id)
    {
        $transaksi = Transaksi::find($id);
        $data = [
            'status' => 'selesai'
        ];
        $transaksi->update($data);
        return redirect('/admin/transaksi');
    }
}
