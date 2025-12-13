    <div class="row p-2">

    {{-- Bagian Pilih Produk & Form Tambah --}}
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">

                {{-- Bagian Katalog Produk (Seperti Gambar) --}}
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="m-0">Daftar Produk</h5>
                    <form method="GET" class="d-flex" style="max-width: 300px;">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control me-2" placeholder="Search barang...">
                        <button class="btn btn-primary">Cari</button>
                    </form>
                </div>

{{-- Katalog Produk --}}
<div class="row">
    @foreach ($produk as $item)
        <div class="col-md-3 mb-3">
            <div class="card h-100 shadow-sm produk-card" 
                 data-id="{{ $item->id }}" 
                 data-name="{{ $item->name }}" 
                 data-harga="{{ $item->harga }}">
                <img src="{{ asset($item->gambar) }}" 
                     class="card-img-top" 
                     style="height:150px; object-fit:contain;" 
                     alt="{{ $item->name }}">
                <div class="card-body p-2">
                    <h6 class="fw-bold text-center">{{ $item->name }}</h6>
                    <small>Harga: Rp. {{ format_rupiah($item->harga) }}</small><br>

                    {{-- Kontrol QTY --}}
                    <div class="d-flex mt-2">
                        <button type="button" class="btn btn-primary btn-sm minus-btn">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" value="0" 
                               class="form-control mx-2 text-center qty-input" 
                               style="max-width:70px;">
                        <button type="button" class="btn btn-primary btn-sm plus-btn">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

            </div>
            <hr>
            {{-- Bagian Daftar Transaksi --}}
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nama Produk</th>
                        <th>QTY item</th>
                        <th>Subtotal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="table-body">
                    {{-- Tampilkan data dari database --}}
                    @foreach($transaksi_detail as $detail)
                    <tr id="row-{{ $detail->id }}">
                        <td>{{ $detail->produk_name }}</td>
                        <td>{{ $detail->qty }}</td>
                        <td>Rp. {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                        <td>
                            <a href="#" onclick="deleteItemById({{ $detail->id }}, {{ $detail->produk_id }})" class="text-danger">
                                <i class="fas fa-times"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <hr>

{{-- Form Tambah Produk ke Transaksi --}}
<form action="/admin/transaksi/detail/create" method="POST" id="form-transaksi">
    @csrf
    <input type="hidden" name="transaksi_id" value="{{ Request::segment(3) }}">

    {{-- input hidden untuk semua produk terpilih akan diisi via JS --}}
    <div id="produk-container"></div>
</form>
            </div>
        </div>
    </div>
</div>

{{-- Bagian Pembayaran --}}
<div class="row p-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">

                {{-- Total & QTY Total --}}
                <div class="form-group mb-3">
                    <label>QTY Total</label>
                    <input type="number" id="qty_total_input" class="form-control" value="{{ $transaksi_detail->sum('qty') }}" readonly>
                </div>

                <div class="form-group mb-3">
                    <label>Total Belanja</label>
                    <input type="number" id="total_input" class="form-control" value="{{ $transaksi->total }}" readonly>
                </div>

                {{-- Metode Pembayaran --}}
                <div class="form-group mb-3">
                    <label class="mb-2">Metode Pembayaran</label>
                    <div class="btn-group w-100" role="group" aria-label="Metode Pembayaran">
                        <button type="button" class="btn btn-secondary metode-btn active" data-metode="cash" id="btn-cash" style="transition: all 0.3s ease;">
                            <i class="fas fa-money-bill-alt"></i> Cash
                        </button>
                        <button type="button" class="btn btn-secondary metode-btn" data-metode="qris" id="btn-qris" style="transition: all 0.3s ease;">
                            <i class="fas fa-qrcode"></i> QRIS
                        </button>
                    </div>
                    <input type="hidden" name="metode_pembayaran" id="metode_pembayaran" value="cash">
                </div>

<style>
.metode-btn.active {
    background-color: #007bff !important;
    border-color: #007bff !important;
    color: white !important;
}
.metode-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>

                {{-- Dibayarkan --}}
                <div class="form-group mb-3" id="dibayarkan-container">
                    <label>Dibayarkan</label>
                    <input type="number" id="dibayarkan_input" name="dibayarkan" class="form-control">
                </div>

                {{-- Kembalian --}}
                <div class="form-group" id="kembalian-container">
                    <label>Uang Kembalian</label>
                    <input type="text" id="kembalian_input" class="form-control" value="Rp. 0" readonly>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between gap-4 mt-3">
            <a href="/admin/transaksi" class="btn btn-info px-4">Kembali</a>

            {{-- Tombol selesai simpan transaksi --}}
            {{-- <form action="/admin/transaksi/detail/selesai/{{ Request::segment(3) }}" method="POST">
                @csrf
                <input type="hidden" name="total" id="total_hidden">
                <input type="hidden" name="dibayarkan" id="dibayarkan_hidden">
                <input type="hidden" name="kembalian" id="kembalian_hidden">
                <button type="submit" class="btn btn-success px-4">Selesai</button>
            </form> --}}
            <form id="form-selesai" action="{{ route('transaksi.selesai', Request::segment(3)) }}" method="POST">
                @csrf
                <input type="hidden" name="total" id="total_hidden">
                <input type="hidden" name="dibayarkan" id="dibayarkan_hidden">
                <input type="hidden" name="kembalian" id="kembalian_hidden">
                <input type="hidden" name="metode_pembayaran" id="metode_pembayaran_hidden" value="cash">
                <button type="submit" class="btn btn-success px-4">Selesai</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    let produkData = {}; // {id: {name, harga, qty}}
    let totalQtyGlobal = 0;
    let totalSubtotalGlobal = 0;

    // Load initial totals saat halaman dimuat
    updateTotalsFromServer();

    // Tampilkan pesan error jika ada
    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif

    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif

    document.querySelectorAll(".produk-card").forEach(card => {
        let id = card.dataset.id;
        let name = card.dataset.name;
        let harga = parseInt(card.dataset.harga);
        let qtyInput = card.querySelector(".qty-input");
        let plusBtn = card.querySelector(".plus-btn");
        let minusBtn = card.querySelector(".minus-btn");

        // Load qty dari database jika ada
        let currentQty = 0;
        @foreach($transaksi_detail as $detail)
            if ({{ $detail->produk_id }} == id) {
                currentQty = {{ $detail->qty }};
                qtyInput.value = currentQty;
            }
        @endforeach

        produkData[id] = { name: name, harga: harga, qty: currentQty };

        plusBtn.addEventListener("click", () => {
            qtyInput.value = parseInt(qtyInput.value) + 1;
            produkData[id].qty = parseInt(qtyInput.value);
            updateView();
            autoSaveItem(id); // Auto save setiap perubahan
        });

        minusBtn.addEventListener("click", () => {
            if (parseInt(qtyInput.value) > 0) {
                qtyInput.value = parseInt(qtyInput.value) - 1;
                produkData[id].qty = parseInt(qtyInput.value);
                updateView();
                autoSaveItem(id); // Auto save setiap perubahan
            }
        });

        qtyInput.addEventListener("change", () => {
            if (qtyInput.value < 0) qtyInput.value = 0;
            produkData[id].qty = parseInt(qtyInput.value);
            updateView();
            autoSaveItem(id); // Auto save setiap perubahan
        });
    });

    function updateView() {
        // Hanya update display qty dan subtotal untuk tampilan UI
        // Tabel akan di-update via AJAX dari server
        totalQtyGlobal = 0;
        totalSubtotalGlobal = 0;

        Object.keys(produkData).forEach(id => {
            let p = produkData[id];
            if (p.qty > 0) {
                let subtotal = p.qty * p.harga;
                totalQtyGlobal += p.qty;
                totalSubtotalGlobal += subtotal;
            }
        });

        // Update tampilan sementara (akan di-override oleh data dari server)
        // document.getElementById("qty_total_input").value = totalQtyGlobal;
        // document.getElementById("total_input").value = totalSubtotalGlobal;
    }

    // Bagian Metode Pembayaran
    const metodeBtns = document.querySelectorAll('.metode-btn');
    const metodeInput = document.getElementById('metode_pembayaran');
    const metodeHidden = document.getElementById('metode_pembayaran_hidden');
    const dibayarkanContainer = document.getElementById('dibayarkan-container');
    const kembalianContainer = document.getElementById('kembalian-container');

    metodeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            metodeBtns.forEach(b => b.classList.remove('active', 'btn-primary'));
            metodeBtns.forEach(b => b.classList.add('btn-secondary'));
            
            // Add active class to clicked button
            this.classList.remove('btn-secondary');
            this.classList.add('active', 'btn-primary');
            
            const metode = this.getAttribute('data-metode');
            metodeInput.value = metode;
            metodeHidden.value = metode;
            
            if (metode === 'qris') {
                // Hide dibayarkan dan kembalian untuk QRIS
                dibayarkanContainer.style.display = 'none';
                kembalianContainer.style.display = 'none';
                
                // Set nilai default untuk QRIS (dibayar sesuai total)
                const total = parseInt(document.getElementById('total_input').value || 0);
                document.getElementById('dibayarkan_input').value = total;
                document.getElementById('kembalian_input').value = "Rp. 0";
                
                // Set hidden inputs
                totalHidden.value = total;
                dibayarkanHidden.value = total;
                kembalianHidden.value = 0;
            } else {
                // Show dibayarkan dan kembalian untuk Cash
                dibayarkanContainer.style.display = 'block';
                kembalianContainer.style.display = 'block';
                
                // Reset nilai
                document.getElementById('dibayarkan_input').value = '';
                document.getElementById('kembalian_input').value = "Rp. 0";
            }
        });
    });

    // Bagian Pembayaran
    let dibayarkanInput = document.getElementById("dibayarkan_input");
    let kembalianInput = document.getElementById("kembalian_input");

    let totalHidden = document.getElementById("total_hidden");
    let dibayarkanHidden = document.getElementById("dibayarkan_hidden");
    let kembalianHidden = document.getElementById("kembalian_hidden");

    // Auto calculate saat input dibayarkan
    dibayarkanInput.addEventListener('input', function () {
        const total = parseInt(document.getElementById('total_input').value || 0);
        const dibayarkan = parseInt(dibayarkanInput.value || 0);
        
        if (total > 0 && dibayarkan >= total) {
            const kembalian = dibayarkan - total;
            kembalianInput.value = "Rp. " + new Intl.NumberFormat("id-ID").format(kembalian);
            
            // Set hidden inputs
            totalHidden.value = total;
            dibayarkanHidden.value = dibayarkan;
            kembalianHidden.value = kembalian;
        } else if (dibayarkan > 0) {
            kembalianInput.value = "Rp. 0";
        }
    });

    const formSelesai = document.getElementById('form-selesai');

    formSelesai.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Ambil total dari input yang sudah diupdate dari database
        const total = parseInt(document.getElementById('total_input').value || 0, 10);
        const dibayarkan = parseInt(document.getElementById('dibayarkan_input').value || 0, 10);

        // Validasi: harus ada total (berarti ada item)
        if (total <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Ada Item',
                text: 'Belum ada item yang dipilih. Silakan pilih produk terlebih dahulu.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        // Validasi pembayaran
        if (dibayarkan <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Input Pembayaran',
                text: 'Silakan masukkan jumlah uang yang dibayarkan!',
                confirmButtonText: 'OK',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        if (dibayarkan < total) {
            const totalFormatted = new Intl.NumberFormat("id-ID").format(total);
            const dibayarkanFormatted = new Intl.NumberFormat("id-ID").format(dibayarkan);
            const kurang = new Intl.NumberFormat("id-ID").format(total - dibayarkan);
            
            Swal.fire({
                icon: 'error',
                title: 'Pembayaran Tidak Cukup',
                html: `
                    <div class="text-left">
                        <p><strong>Total Belanja:</strong> Rp ${totalFormatted}</p>
                        <p><strong>Dibayarkan:</strong> Rp ${dibayarkanFormatted}</p>
                        <p><strong>Kurang:</strong> <span class="text-danger">Rp ${kurang}</span></p>
                    </div>
                    <hr>
                    <p>Uang yang dibayarkan tidak boleh kurang dari total belanja!</p>
                `,
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
            return;
        }

        const kembalian = dibayarkan - total;

        // Konfirmasi sebelum menyelesaikan
        const totalFormatted = new Intl.NumberFormat("id-ID").format(total);
        const kembalianFormatted = new Intl.NumberFormat("id-ID").format(kembalian);
        
        const result = await Swal.fire({
            title: 'Konfirmasi Selesai Transaksi',
            html: `
                <div class="text-left">
                    <p><strong>Total:</strong> Rp ${totalFormatted}</p>
                    <p><strong>Dibayar:</strong> Rp ${new Intl.NumberFormat("id-ID").format(dibayarkan)}</p>
                    <p><strong>Kembalian:</strong> Rp ${kembalianFormatted}</p>
                </div>
                <hr>
                <p>Yakin ingin menyelesaikan transaksi ini?</p>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-check"></i> Ya, Selesaikan!',
            cancelButtonText: '<i class="fas fa-times"></i> Batal'
        });
        
        if (!result.isConfirmed) {
            return;
        }
        
        // Set hidden inputs untuk dikirim ke server
        document.getElementById('total_hidden').value = total;
        document.getElementById('dibayarkan_hidden').value = dibayarkan;
        document.getElementById('kembalian_hidden').value = kembalian;

        // Tampilkan loading
        const submitBtn = formSelesai.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
        submitBtn.disabled = true;

        // Karena item sudah tersimpan via auto-save, langsung submit form selesai
        formSelesai.submit();
    });

    // Function untuk auto save item ke database
    function autoSaveItem(produkId) {
        const produk = produkData[produkId];
        const transaksiId = "{{ Request::segment(3) }}";
        
        if (produk.qty <= 0) {
            // Jika qty 0, hapus dari database jika ada
            deleteItemFromDB(produkId);
            return;
        }

        const subtotal = produk.qty * produk.harga;
        
        // Show loading
        showToast('Menyimpan...', 'info');
        
        // Kirim ke server via AJAX
        const formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('transaksi_id', transaksiId);
        formData.append('produk_id', produkId);
        formData.append('produk_name', produk.name);
        formData.append('qty', produk.qty);
        formData.append('subtotal', subtotal);

        fetch('/admin/transaksi/detail/create', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update tampilan total dari server
                updateTotalsFromServer();
                // showToast('Item tersimpan!', 'success');
            } else {
                showToast('Gagal menyimpan: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Gagal menyimpan item!', 'error');
        });
    }

    // Function untuk hapus item dari database
    function deleteItemFromDB(produkId) {
        fetch(`/admin/transaksi/detail/delete-by-produk?produk_id=${produkId}&transaksi_id={{ Request::segment(3) }}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateTotalsFromServer();
                showToast('Item berhasil dihapus!', 'success');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Function untuk update total dari server
    function updateTotalsFromServer() {
        fetch(`/admin/transaksi/get-totals/{{ Request::segment(3) }}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('qty_total_input').value = data.qty_total;
            document.getElementById('total_input').value = data.total;
            document.getElementById('total_hidden').value = data.total;
            
            // Refresh tabel untuk menampilkan data terbaru
            refreshTable();
        });
    }

    // Function untuk refresh tabel dari server
    function refreshTable() {
        fetch(`/admin/transaksi/{{ Request::segment(3) }}/get-details`)
        .then(response => response.json())
        .then(data => {
            const tableBody = document.getElementById('table-body');
            tableBody.innerHTML = '';
            
            data.details.forEach(detail => {
                const row = `
                    <tr id="row-${detail.id}">
                        <td>${detail.produk_name}</td>
                        <td>${detail.qty}</td>
                        <td>Rp. ${new Intl.NumberFormat("id-ID").format(detail.subtotal)}</td>
                        <td>
                            <a href="#" onclick="deleteItemById(${detail.id}, ${detail.produk_id})" class="text-danger">
                                <i class="fas fa-times"></i>
                            </a>
                        </td>
                    </tr>
                `;
                tableBody.innerHTML += row;
            });
        });
    }

    // Function untuk hapus item berdasarkan ID detail
    window.deleteItemById = function(detailId, produkId) {
        // Fallback jika SweetAlert tidak tersedia
        if (typeof Swal === 'undefined') {
            if (confirm('Hapus item ini dari transaksi?')) {
                deleteItemProcess(detailId, produkId);
            }
            return;
        }

        Swal.fire({
            title: 'Konfirmasi Hapus',
            text: 'Hapus item ini dari transaksi?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="fas fa-trash"></i> Ya, Hapus!',
            cancelButtonText: '<i class="fas fa-times"></i> Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                deleteItemProcess(detailId, produkId);
            }
        }).catch((error) => {
            console.error('SweetAlert error:', error);
            if (confirm('Hapus item ini dari transaksi?')) {
                deleteItemProcess(detailId, produkId);
            }
        });
    }

    // Function untuk proses delete item
    function deleteItemProcess(detailId, produkId) {
        fetch(`/admin/transaksi/detail/delete?id=${detailId}`, {
            method: 'GET'
        })
        .then(response => {
            if (response.ok) {
                // Reset qty di form
                const qtyInput = document.querySelector(`.produk-card[data-id="${produkId}"] .qty-input`);
                if (qtyInput) {
                    qtyInput.value = 0;
                    produkData[produkId].qty = 0;
                }
                
                // Update totals dan refresh tabel
                updateTotalsFromServer();
                showToast('Item berhasil dihapus!', 'success');
            } else {
                showToast('Gagal menghapus item!', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Gagal menghapus item!', 'error');
        });
    }

    // Function untuk menampilkan notifikasi
    function showToast(message, type) {
        // Remove existing toasts
        document.querySelectorAll('.custom-toast').forEach(t => t.remove());
        
        // Simple toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : (type === 'info' ? 'info' : 'danger')} position-fixed custom-toast`;
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.style.minWidth = '250px';
        toast.innerHTML = message;
        document.body.appendChild(toast);
        
        if (type !== 'info') {
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    }
});
</script>

