<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;

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

    if ($request->filter == 'today') {
        $query->whereDate('created_at', today());
    } elseif ($request->filter == '7days') {
        $query->where('created_at', '>=', now()->subDays(7));
    } elseif ($request->filter == '1month') {
        $query->where('created_at', '>=', now()->subMonth());
    }

    if ($request->tanggal) {
        $query->whereDate('created_at', $request->tanggal);
    }

    $transaksi = $query->orderBy('created_at', 'desc')->paginate(10);

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

    $transaksi->update([
        'total'      => $request->total,
        'status' => 'selesai',
        // 'dibayarkan' => $request->dibayarkan,
        // 'kembalian'  => $request->kembalian,
    ]);

    return redirect('/admin/transaksi')->with('success', 'Transaksi berhasil disimpan');
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

        $transaksi = Transaksi::find($id);

        $dibayarkan = request('dibayarkan');
        $kembalian = $dibayarkan - $transaksi->total;



        $data = [
            'title'     => 'Tambah Transaksi',
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
}
