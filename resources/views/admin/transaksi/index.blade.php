<div class="row p-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">

                <h5><b>{{ $title }}</b></h5>

                <div class="d-flex justify-content-between mb-2">
                    <a href="/admin/transaksi/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                    <button type="button" onclick="downloadData()" class="btn btn-success">
                        <i class="fas fa-download"></i> Download Data
                    </button>
                </div>

                {{-- Filter --}}
                <form method="GET" action="{{ url('/admin/transaksi') }}" class="mb-3">
                    <div class="d-flex align-items-center gap-2">

                        <select name="filter" class="form-control w-auto">
                            <option value="">-- Semua --</option>
                            <option value="today" {{ request('filter')=='today'?'selected':'' }}>Hari ini</option>
                            <option value="7days" {{ request('filter')=='7days'?'selected':'' }}>7 Hari</option>
                            <option value="1month" {{ request('filter')=='1month'?'selected':'' }}>1 Bulan</option>
                        </select>

                        <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="form-control w-auto">

                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ url('/admin/transaksi') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>

                <table class="table">
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Action</th>
                    </tr>

                    @forelse ($transaksi as $item)
                        <tr>
                            <td>{{ $item->kode_transaksi ?? 'TRX-'.$item->id }}</td>
                            <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-{{ $item->status == 'selesai' ? 'success' : 'warning' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                            <td>
                                <div class="d-flex">
                                    @if($item->status != 'selesai')
                                        {{-- Hanya tampilkan tombol edit jika transaksi belum selesai --}}
                                        <a href="/admin/transaksi/{{ $item->id }}/edit" class="btn btn-info btn-sm" title="Edit Transaksi">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    
                                    {{-- Tombol cetak struk untuk transaksi selesai --}}
                                    @if($item->status == 'selesai')
                                        <a href="/admin/transaksi/{{ $item->id }}/print" target="_blank" class="btn btn-success btn-sm {{ $item->status != 'selesai' ? 'ml-1' : '' }}" title="Cetak Struk">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    @endif
                                    
                                    {{-- Tombol delete untuk semua status --}}
                                    <form action="/admin/transaksi/{{ $item->id }}" method="POST" id="delete-form-{{ $item->id }}">
                                        @method('delete')
                                        @csrf
                                        <button type="button" onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->kode_transaksi ?? 'TRX-'.$item->id) }}')" class="btn btn-danger btn-sm ml-1" title="Hapus Transaksi">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada transaksi</td>
                        </tr>
                    @endforelse
                </table>

                <div class="d-flex justify-content-center">
                    {{ $transaksi->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(transaksiId, kodeTransaksi) {
    // Fallback jika SweetAlert tidak tersedia
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert tidak ditemukan, menggunakan confirm browser');
        if (confirm('Yakin ingin hapus transaksi ' + kodeTransaksi + '?')) {
            document.getElementById('delete-form-' + transaksiId).submit();
        }
        return;
    }

    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Yakin ingin hapus transaksi <strong>${kodeTransaksi}</strong>?<br><small class="text-muted">Data yang dihapus tidak dapat dikembalikan.</small>`,
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
                text: 'Sedang memproses penghapusan transaksi',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit form delete
            const form = document.getElementById('delete-form-' + transaksiId);
            if (form) {
                form.submit();
            } else {
                console.error('Form tidak ditemukan: delete-form-' + transaksiId);
                Swal.fire('Error', 'Form tidak ditemukan', 'error');
            }
        }
    }).catch((error) => {
        console.error('SweetAlert error:', error);
        // Fallback ke confirm browser
        if (confirm('Yakin ingin hapus transaksi ' + kodeTransaksi + '?')) {
            document.getElementById('delete-form-' + transaksiId).submit();
        }
    });
}

function downloadData() {
    // Ambil parameter filter saat ini
    const filter = '{{ request("filter") }}';
    const tanggal = '{{ request("tanggal") }}';
    
    // Konfirmasi download
    Swal.fire({
        title: 'Download Data Transaksi',
        html: `
            <div class="text-left">
                <p><strong>Filter:</strong> ${filter || 'Semua Data'}</p>
                ${tanggal ? `<p><strong>Tanggal:</strong> ${tanggal}</p>` : ''}
            </div>
            <hr>
            <p>Data akan didownload dalam format Excel (.csv) dengan kolom terpisah</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-download"></i> Download',
        cancelButtonText: '<i class="fas fa-times"></i> Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Tampilkan loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang menyiapkan file download',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Build URL dengan parameter
            let url = '/admin/transaksi/export?';
            const params = new URLSearchParams();
            
            if (filter) {
                params.append('filter', filter);
            }
            if (tanggal) {
                params.append('tanggal', tanggal);
            }
            
            url += params.toString();
            
            // Download file
            window.location.href = url;
            
            // Tutup loading setelah delay singkat
            setTimeout(() => {
                Swal.close();
            }, 2000);
        }
    });
}
</script>
