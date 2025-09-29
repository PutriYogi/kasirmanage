<div class="row p-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">

                <h5><b>{{ $title }}</b></h5>

                <a href="/admin/produk/create" class="btn btn-primary mb-2"><i class="fas fa-plus"></i> Tambah</a>

                <table class="table">
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>

                    @foreach ($produk as $item)
                        
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->name }}</td>
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

                <div class="d-flex justify-content-center">
                    {{ $produk->links() }}
                </div>
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