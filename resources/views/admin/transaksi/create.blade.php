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
                <img src="{{ asset(''.$item->gambar) }}" 
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
                    {{-- JS akan mengisi tabel ini secara dinamis --}}
                </tbody>
            </table>
            <hr>

{{-- Form Tambah Produk ke Transaksi --}}
<form action="/admin/transaksi/detail/create" method="POST" id="form-transaksi">
    @csrf
    <input type="hidden" name="transaksi_id" value="{{ Request::segment(3) }}">

    {{-- input hidden untuk semua produk terpilih akan diisi via JS --}}
    <div id="produk-container"></div>

    {{-- <div class="row mb-3">
        <div class="col-md-4"><label>QTY Total</label></div>
        <div class="col-md-8">
            <h5 class="m-0" id="qty_total">0</h5>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4"><label>Total</label></div>
        <div class="col-md-8">
            <h5 class="m-0" id="subtotal_text">Rp. 0</h5>
        </div>
    </div> --}}
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
                    <input type="number" id="qty_total_input" class="form-control" value="0" readonly>
                </div>

                <div class="form-group mb-3">
                    <label>Total Belanja</label>
                    <input type="number" id="total_input" class="form-control" value="0" readonly>
                </div>

                {{-- Dibayarkan --}}
                <div class="form-group mb-3">
                    <label>Dibayarkan</label>
                    <input type="number" id="dibayarkan_input" name="dibayarkan" class="form-control">
                </div>

                {{-- Tombol Hitung --}}
                <button type="button" id="hitung-btn" class="btn btn-primary w-100">Hitung</button>

                <hr>

                {{-- Kembalian --}}
                <div class="form-group">
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

    document.querySelectorAll(".produk-card").forEach(card => {
        let id = card.dataset.id;
        let name = card.dataset.name;
        let harga = parseInt(card.dataset.harga);
        let qtyInput = card.querySelector(".qty-input");
        let plusBtn = card.querySelector(".plus-btn");
        let minusBtn = card.querySelector(".minus-btn");

        produkData[id] = { name: name, harga: harga, qty: 0 };

        plusBtn.addEventListener("click", () => {
            qtyInput.value = parseInt(qtyInput.value) + 1;
            produkData[id].qty = parseInt(qtyInput.value);
            updateView();
        });

        minusBtn.addEventListener("click", () => {
            if (parseInt(qtyInput.value) > 0) {
                qtyInput.value = parseInt(qtyInput.value) - 1;
                produkData[id].qty = parseInt(qtyInput.value);
                updateView();
            }
        });

        qtyInput.addEventListener("input", () => {
            if (qtyInput.value < 0) qtyInput.value = 0;
            produkData[id].qty = parseInt(qtyInput.value);
            updateView();
        });
    });

    function updateView() {
        totalQtyGlobal = 0;
        totalSubtotalGlobal = 0;

        let container = document.getElementById("produk-container");
        container.innerHTML = "";
        let tableBody = document.getElementById("table-body");
        tableBody.innerHTML = "";

        Object.keys(produkData).forEach(id => {
            let p = produkData[id];
            if (p.qty > 0) {
                let subtotal = p.qty * p.harga;
                totalQtyGlobal += p.qty;
                totalSubtotalGlobal += subtotal;

                // hidden input utk form
                container.innerHTML += `
                    <input type="hidden" name="produk_id[]" value="${id}">
                    <input type="hidden" name="produk_name[]" value="${p.name}">
                    <input type="hidden" name="qty[]" value="${p.qty}">
                    <input type="hidden" name="harga[]" value="${p.harga}">
                    <input type="hidden" name="subtotal[]" value="${subtotal}">
                `;

                // tabel transaksi
                tableBody.innerHTML += `
                    <tr>
                        <td>${p.name}</td>
                        <td>${p.qty}</td>
                        <td>Rp. ${new Intl.NumberFormat("id-ID").format(subtotal)}</td>
                        <td><a href="#" class="text-danger remove-item" data-id="${id}">
                            <i class="fas fa-times"></i></a></td>
                    </tr>
                `;
            }
        });

        // Update langsung ke bagian pembayaran
        document.getElementById("qty_total_input").value = totalQtyGlobal;
        document.getElementById("total_input").value = totalSubtotalGlobal;

        // Event hapus item
        document.querySelectorAll(".remove-item").forEach(btn => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                let id = btn.dataset.id;
                produkData[id].qty = 0;
                document.querySelector(`.produk-card[data-id="${id}"] .qty-input`).value = 0;
                updateView();
            });
        });
    }

    // Bagian Pembayaran
    let dibayarkanInput = document.getElementById("dibayarkan_input");
    let kembalianInput = document.getElementById("kembalian_input");
    let hitungBtn = document.getElementById("hitung-btn");

    let totalHidden = document.getElementById("total_hidden");
    let dibayarkanHidden = document.getElementById("dibayarkan_hidden");
    let kembalianHidden = document.getElementById("kembalian_hidden");

    // hitungBtn.addEventListener("click", function () {
    //     let total = totalSubtotalGlobal;
    //     let dibayarkan = parseInt(dibayarkanInput.value || 0);

    //     if (dibayarkan < total) {
    //         alert("Uang yang dibayarkan tidak boleh kurang dari total belanja!");
    //         kembalianInput.value = "Rp. 0";
    //         return;
    //     }

    //     let kembalian = dibayarkan - total;
    //     kembalianInput.value = "Rp. " + new Intl.NumberFormat("id-ID").format(kembalian);

    //     // isi hidden input
    //     totalHidden.value = total;
    //     dibayarkanHidden.value = dibayarkan;
    //     kembalianHidden.value = kembalian;
    // });
        hitungBtn.addEventListener("click", function () {
        let total = totalSubtotalGlobal;
        let dibayarkan = parseInt(dibayarkanInput.value || 0);

        if (dibayarkan < total) {
            alert("Uang yang dibayarkan tidak boleh kurang dari total belanja!");
            kembalianInput.value = "Rp. 0";
            return;
        }

        let kembalian = dibayarkan - total;

        // tampilkan ke user
        kembalianInput.value = "Rp. " + new Intl.NumberFormat("id-ID").format(kembalian);

        // set hidden input untuk dikirim
        totalHidden.value = total;
        dibayarkanHidden.value = dibayarkan;
        kembalianHidden.value = kembalian;
    });

    const formSelesai = document.getElementById('form-selesai');

    formSelesai.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Ambil token CSRF dari salah satu form yang ada
        const csrf = document.querySelector('input[name="_token"]').value;
        const transaksiId = "{{ Request::segment(3) }}";

        // Kumpulkan item dari hidden inputs yang sudah kamu isi di #produk-container
        const ids    = Array.from(document.querySelectorAll('#produk-container input[name="produk_id[]"]')).map(i => i.value);
        const names  = Array.from(document.querySelectorAll('#produk-container input[name="produk_name[]"]')).map(i => i.value);
        const qtys   = Array.from(document.querySelectorAll('#produk-container input[name="qty[]"]')).map(i => i.value);
        const subs   = Array.from(document.querySelectorAll('#produk-container input[name="subtotal[]"]')).map(i => i.value);

        // Validasi: harus ada item
        if (ids.length === 0) {
            alert('Belum ada item yang dipilih.');
            return;
        }

        // Pastikan total/dibayarkan/kembalian terisi walau user lupa tekan "Hitung"
        const total = totalSubtotalGlobal; // dari perhitungan real-time kamu
        const dibayarkan = parseInt(document.getElementById('dibayarkan_input').value || 0, 10);

        if (dibayarkan < total) {
            alert("Uang yang dibayarkan tidak boleh kurang dari total belanja!");
            return;
        }

        const kembalian = dibayarkan - total;
        document.getElementById('total_hidden').value = total;
        document.getElementById('dibayarkan_hidden').value = dibayarkan;
        document.getElementById('kembalian_hidden').value = kembalian;

        // 1) Simpan detail satu-per-satu memakai endpoint lama
        for (let i = 0; i < ids.length; i++) {
            const fd = new FormData();
            fd.append('_token', csrf);
            fd.append('transaksi_id', transaksiId);
            fd.append('produk_id', ids[i]);
            fd.append('produk_name', names[i]);
            fd.append('qty', qtys[i]);
            fd.append('subtotal', subs[i]);

            const resp = await fetch('/admin/transaksi/detail/create', { method: 'POST', body: fd });
            if (!resp.ok) {
                alert('Gagal menyimpan salah satu item. Silakan coba lagi.');
                return;
            }
        }

        // 2) Setelah semua detail tersimpan, submit form total (POST ke /transaksi/detail/selesai/{id})
        formSelesai.submit();
    });
});
</script>

