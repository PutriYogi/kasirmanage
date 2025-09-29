<div class="row p-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">

                <h5><b>{{ $title }}</b></h5>

                <a href="/admin/kategori/create" class="btn btn-primary mb-2"><i class="fas fa-plus"></i> Tambah</a>

                <table class="table">
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>

                    @foreach ($kategori as $item)
                        
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->name }}</td>
                        <td>
                            <div class="d-flex">
                                <a href="/admin/kategori/{{ $item->id }}/edit" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                                {{-- <a href="" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a> --}}
                                <form action="/admin/kategori/{{ $item->id }}" method="POST" id="delete-kategori-{{ $item->id }}">
                                    @method('delete')
                                    @csrf
                                    <button type="button" onclick="confirmDeleteKategori({{ $item->id }}, '{{ addslashes($item->name) }}')" class="btn btn-danger btn-sm ml-1" title="Hapus Kategori"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach

                </table>

                <div class="d-flex justify-content-center">
                    {{ $kategori->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeleteKategori(kategoriId, kategoriName) {
    // Fallback jika SweetAlert tidak tersedia
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert tidak ditemukan, menggunakan confirm browser');
        if (confirm('Yakin ingin hapus kategori "' + kategoriName + '"?')) {
            document.getElementById('delete-kategori-' + kategoriId).submit();
        }
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Hapus Kategori',
        html: `Yakin ingin hapus kategori <strong>"${kategoriName}"</strong>?<br><small class="text-muted">Data yang dihapus tidak dapat dikembalikan.</small>`,
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
                text: 'Sedang memproses penghapusan kategori',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit form delete
            const form = document.getElementById('delete-kategori-' + kategoriId);
            if (form) {
                form.submit();
            } else {
                console.error('Form tidak ditemukan: delete-kategori-' + kategoriId);
                Swal.fire('Error', 'Form tidak ditemukan', 'error');
            }
        }
    }).catch((error) => {
        console.error('SweetAlert error:', error);
        // Fallback ke confirm browser
        if (confirm('Yakin ingin hapus kategori "' + kategoriName + '"?')) {
            document.getElementById('delete-kategori-' + kategoriId).submit();
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