<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\Request;
use App\Models\Produk;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Log;

class AdminProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        // die('masuk');
        
        // Ambil semua kategori untuk filter
        $kategori = Kategori::all();
        
        // Query produk dengan filter kategori dan pencarian
        $produkQuery = Produk::with('kategori');
        
        // Filter berdasarkan kategori
        if (request('kategori_id')) {
            $produkQuery->where('kategori_id', request('kategori_id'));
        }
        
        // Filter berdasarkan pencarian nama
        if (request('search')) {
            $produkQuery->where('name', 'like', '%' . request('search') . '%');
        }
        
        // Sorting
        switch (request('sort')) {
            case 'name_asc':
                $produkQuery->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $produkQuery->orderBy('name', 'desc');
                break;
            case 'harga_asc':
                $produkQuery->orderBy('harga', 'asc');
                break;
            case 'harga_desc':
                $produkQuery->orderBy('harga', 'desc');
                break;
            case 'newest':
                $produkQuery->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $produkQuery->orderBy('created_at', 'asc');
                break;
            default:
                $produkQuery->orderBy('created_at', 'desc'); // Default terbaru
                break;
        }
        
        $data = [
            'title'     => 'Manajemen Produk',
            'produk'    => $produkQuery->paginate(10)->appends(request()->query()),
            'kategori'  => $kategori,
            'content'   => 'admin/produk/index'
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
            'title'     => 'Tambah Produk',
            'kategori'  => Kategori::get(),
            'content'   => 'admin/produk/create'
        ];
        return view('admin.layouts.wrapper', $data);
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
        $data = $request->validate([
            'name'  => 'required',
            'kategori_id'  => 'required',
            'harga'  => 'required',
        ]);
        // dd($request->all());

        if ($request->hasFile('gambar')) {
            $gambar = $request->file('gambar');
            $file_name = time() . "_" . $gambar->getClientOriginalName();

            $storage = 'uploads/images/';
            $gambar->move($storage, $file_name);
            $data['gambar'] = $storage . $file_name;
        } else {
            $data['gambar'] = null;
        }

        Produk::create($data);
        Alert::success('Sukses', 'Data berhasil ditambahkan');
        return redirect()->back();
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
        $data = [
            'title'     => 'Tambah Produk',
            'produk'  => Produk::find($id),
            'kategori'  => Kategori::get(),
            'content'   => 'admin/produk/create'
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
        $produk = Produk::find($id);
        $data = $request->validate([
            'name'  => 'required',
            'kategori_id'  => 'required',
            'harga'  => 'required',
        ]);

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada dan file exists
            if ($produk->gambar != null && !empty($produk->gambar)) {
                $oldImagePath = public_path($produk->gambar);
                if (file_exists($oldImagePath)) {
                    try {
                        unlink($oldImagePath);
                    } catch (\Exception $e) {
                        Log::warning('Gagal menghapus gambar lama: ' . $e->getMessage());
                    }
                }
            }
            
            // Upload gambar baru
            $gambar = $request->file('gambar');
            $file_name = time() . "_" . $gambar->getClientOriginalName();

            $storage = 'uploads/images/';
            $gambar->move($storage, $file_name);
            $data['gambar'] = $storage . $file_name;
        } else {
            $data['gambar'] = $produk->gambar;
        }

        $produk->update($data);
        Alert::success('Sukses', 'Data berhasil diedit');
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $produk = Produk::find($id);
        
        if (!$produk) {
            return redirect()->back()->with('error', 'Produk tidak ditemukan.');
        }
        
        $produkName = $produk->name;
        
        // Hapus file gambar jika ada dan file benar-benar exist
        if ($produk->gambar != null && !empty($produk->gambar)) {
            $imagePath = public_path($produk->gambar);
            
            // Cek apakah file benar-benar ada sebelum dihapus
            if (file_exists($imagePath)) {
                try {
                    unlink($imagePath);
                } catch (\Exception $e) {
                    // Log error tapi tetap lanjut hapus produk
                    Log::warning('Gagal menghapus gambar produk: ' . $e->getMessage());
                }
            }
        }
        
        $produk->delete();
        
        return redirect()->back()->with('success', 'Produk "' . $produkName . '" berhasil dihapus.');
    }
}
