<div class="row p-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">

                <h5><b>{{ $title }}</b></h5>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <a href="/admin/produk/create" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Produk</a>
                    </div>
                    <div class="col-md-8">
                        <form method="GET" action="/admin/produk" class="d-flex">
                            <input type="text" name="search" class="form-control mr-2" placeholder="Cari nama produk..." value="{{ request('search') }}">
                            <select name="kategori_id" class="form-control mr-2">
                                <option value="">Semua Kategori</option>
                                @foreach($kategori as $kat)
                                    <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                                        {{ $kat->name }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="sort" class="form-control mr-2">
                                <option value="">Urutkan</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                                <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Nama Z-A</option>
                                <option value="harga_asc" {{ request('sort') == 'harga_asc' ? 'selected' : '' }}>Harga Terendah</option>
                                <option value="harga_desc" {{ request('sort') == 'harga_desc' ? 'selected' : '' }}>Harga Tertinggi</option>
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                            </select>
                            <button type="submit" class="btn btn-secondary mr-2"><i class="fas fa-search"></i> Filter</button>
                            @if(request('kategori_id') || request('search') || request('sort'))
                                <a href="/admin/produk" class="btn btn-outline-secondary"><i class="fas fa-times"></i> Reset</a>
                            @endif
                        </form>
                    </div>
                </div>

                @if(request('kategori_id') || request('search') || request('sort'))
                    <div class="alert alert-info">
                        <i class="fas fa-filter"></i> 
                        @if(request('search'))
                            Pencarian: "<strong>{{ request('search') }}</strong>"
                        @endif
                        @if(request('kategori_id'))
                            @if(request('search')) | @endif
                            Kategori: <strong>{{ $kategori->where('id', request('kategori_id'))->first()->name ?? 'Tidak ditemukan' }}</strong>
                        @endif
                        @if(request('sort'))
                            @if(request('search') || request('kategori_id')) | @endif
                            Urutan: <strong>
                                @switch(request('sort'))
                                    @case('name_asc') Nama A-Z @break
                                    @case('name_desc') Nama Z-A @break
                                    @case('harga_asc') Harga Terendah @break
                                    @case('harga_desc') Harga Tertinggi @break
                                    @case('newest') Terbaru @break
                                    @case('oldest') Terlama @break
                                    @default {{ request('sort') }}
                                @endswitch
                            </strong>
                        @endif
                        <a href="/admin/produk" class="btn btn-sm btn-outline-primary float-right">Tampilkan Semua</a>
                    </div>
                @endif

                <table class="table">
                    <tr>
                        <th>No</th>
                        <th>Gambar</th>
                        <th>Name</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Action</th>
                    </tr>

                    @foreach ($produk as $item)
                        
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            @if($item->gambar)
                                <img src="{{ asset($item->gambar) }}" width="50px" height="50px" style="object-fit: cover;" alt="Gambar Produk">
                            @else
                                <span class="text-muted">Tidak ada gambar</span>
                            @endif
                        </td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->kategori->name ?? 'Tidak ada kategori' }}</td>
                        <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td>
                            <div class="d-flex">
                                <a href="/admin/produk/{{ $item->id }}/edit" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                                {{-- <a href="" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a> --}}
                                <form action="/admin/produk/{{ $item->id }}" method="POST" id="delete-produk-{{ $item->id }}">
                                    @method('delete')
                                    @csrf
                                    <button type="button" onclick="confirmDeleteProduk({{ $item->id }}, '{{ addslashes($item->name) }}')" class="btn btn-danger btn-sm ml-1" title="Hapus Produk">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                </table>

                @if($produk->count() == 0)
                    <div class="text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Tidak ada produk ditemukan</h5>
                        @if(request('kategori_id') || request('search'))
                            <p class="text-muted">Coba ubah filter atau kata kunci pencarian</p>
                            <a href="/admin/produk" class="btn btn-primary">Tampilkan Semua Produk</a>
                        @else
                            <p class="text-muted">Silakan tambahkan produk terlebih dahulu</p>
                            <a href="/admin/produk/create" class="btn btn-primary">Tambah Produk Pertama</a>
                        @endif
                    </div>
                @else
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Menampilkan {{ $produk->count() }} dari {{ $produk->total() }} produk
                        </small>
                        <div>
                            {{ $produk->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeleteProduk(produkId, produkName) {
    // Fallback jika SweetAlert tidak tersedia
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert tidak ditemukan, menggunakan confirm browser');
        if (confirm('Yakin ingin hapus produk "' + produkName + '"?\nGambar produk juga akan dihapus.')) {
            document.getElementById('delete-produk-' + produkId).submit();
        }
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Hapus Produk',
        html: `Yakin ingin hapus produk <strong>"${produkName}"</strong>?<br><small class="text-muted">Produk dan gambarnya akan dihapus permanen.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
        cancelButtonText: '<i class="fas fa-times"></i> Batal',
        reverseButtons: true,
        focusCancel: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading
            Swal.fire({
                title: 'Menghapus...',
                text: 'Sedang memproses penghapusan produk dan gambar',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit form delete
            const form = document.getElementById('delete-produk-' + produkId);
            if (form) {
                form.submit();
            } else {
                console.error('Form tidak ditemukan: delete-produk-' + produkId);
                Swal.fire('Error', 'Form tidak ditemukan', 'error');
            }
        }
    }).catch((error) => {
        console.error('SweetAlert error:', error);
        // Fallback ke confirm browser
        if (confirm('Yakin ingin hapus produk "' + produkName + '"?\nGambar produk juga akan dihapus.')) {
            document.getElementById('delete-produk-' + produkId).submit();
        }
    });
}

// Tampilkan success message jika ada
@if(session('success'))
    Swal.fire({
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        icon: 'success',
        confirmButtonText: 'OK'
    });
@endif

// Tampilkan error message jika ada
@if(session('error'))
    Swal.fire({
        title: 'Error!',
        text: '{{ session('error') }}',
        icon: 'error',
        confirmButtonText: 'OK'
    });
@endif
</script>