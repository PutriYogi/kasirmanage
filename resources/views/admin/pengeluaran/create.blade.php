<div class="row p-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus"></i> {{ $title }}</h3        });
    });
}
</script>          </div>
            <div class="card-body">
                <form action="{{ route('admin.pengeluaran.store') }}" method="POST" id="pengeluaranForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="jenis_pengeluaran"><i class="fas fa-tag"></i> Jenis Pengeluaran <span class="text-danger">*</span></label>
                                <select name="jenis_pengeluaran" id="jenis_pengeluaran" class="form-control @error('jenis_pengeluaran') is-invalid @enderror" required>
                                    <option value="">Pilih Jenis Pengeluaran</option>
                                    @foreach($jenisOptions as $jenis)
                                        <option value="{{ $jenis }}" {{ old('jenis_pengeluaran') == $jenis ? 'selected' : '' }}>
                                            {{ $jenis }}
                                        </option>
                                    @endforeach
                                    <option value="custom" {{ old('jenis_pengeluaran') == 'custom' ? 'selected' : '' }}>
                                        Lainnya (Custom)
                                    </option>
                                </select>
                                @error('jenis_pengeluaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group" id="customJenisContainer" style="display: none;">
                                <label for="custom_jenis"><i class="fas fa-edit"></i> Jenis Pengeluaran Custom</label>
                                <input type="text" name="custom_jenis" id="custom_jenis" class="form-control" placeholder="Masukkan jenis pengeluaran custom">
                            </div>

                            <div class="form-group">
                                <label for="nominal"><i class="fas fa-money-bill-wave"></i> Nominal <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" name="nominal" id="nominal" class="form-control @error('nominal') is-invalid @enderror" 
                                           value="{{ old('nominal') }}" placeholder="0" min="0" step="0.01" required>
                                </div>
                                @error('nominal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="keterangan"><i class="fas fa-sticky-note"></i> Keterangan</label>
                                <textarea name="keterangan" id="keterangan" rows="6" class="form-control @error('keterangan') is-invalid @enderror" 
                                          placeholder="Keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.pengeluaran.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                                <button type="button" class="btn btn-primary" onclick="submitPengeluaranForm(event)">
                                    <i class="fas fa-save"></i> Simpan Pengeluaran
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle jenis pengeluaran selection
    const jenisSelect = document.getElementById('jenis_pengeluaran');
    const customContainer = document.getElementById('customJenisContainer');
    const customInput = document.getElementById('custom_jenis');

    jenisSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customContainer.style.display = 'block';
            customInput.required = true;
        } else {
            customContainer.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    });

    // Trigger change event if custom is already selected (for old input)
    if (jenisSelect.value === 'custom') {
        jenisSelect.dispatchEvent(new Event('change'));
    }

    // Format nominal input
    const nominalInput = document.getElementById('nominal');
    nominalInput.addEventListener('input', function() {
        // Remove any non-numeric characters except decimal point
        this.value = this.value.replace(/[^0-9.]/g, '');
    });

    // Prevent form from submitting normally
    const form = document.getElementById('pengeluaranForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    });
});

function submitPengeluaranForm(event) {
    // Prevent any default form submission
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const jenisSelect = document.getElementById('jenis_pengeluaran');
    const customInput = document.getElementById('custom_jenis');
    const nominalInput = document.getElementById('nominal');

    // Validate jenis pengeluaran
    if (!jenisSelect.value) {
        Swal.fire({
            icon: 'warning',
            title: 'Data Tidak Lengkap',
            text: 'Mohon pilih jenis pengeluaran',
            confirmButtonText: 'OK'
        });
        jenisSelect.focus();
        return false;
    }

    // Validate custom jenis if selected
    if (jenisSelect.value === 'custom' && !customInput.value.trim()) {
        Swal.fire({
            icon: 'warning',
            title: 'Data Tidak Lengkap',
            text: 'Mohon isi jenis pengeluaran custom',
            confirmButtonText: 'OK'
        });
        customInput.focus();
        return false;
    }

    // Validate nominal
    if (!nominalInput.value || parseFloat(nominalInput.value) <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Data Tidak Valid',
            text: 'Nominal harus diisi dan lebih besar dari 0',
            confirmButtonText: 'OK'
        });
        nominalInput.focus();
        return false;
    }

    // If custom jenis, set it as the jenis_pengeluaran value
    if (jenisSelect.value === 'custom') {
        jenisSelect.value = customInput.value.trim();
    }

    // Show confirmation
    Swal.fire({
        title: 'Konfirmasi Simpan',
        html: `
            <div class="text-left">
                <p><strong>Jenis:</strong> ${jenisSelect.value === 'custom' ? customInput.value : jenisSelect.value}</p>
                <p><strong>Nominal:</strong> Rp ${new Intl.NumberFormat('id-ID').format(nominalInput.value)}</p>
                <p class="text-info"><small><i class="fas fa-info-circle"></i> Tanggal akan dicatat otomatis saat penyimpanan</small></p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-save"></i> Ya, Simpan!',
        cancelButtonText: '<i class="fas fa-times"></i> Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Menyimpan...',
                text: 'Sedang memproses data pengeluaran',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit form
            document.getElementById('pengeluaranForm').submit();
        }
    });
}
</script>