<div class="row p-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5><b>{{ $title }}</b></h5>

                <div class="d-flex justify-content-between mb-2">
                    <a href="{{ route('admin.pengeluaran.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Pengeluaran
                    </a>
                </div>

                {{-- Filter dengan style dashboard --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.pengeluaran.index') }}" class="row align-items-end" id="pengeluaranFilterForm">
                            <div class="col-md-3">
                                <label class="mb-1"><i class="fas fa-clock"></i> Periode:</label>
                                <select name="periode" class="form-control" onchange="autoSubmitPengeluaranFilter()">
                                    <option value="" {{ request('periode') == '' ? 'selected' : '' }}>Semua Data</option>
                                    <option value="hari" {{ request('periode') == 'hari' ? 'selected' : '' }}>Hari Ini</option>
                                    <option value="minggu" {{ request('periode') == 'minggu' ? 'selected' : '' }}>Minggu Ini</option>
                                    <option value="bulan" {{ request('periode') == 'bulan' ? 'selected' : '' }}>Bulan Ini</option>
                                    <option value="7hari" {{ request('periode') == '7hari' ? 'selected' : '' }}>7 Hari Terakhir</option>
                                    <option value="30hari" {{ request('periode') == '30hari' ? 'selected' : '' }}>30 Hari Terakhir</option>
                                    <option value="custom" {{ request('periode') == 'custom' ? 'selected' : '' }}>Periode Custom</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3" id="startDateContainerPengeluaran" style="{{ request('periode') == 'custom' ? '' : 'display: none;' }}">
                                <label class="mb-1">Tanggal Mulai:</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') ?? \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                            
                            <div class="col-md-3" id="endDateContainerPengeluaran" style="{{ request('periode') == 'custom' ? '' : 'display: none;' }}">
                                <label class="mb-1">Tanggal Selesai:</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') ?? \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                            
                            <div class="col-md-3" style="display: none;">
                                <button type="button" class="btn btn-primary" onclick="submitPengeluaranFilter()">
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
                            <th>Tanggal</th>
                            <th>Jenis Pengeluaran</th>
                            <th>Nominal</th>
                            <th>Keterangan</th>
                            <th>Action</th>
                        </tr>
                    <tbody>
                        @forelse ($pengeluaran as $item)
                            <tr>
                                <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $item->jenis_pengeluaran }}</span>
                                </td>
                                <td>
                                    <strong class="text-danger">Rp {{ number_format($item->nominal, 0, ',', '.') }}</strong>
                                </td>
                                <td>{{ $item->keterangan ?? '-' }}</td>
                                <td>
                                    <div class="d-flex">
                                        <a href="{{ route('admin.pengeluaran.edit', $item->id) }}" class="btn btn-warning btn-sm" title="Edit Pengeluaran">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <form action="{{ route('admin.pengeluaran.destroy', $item->id) }}" method="POST" id="delete-form-{{ $item->id }}">
                                            @method('delete')
                                            @csrf
                                            <button type="button" onclick="confirmDeletePengeluaran({{ $item->id }}, '{{ addslashes($item->jenis_pengeluaran) }}')" class="btn btn-danger btn-sm ml-1" title="Hapus Pengeluaran">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle"></i> Tidak ada data pengeluaran untuk periode ini
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        {{-- Total Pengeluaran --}}
                        <div class="alert mb-0">
                            <h6 class="mb-0">
                                <i class="fas fa-money-bill-alt mr-1"></i>
                                Total: <strong>Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</strong>
                            </h6>
                        </div>
                    </div>
                    
                    <div>
                        {{ $pengeluaran->links() }}
                    </div>
                    
                    <div>
                        <button type="button" onclick="downloadPengeluaran()" class="btn btn-success">
                            <i class="fas fa-download"></i> Download Laporan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto submit filter pengeluaran dengan toggle custom dates
function autoSubmitPengeluaranFilter() {
    const periode = document.querySelector('select[name="periode"]').value;
    const startContainer = document.getElementById('startDateContainerPengeluaran');
    const endContainer = document.getElementById('endDateContainerPengeluaran');
    
    if (periode === 'custom') {
        // Tampilkan custom date inputs tanpa auto submit
        startContainer.style.display = 'block';
        endContainer.style.display = 'block';
    } else {
        // Sembunyikan custom date inputs dan auto submit
        startContainer.style.display = 'none';
        endContainer.style.display = 'none';
        
        // Auto submit untuk periode preset
        document.getElementById('pengeluaranFilterForm').submit();
    }
}

// Toggle custom date inputs untuk pengeluaran (untuk backward compatibility)
function toggleCustomDatesPengeluaran() {
    autoSubmitPengeluaranFilter();
}

// Submit filter pengeluaran dengan validasi
function submitPengeluaranFilter() {
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
            });
            return false;
        }
    }
    
    // Submit form
    document.getElementById('pengeluaranFilterForm').submit();
}

// Confirm delete pengeluaran
function confirmDeletePengeluaran(pengeluaranId, jenisPengeluaran) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        html: `Yakin ingin hapus pengeluaran <strong>${jenisPengeluaran}</strong>?<br><small class="text-muted">Data yang dihapus tidak dapat dikembalikan.</small>`,
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
            document.getElementById('delete-form-' + pengeluaranId).submit();
        }
    });
}

// Download pengeluaran function
function downloadPengeluaran() {
    const periode = '{{ request("periode") }}';
    const startDate = '{{ request("start_date") }}';
    const endDate = '{{ request("end_date") }}';
    
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
    
    Swal.fire({
        title: 'Download Laporan Pengeluaran',
        html: `
            <div class="text-left">
                <p><strong>Filter Periode:</strong> ${periodeLabel}</p>
                ${periode === 'custom' && startDate && endDate ? 
                    `<p><strong>Rentang Tanggal:</strong> ${startDate} sampai ${endDate}</p>` : ''}
            </div>
            <hr>
            <p>Laporan akan didownload dalam format Excel (.csv) sesuai dengan filter yang dipilih</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-download"></i> Download',
        cancelButtonText: '<i class="fas fa-times"></i> Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang menyiapkan laporan untuk download',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            let url = '{{ route("admin.pengeluaran.export") }}?';
            const params = new URLSearchParams();
            
            if (periode) {
                params.append('periode', periode);
                
                if (periode === 'custom') {
                    if (startDate) params.append('start_date', startDate);
                    if (endDate) params.append('end_date', endDate);
                }
            }
            
            url += params.toString();
            window.location.href = url;
            
            setTimeout(() => {
                Swal.close();
            }, 2000);
        }
    });
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
                    document.getElementById('pengeluaranFilterForm').submit();
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
                    document.getElementById('pengeluaranFilterForm').submit();
                }
            }
        });
    }
});
</script>