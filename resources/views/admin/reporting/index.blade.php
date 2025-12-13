<div class="row p-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5><b>{{ $title }}</b></h5>

                {{-- Filter dengan style dashboard --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="{{ url('/admin/reporting') }}" class="row align-items-end" id="reportingFilterForm">
                            <div class="col-md-3">
                                <label class="mb-1"><i class="fas fa-clock"></i> Periode:</label>
                                <select name="periode" class="form-control" onchange="autoSubmitReportingFilter()">
                                    <option value="hari" {{ !request('periode') || request('periode') == 'hari' ? 'selected' : '' }}>Hari Ini</option>
                                    <option value="minggu" {{ request('periode') == 'minggu' ? 'selected' : '' }}>Minggu Ini</option>
                                    <option value="bulan" {{ request('periode') == 'bulan' ? 'selected' : '' }}>Bulan Ini</option>
                                    <option value="7hari" {{ request('periode') == '7hari' ? 'selected' : '' }}>7 Hari Terakhir</option>
                                    <option value="30hari" {{ request('periode') == '30hari' ? 'selected' : '' }}>30 Hari Terakhir</option>
                                    <option value="semua" {{ request('periode') == 'semua' ? 'selected' : '' }}>Semua Data</option>
                                    <option value="custom" {{ request('periode') == 'custom' ? 'selected' : '' }}>Periode Custom</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3" id="startDateContainerReporting" style="{{ request('periode') == 'custom' ? '' : 'display: none;' }}">
                                <label class="mb-1">Tanggal Mulai:</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') ?? \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                            
                            <div class="col-md-3" id="endDateContainerReporting" style="{{ request('periode') == 'custom' ? '' : 'display: none;' }}">
                                <label class="mb-1">Tanggal Selesai:</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') ?? \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>
                            
                            <div class="col-md-3" style="display: none;">
                                <button type="button" class="btn btn-primary" onclick="submitReportingFilter()">
                                    <i class="fas fa-search"></i> Filter Data
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> 
                                Menampilkan data: <strong>
                                    @php
                                        $periode = request('periode') ?: 'hari';
                                    @endphp
                                    @switch($periode)
                                        @case('hari') Hari Ini @break
                                        @case('minggu') Minggu Ini @break
                                        @case('bulan') Bulan Ini @break
                                        @case('7hari') 7 Hari Terakhir @break
                                        @case('30hari') 30 Hari Terakhir @break
                                        @case('semua') Semua Data @break
                                        @case('custom') {{ request('start_date') }} s/d {{ request('end_date') }} @break
                                        @default Hari Ini @break
                                    @endswitch
                                </strong>
                                <span class="badge badge-primary ml-2">{{ \Carbon\Carbon::now()->format('d M Y, H:i') }}</span>
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Summary Statistics Cards --}}
                <div class="row mb-4">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>Rp {{ number_format($stats['pendapatan_bersih'] ?? 0, 0, ',', '.') }}</h3>
                                <p>Pendapatan Bersih</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>Rp {{ number_format($stats['pendapatan_kotor'] ?? 0, 0, ',', '.') }}</h3>
                                <p>Pendapatan Kotor</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3>Rp {{ number_format($stats['total_pengeluaran'] ?? 0, 0, ',', '.') }}</h3>
                                <p>Total Pengeluaran</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ $stats['total_transaksi'] ?? 0 }}</h3>
                                <p>Total Transaksi</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment Method Statistics Cards --}}
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="small-box bg-gradient-secondary">
                            <div class="inner">
                                <h3>Rp {{ number_format($stats['pendapatan_cash'] ?? 0, 0, ',', '.') }}</h3>
                                <p>Pendapatan Cash <small>({{ $stats['jumlah_transaksi_cash'] ?? 0 }} transaksi)</small></p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill-alt"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="small-box bg-gradient-cyan">
                            <div class="inner">
                                <h3>Rp {{ number_format($stats['pendapatan_qris'] ?? 0, 0, ',', '.') }}</h3>
                                <p>Pendapatan QRIS <small>({{ $stats['jumlah_transaksi_qris'] ?? 0 }} transaksi)</small></p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-qrcode"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Data Tables with Tabs --}}
                <div class="card">
                    <div class="card-header p-0 pt-1">
                        <ul class="nav nav-tabs" id="reportingTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="transaksi-tab" data-toggle="tab" href="#transaksi" role="tab">
                                    <i class="fas fa-shopping-cart"></i> Data Transaksi
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="pengeluaran-tab" data-toggle="tab" href="#pengeluaran" role="tab">
                                    <i class="fas fa-credit-card"></i> Data Pengeluaran
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="reportingTabsContent">
                            <div class="tab-pane fade show active" id="transaksi" role="tabpanel">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Total</th>
                                            <th>Metode Pembayaran</th>
                                            <th>Jumlah Item</th>
                                            <th>Detail Produk</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($transaksi as $index => $item)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-dark">{{ $transaksi->firstItem() + $index }}</span>
                                                </td>
                                                <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                                                <td>
                                                    <strong class="text-success">Rp {{ number_format($item->total, 0, ',', '.') }}</strong>
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
                                                <td>
                                                    <span class="badge badge-info">{{ $item->details->sum('qty') }} item</span>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap">
                                                        @foreach($item->details as $detail)
                                                            <span class="badge badge-secondary mr-1 mb-1">
                                                                {{ $detail->produk_name }} ({{ $detail->qty }}x)
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">
                                                    <div class="alert alert-info mb-0">
                                                        <i class="fas fa-info-circle"></i> Tidak ada data transaksi untuk periode ini
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        {{ $transaksi->links() }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane fade" id="pengeluaran" role="tabpanel">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal</th>
                                            <th>Jenis Pengeluaran</th>
                                            <th>Nominal</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pengeluaran as $index => $item)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-dark">{{ $pengeluaran->firstItem() + $index }}</span>
                                                </td>
                                                <td>{{ $item->created_at ? $item->created_at->format('d-m-Y H:i') : \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                                                <td>
                                                    <span class="badge badge-warning">{{ $item->jenis_pengeluaran }}</span>
                                                </td>
                                                <td>
                                                    <strong class="text-danger">Rp {{ number_format($item->nominal, 0, ',', '.') }}</strong>
                                                </td>
                                                <td>{{ $item->keterangan ?? '-' }}</td>
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
                                
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        {{ $pengeluaran->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <button type="button" onclick="downloadReporting()" class="btn btn-success">
                        <i class="fas fa-download"></i> Download Laporan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto submit filter reporting dengan toggle custom dates
function autoSubmitReportingFilter() {
    const periode = document.querySelector('select[name="periode"]').value;
    const startContainer = document.getElementById('startDateContainerReporting');
    const endContainer = document.getElementById('endDateContainerReporting');
    
    if (periode === 'custom') {
        // Tampilkan custom date inputs tanpa auto submit
        startContainer.style.display = 'block';
        endContainer.style.display = 'block';
    } else {
        // Sembunyikan custom date inputs dan auto submit
        startContainer.style.display = 'none';
        endContainer.style.display = 'none';
        
        // Auto submit untuk periode preset
        document.getElementById('reportingFilterForm').submit();
    }
}

// Toggle custom date inputs untuk reporting (untuk backward compatibility)
function toggleCustomDatesReporting() {
    autoSubmitReportingFilter();
}

// Submit filter reporting dengan validasi
function submitReportingFilter() {
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
                    document.getElementById('reportingFilterForm').submit();
                }
            });
            return false;
        }
    }
    
    // Jika semua validasi passed, submit form
    document.getElementById('reportingFilterForm').submit();
}

// Download reporting function
function downloadReporting() {
    // Ambil parameter filter saat ini
    const periode = '{{ request("periode") ?: "hari" }}';
    const startDate = '{{ request("start_date") }}';
    const endDate = '{{ request("end_date") }}';
    
    // Tentukan label periode untuk ditampilkan
    let periodeLabel = 'Hari Ini';
    if (periode) {
        switch(periode) {
            case 'hari': periodeLabel = 'Hari Ini'; break;
            case 'minggu': periodeLabel = 'Minggu Ini'; break;
            case 'bulan': periodeLabel = 'Bulan Ini'; break;
            case '7hari': periodeLabel = '7 Hari Terakhir'; break;
            case '30hari': periodeLabel = '30 Hari Terakhir'; break;
            case 'semua': periodeLabel = 'Semua Data'; break;
            case 'custom': periodeLabel = `${startDate} s/d ${endDate}`; break;
        }
    }
    
    // Konfirmasi download
    Swal.fire({
        title: 'Download Laporan Keuangan Komprehensif',
        html: `
            <div class="text-left">
                <p><strong>Filter Periode:</strong> ${periodeLabel}</p>
                ${periode === 'custom' && startDate && endDate ? 
                    `<p><strong>Rentang Tanggal:</strong> ${startDate} sampai ${endDate}</p>` : ''}
                <p><strong>Data yang akan didownload:</strong></p>
                <ul class="text-left">
                    <li><strong>Ringkasan Keuangan:</strong> Pendapatan Bersih, Pendapatan Kotor, Total Pengeluaran</li>
                    <li><strong>Detail Transaksi:</strong> Semua transaksi penjualan dengan detail produk</li>
                    <li><strong>Detail Pengeluaran:</strong> Semua pengeluaran dengan kategori dan keterangan</li>
                </ul>
            </div>
            <hr>
            <p>Laporan akan didownload dalam format Excel (.csv) dengan data keuangan lengkap</p>
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
                text: 'Sedang menyiapkan laporan untuk download',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Build URL dengan parameter filter
            let url = '/admin/reporting/export?';
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
                    document.getElementById('reportingFilterForm').submit();
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
                    document.getElementById('reportingFilterForm').submit();
                }
            }
        });
    }
});
</script>