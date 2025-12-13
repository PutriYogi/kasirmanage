<div class="row p-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">

                <h5><b>{{ $title }}</b></h5>

                <div class="d-flex justify-content-between mb-2">
                    <a href="/admin/transaksi/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah
                    </a>
                    {{-- <button type="button" onclick="downloadData()" class="btn btn-success">
                        <i class="fas fa-download"></i> Download Data
                    </button> --}}
                </div>

                {{-- Filter dengan style dashboard --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="{{ url('/admin/transaksi') }}" class="row align-items-end" id="transaksiFilterForm">
                            <div class="col-md-3">
                                <label class="mb-1"><i class="fas fa-clock"></i> Periode:</label>
                                <select name="periode" class="form-control" onchange="autoSubmitTransaksiFilter()">
                                    <option value="" {{ request('periode') == '' ? 'selected' : '' }}>Semua Data</option>
                                    <option value="hari" {{ request('periode') == 'hari' ? 'selected' : '' }}>Hari Ini</option>
                                    <option value="minggu" {{ request('periode') == 'minggu' ? 'selected' : '' }}>Minggu Ini</option>
                                    <option value="bulan" {{ request('periode') == 'bulan' ? 'selected' : '' }}>Bulan Ini</option>
                                    <option value="7hari" {{ request('periode') == '7hari' ? 'selected' : '' }}>7 Hari Terakhir</option>
                                    <option value="30hari" {{ request('periode') == '30hari' ? 'selected' : '' }}>30 Hari Terakhir</option>
                                    <option value="custom" {{ request('periode') == 'custom' ? 'selected' : '' }}>Periode Custom</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3" id="startDateContainerTransaksi" style="{{ request('periode') == 'custom' ? '' : 'display: none;' }}">
                                <label class="mb-1">Tanggal Mulai:</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') ?? \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                            
                            <div class="col-md-3" id="endDateContainerTransaksi" style="{{ request('periode') == 'custom' ? '' : 'display: none;' }}">
                                <label class="mb-1">Tanggal Selesai:</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') ?? \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                            
                            <div class="col-md-3" style="display: none;">
                                <button type="button" class="btn btn-primary" onclick="submitTransaksiFilter()">
                                    <i class="fas fa-search"></i> Filter Data
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                @if(request('periode'))
                                    Menampilkan data: <strong>
                                        @switch(request('periode'))
                                            @case('hari') Hari Ini @break
                                            @case('minggu') Minggu Ini @break
                                            @case('bulan') Bulan Ini @break
                                            @case('7hari') 7 Hari Terakhir @break
                                            @case('30hari') 30 Hari Terakhir @break
                                            @case('custom') {{ request('start_date') }} s/d {{ request('end_date') }} @break
                                            @default Semua Data @break
                                        @endswitch
                                    </strong>
                                @else
                                    Menampilkan: <strong>Semua Data</strong>
                                @endif
                                <span class="badge badge-primary ml-2">{{ \Carbon\Carbon::now()->format('d M Y, H:i') }}</span>
                            </small>
                        </div>
                    </div>
                </div>

                <table class="table">
                    <tr>
                        <th>No</th>
                        <th>ID Transaksi</th>
                        <th>Nominal</th>
                        <th>Status</th>
                        <th>Metode Pembayaran</th>
                        <th>Tanggal</th>
                        <th>Action</th>
                    </tr>

                    @forelse ($transaksi as $index => $item)
                        <tr>
                            <td>
                                <span class="badge badge-dark">{{ $transaksi->firstItem() + $index }}</span>
                            </td>
                            <td>{{ $item->kode_transaksi ?? 'TRX-'.$item->id }}</td>
                            <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-{{ $item->status == 'selesai' ? 'success' : 'warning' }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                @if($item->metode_pembayaran == 'qris')
                                    <span class="badge badge-info">
                                        <i class="fas fa-qrcode"></i> QRIS
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-money-bill-alt"></i> Cash
                                    </span>
                                @endif
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
                            <td colspan="7" class="text-center">Tidak ada transaksi</td>
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
// Auto submit filter transaksi dengan toggle custom dates
function autoSubmitTransaksiFilter() {
    const periode = document.querySelector('select[name="periode"]').value;
    const startContainer = document.getElementById('startDateContainerTransaksi');
    const endContainer = document.getElementById('endDateContainerTransaksi');
    
    if (periode === 'custom') {
        // Tampilkan custom date inputs tanpa auto submit
        startContainer.style.display = 'block';
        endContainer.style.display = 'block';
    } else {
        // Sembunyikan custom date inputs dan auto submit
        startContainer.style.display = 'none';
        endContainer.style.display = 'none';
        
        // Auto submit untuk periode preset
        document.getElementById('transaksiFilterForm').submit();
    }
}

// Toggle custom date inputs untuk transaksi (untuk backward compatibility)
function toggleCustomDatesTransaksi() {
    autoSubmitTransaksiFilter();
}

// Submit filter transaksi dengan validasi
function submitTransaksiFilter() {
    const periode = document.querySelector('select[name="periode"]').value;
    
    // Validasi untuk custom period
    if (periode === 'custom') {
        const startDate = document.querySelector('input[name="start_date"]').value;
        const endDate = document.querySelector('input[name="end_date"]').value;
        
        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Tidak Lengkap',
                text: 'Mohon pilih tanggal mulai dan tanggal selesai untuk periode custom',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6'
            }).then((result) => {
                return;
            });
            return false;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            Swal.fire({
                icon: 'error',
                title: 'Tanggal Tidak Valid',
                text: 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai',
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            }).then((result) => {
                return;
            });
            return false;
        }
        
        // Warning untuk periode yang terlalu panjang
        const daysDiff = Math.ceil((new Date(endDate) - new Date(startDate)) / (1000 * 60 * 60 * 24));
        if (daysDiff > 90) {
            Swal.fire({
                icon: 'info',
                title: 'Periode Panjang Terdeteksi',
                text: `Anda memilih periode ${daysDiff} hari. Data yang ditampilkan mungkin sangat banyak.`,
                showCancelButton: true,
                confirmButtonText: 'Lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('transaksiFilterForm').submit();
                }
            });
            return false;
        }
    }
    
    // Jika semua validasi passed, submit form
    document.getElementById('transaksiFilterForm').submit();
}

// Set max date untuk date inputs saat DOM ready
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    
    if (startDateInput) startDateInput.max = today;
    if (endDateInput) endDateInput.max = today;
    
    // Auto submit untuk custom date inputs saat tanggal dipilih
    if (startDateInput) {
        startDateInput.addEventListener('change', function() {
            const periode = document.querySelector('select[name="periode"]').value;
            if (periode === 'custom') {
                const startDate = this.value;
                const endDate = endDateInput.value;
                
                // Submit jika kedua tanggal sudah terisi
                if (startDate && endDate) {
                    document.getElementById('transaksiFilterForm').submit();
                }
            }
        });
    }
    
    if (endDateInput) {
        endDateInput.addEventListener('change', function() {
            const periode = document.querySelector('select[name="periode"]').value;
            if (periode === 'custom') {
                const startDate = startDateInput.value;
                const endDate = this.value;
                
                // Submit jika kedua tanggal sudah terisi
                if (startDate && endDate) {
                    document.getElementById('transaksiFilterForm').submit();
                }
            }
        });
    }
    
    // SweetAlert untuk transaksi selesai
    @if(session('transaksi_selesai'))
    const transaksiData = @json(session('transaksi_selesai'));
    Swal.fire({
        icon: 'success',
        title: 'Transaksi Berhasil Diselesaikan!',
        html: `
            <div class="text-left">
                <p><strong>Kode Transaksi:</strong> ${transaksiData.kode}</p>
                <p><strong>Metode Pembayaran:</strong> 
                    <span class="badge badge-${transaksiData.metode_pembayaran == 'qris' ? 'info' : 'secondary'}">
                        ${transaksiData.metode_pembayaran == 'qris' ? 'QRIS' : 'Cash'}
                    </span>
                </p>
                <hr>
                <p><strong>Total:</strong> Rp ${new Intl.NumberFormat('id-ID').format(transaksiData.total)}</p>
                <p><strong>Dibayarkan:</strong> Rp ${new Intl.NumberFormat('id-ID').format(transaksiData.dibayarkan)}</p>
                ${transaksiData.metode_pembayaran == 'cash' ? 
                    `<p><strong>Kembalian:</strong> Rp ${new Intl.NumberFormat('id-ID').format(transaksiData.kembalian)}</p>` : 
                    '<p><em>Pembayaran QRIS - Tidak ada kembalian</em></p>'
                }
            </div>
        `,
        confirmButtonText: '<i class="fas fa-check"></i> OK',
        confirmButtonColor: '#28a745',
        timer: 10000,
        timerProgressBar: true,
        showClass: {
            popup: 'animate__animated animate__bounceIn'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOut'
        }
    });
    @endif
});

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
    // Ambil parameter filter saat ini sesuai dengan filter baru
    const periode = '{{ request("periode") }}';
    const startDate = '{{ request("start_date") }}';
    const endDate = '{{ request("end_date") }}';
    
    // Tentukan label periode untuk ditampilkan
    let periodeLabel = 'Semua Data';
    if (periode) {
        switch(periode) {
            case 'hari': periodeLabel = 'Hari Ini'; break;
            case 'minggu': periodeLabel = 'Minggu Ini'; break;
            case 'bulan': periodeLabel = 'Bulan Ini'; break;
            case '7hari': periodeLabel = '7 Hari Terakhir'; break;
            case '30hari': periodeLabel = '30 Hari Terakhir'; break;
            case 'custom': periodeLabel = `${startDate} s/d ${endDate}`; break;
        }
    }
    
    // Konfirmasi download
    Swal.fire({
        title: 'Download Data Transaksi',
        html: `
            <div class="text-left">
                <p><strong>Filter Periode:</strong> ${periodeLabel}</p>
                ${periode === 'custom' && startDate && endDate ? 
                    `<p><strong>Rentang Tanggal:</strong> ${startDate} sampai ${endDate}</p>` : ''}
            </div>
            <hr>
            <p>Data akan didownload dalam format Excel (.csv) sesuai dengan filter yang dipilih</p>
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
            
            // Build URL dengan parameter filter baru
            let url = '/admin/transaksi/export?';
            const params = new URLSearchParams();
            
            if (periode) {
                params.append('periode', periode);
                
                if (periode === 'custom') {
                    if (startDate) params.append('start_date', startDate);
                    if (endDate) params.append('end_date', endDate);
                }
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
