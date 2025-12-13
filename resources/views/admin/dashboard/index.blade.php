<div class="container-fluid mt-2">
    <!-- Statistics Cards -->
    <div class="row">
        <!-- Total Pendapatan -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['pendapatan_bersih'], 0, ',', '.') }}</h3>
                    <p>Pendapatan Bersih</p>
                    <small class="text-light">
                        <i class="fas fa-chart-line"></i> 
                        Kotor: Rp {{ number_format($stats['total_pendapatan'], 0, ',', '.') }}
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="small-box-footer">
                    {{ $stats['periode_label'] }}
                    <i class="fas fa-arrow-circle-right"></i>
                </div>
            </div>
        </div>

        <!-- Jumlah Transaksi -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($stats['jumlah_transaksi']) }}</h3>
                    <p>Jumlah Transaksi</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="small-box-footer">
                    {{ $stats['periode_label'] }}
                    <i class="fas fa-arrow-circle-right"></i>
                </div>
            </div>
        </div>

        <!-- Total Pengeluaran -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>Rp {{ number_format($stats['total_pengeluaran'], 0, ',', '.') }}</h3>
                    <p>Total Pengeluaran</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-alt"></i>
                </div>
                <div class="small-box-footer">
                    {{ $stats['periode_label'] }}
                    <i class="fas fa-arrow-circle-right"></i>
                </div>
            </div>
        </div>

        <!-- Produk Terlaris -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['produk_terlaris']->total_terjual ?? 0 }}</h3>
                    <p>{{ $stats['produk_terlaris']->name ?? 'Tidak ada penjualan' }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="small-box-footer">
                    Produk Terlaris
                    <i class="fas fa-arrow-circle-right"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Pendapatan -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> {{ $chartData['title'] }} - {{ $stats['periode_label'] }}
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="pendapatanChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Welcome SweetAlert - hanya tampil ketika baru login
    @if(isset($justLoggedIn) && $justLoggedIn)
    Swal.fire({
        icon: 'success',
        title: 'Selamat Datang!',
        text: 'Halo {{ auth()->user()->name }}, Selamat datang di halaman admin!',
        confirmButtonText: 'Mulai Kerja',
        confirmButtonColor: '#28a745',
        timer: 3000,
        timerProgressBar: true,
        showClass: {
            popup: 'animate__animated animate__fadeInDown'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
        }
    });
    @endif
    
    // Chart.js configuration untuk data harian
    const ctx = document.getElementById('pendapatanChart').getContext('2d');
    
    const chartData = {
        labels: @json($chartData['labels']),
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: @json($chartData['data']),
            backgroundColor: function(context) {
                // Gradient color berdasarkan value
                const value = context.parsed.y;
                const maxValue = Math.max(...@json($chartData['data']));
                if (maxValue === 0) return 'rgba(54, 162, 235, 0.2)';
                const intensity = value / maxValue;
                return `rgba(54, 162, 235, ${0.2 + intensity * 0.6})`;
            },
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            borderRadius: 4,
            borderSkipped: false,
            hoverBackgroundColor: 'rgba(54, 162, 235, 0.4)',
            hoverBorderColor: 'rgba(54, 162, 235, 1)',
        }]
    };

    const config = {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                    },
                    ticks: {
                        callback: function(value, index, values) {
                            if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            } else if (value >= 1000) {
                                return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                            }
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)',
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return 'Pendapatan: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        },
                        afterLabel: function(context) {
                            const total = @json(array_sum($chartData['data']));
                            if (total === 0) return 'Belum ada transaksi';
                            const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                            return `Persentase: ${percentage}%`;
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeInOutQuart'
            }
        }
    };

    new Chart(ctx, config);
    
    // Auto refresh page setiap 5 menit untuk data real-time
    setTimeout(function(){
        location.reload();
    }, 300000); // 300000 ms = 5 menit
});
</script>