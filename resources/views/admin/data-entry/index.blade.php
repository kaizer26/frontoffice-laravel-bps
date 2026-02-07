@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-database text-primary me-2"></i>{{ $registry->judul }}</h1>
        <p class="text-muted small mb-0">Kelola data per periode - {{ ucfirst($registry->periode_tipe) }}</p>
    </div>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <a href="{{ route('admin.data-registry.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Tabel
        </a>
        <a href="{{ route('admin.data-entry.create', $registry) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Periode Baru
        </a>
    </div>
</div>

<!-- Info Card -->
<div class="card mb-4 bg-light">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                @if($registry->deskripsi)
                    <p class="mb-2"><strong>Deskripsi:</strong> {{ $registry->deskripsi }}</p>
                @endif
                @if($registry->satuan)
                    <p class="mb-2"><strong>Satuan:</strong> {{ $registry->satuan }}</p>
                @endif
            </div>
            <div class="col-md-6">
                @if($registry->sumber_data)
                    <p class="mb-2"><strong>Sumber:</strong> {{ $registry->sumber_data }}</p>
                @endif
                <p class="mb-0"><strong>Total Periode:</strong> {{ $entries->total() }}</p>
            </div>
        </div>
    </div>
</div>

@if($entries->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
            <h5>Belum Ada Data Periode</h5>
            <p class="text-muted">Mulai dengan menambahkan data periode pertama.</p>
            <a href="{{ route('admin.data-entry.create', $registry) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Periode Pertama
            </a>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Terakhir Diupdate</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                            <tr>
                                <td>
                                    <strong>{{ $entry->periode }}</strong>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> {{ $entry->updated_at->diffForHumans() }}
                                    </small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.data-entry.show', [$registry, $entry]) }}" class="btn btn-info">
                                            <i class="fas fa-eye"></i> Lihat
                                        </a>
                                        <a href="{{ route('admin.data-entry.edit', [$registry, $entry]) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="{{ route('admin.data-entry.export', [$registry, $entry]) }}" class="btn btn-success" target="_blank">
                                            <i class="fas fa-download"></i> Export
                                        </a>
                                        <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $entry->id }}, '{{ $entry->periode }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $entries->links() }}
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
<style>
/* Fix pagination button sizing */
.pagination .page-link {
    font-size: 0.875rem !important;
    padding: 0.375rem 0.75rem !important;
    line-height: 1.5 !important;
}

.pagination .page-item {
    display: inline-block !important;
}
</style>

<script>
function confirmDelete(id, periode) {
    Swal.fire({
        title: 'Hapus Data Periode?',
        html: `Data periode <strong>"${periode}"</strong> akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/data-registry/{{ $registry->id }}/entries/${id}`;
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
