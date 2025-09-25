<div class="row p-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">

                <h5><b>{{ $title }}</b></h5>

                <a href="/admin/transaksi/create" class="btn btn-primary mb-2">
                    <i class="fas fa-plus"></i> Tambah
                </a>

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
                            <td>{{ $item->kode_transaksi }}</td>
                            <td>{{ $item->total }}</td>
                            <td>{{ $item->status}}</td>
                            <td>{{ $item->created_at->format('d-m-Y H:i') }}</td>
                            <td>
                                <div class="d-flex">
                                    <a href="/admin/transaksi/{{ $item->id }}/edit" class="btn btn-info btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="/admin/transaksi/{{ $item->id }}" method="POST">
                                        @method('delete')
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm ml-1">
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
