@extends('layouts.app')
@section('title', 'Manajemen Data Order')

@section('content')

<!-- ALERT MESSAGES -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show card-custom" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show card-custom" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show card-custom" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>Terdapat kesalahan pada input Anda:
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- FILTER & SEARCH PANEL ENTERPRISE -->
<div class="row g-3 mb-4">
    <div class="col-lg-9">
        <div class="card card-custom p-3 bg-white h-100">
            <form method="GET" action="{{ route('orders.index') }}" class="row g-2">
                <!-- Search Input Text -->
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari ID Order, No Agenda, Pemohon..." value="{{ $search }}">
                </div>
                <!-- Filter Tahun -->
                <div class="col-md-2">
                    <select name="year" class="form-select form-select-sm">
                        <option value="">-- Semua Tahun --</option>
                        @foreach($availableYears as $yr)
                            <option value="{{ $yr }}" {{ $year == $yr ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Filter Status -->
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">-- Status --</option>
                        @foreach($listStatus as $st)
                            <option value="{{ $st }}" {{ $status == $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Filter UP3 -->
                <div class="col-md-2">
                    <select name="up3" class="form-select form-select-sm">
                        <option value="">-- UP3 --</option>
                        @foreach($listUp3 as $up)
                            <option value="{{ $up }}" {{ $up3 == $up ? 'selected' : '' }}>{{ $up }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Buttons -->
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill" style="background-color: var(--pln-blue);"><i class="bi bi-search"></i> Cari</button>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Import Excel & Reset DB -->
    <div class="col-lg-3">
        <div class="card card-custom p-3 bg-white h-100 d-flex flex-column justify-content-between">
            <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-1 mb-2">
                @csrf
                <input type="file" name="file" class="form-control form-control-sm" required>
                <button type="submit" class="btn btn-success btn-sm px-2"><i class="bi bi-file-earmark-excel"></i> Import</button>
            </form>
            <button class="btn btn-outline-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#modalResetAll">
                <i class="bi bi-trash3-fill me-1"></i> Reset Seluruh Database
            </button>
        </div>
    </div>
</div>

<!-- TABEL MANAJEMEN DATA -->
<div class="card card-custom bg-white p-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0" style="color:var(--pln-blue)"><i class="bi bi-table me-2"></i>Daftar Order Layanan</h6>
        <small class="text-muted">Menampilkan {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} dari {{ number_format($orders->total()) }} Data</small>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size:0.88rem;">
            <thead class="table-light">
                <tr>
                    <th>ID Order</th>
                    <th>No Agenda</th>
                    <th>Pemohon</th>
                    <th>Status</th>
                    <th>Daya</th>
                    <th>UP3 / ULP</th>
                    <th>Pengajuan</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="fw-bold" style="color:var(--pln-blue);">{{ $order->id_order }}</td>
                        <td><small class="text-muted">{{ $order->no_agenda ?? '-' }}</small></td>
                        <td><b>{{ $order->pemohon }}</b></td>
                        <td>
                            @if(str_contains(strtolower($order->status), 'selesai'))
                                <span class="badge bg-success badge-status">{{ $order->status }}</span>
                            @elseif(str_contains(strtolower($order->status), 'batal'))
                                <span class="badge bg-danger badge-status">{{ $order->status }}</span>
                            @else
                                <span class="badge bg-warning text-dark badge-status">{{ $order->status ?? 'Draft' }}</span>
                            @endif
                        </td>
                        <td>{{ number_format($order->daya) }} VA</td>
                        <td>
                            <div class="lh-sm">
                                <strong class="d-block text-dark">{{ $order->up3 ?? '-' }}</strong>
                                <small class="text-muted">{{ $order->ulp ?? '-' }}</small>
                            </div>
                        </td>
                        <td><small>{{ $order->tanggal_pengajuan ?? '-' }}</small></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <!-- Tombol Edit (Triggers Modal) -->
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $order->id }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <!-- Tombol Delete -->
                                <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus ID Order {{ $order->id_order }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>

                            <!-- MODAL EDIT DATA -->
                            <div class="modal fade text-start" id="editModal{{ $order->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('orders.update', $order->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header bg-primary text-white">
                                                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Order: {{ $order->id_order }}</h6>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body row g-2">
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold">Nama Pemohon</label>
                                                    <input type="text" name="pemohon" class="form-control form-control-sm" value="{{ $order->pemohon }}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-bold">Status</label>
                                                    <input type="text" name="status" class="form-control form-control-sm" value="{{ $order->status }}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-bold">Daya (VA)</label>
                                                    <input type="number" name="daya" class="form-control form-control-sm" value="{{ $order->daya }}" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-bold">UP3</label>
                                                    <input type="text" name="up3" class="form-control form-control-sm" value="{{ $order->up3 }}">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-bold">ULP</label>
                                                    <input type="text" name="ulp" class="form-control form-control-sm" value="{{ $order->ulp }}">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">Data order tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="d-flex justify-content-end mt-3">
        {{ $orders->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- MODAL CONFIRMATION RESET ALL -->
<div class="modal fade" id="modalResetAll" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Konfirmasi Reset Database</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Tindakan ini akan <b>menghapus seluruh record data order</b> di database secara permanen. Apakah Anda yakin?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <form action="{{ route('orders.destroyAll') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Bersihkan Database</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection