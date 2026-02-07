@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-table text-primary me-2"></i>Daftar Data</h1>
        <p class="text-muted small mb-0">Kelola tabel data dinamis dengan template Excel</p>
    </div>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <a href="{{ route('admin.data-registry.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Tabel Baru
        </a>
    </div>
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" class="form-control" id="searchInput" placeholder="Cari tabel data...">
        </div>
    </div>
</div>

@if($registries->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-table fa-3x text-muted mb-3"></i>
            <h5>Belum Ada Tabel Data</h5>
            <p class="text-muted">Mulai dengan membuat tabel data pertama Anda.</p>
            <a href="{{ route('admin.data-registry.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Buat Tabel Pertama
            </a>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="dataTable">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="30%">Judul Tabel</th>
                            <th width="15%">Tipe Periode</th>
                            <th width="10%">Layout</th>
                            <th width="10%">Format</th>
                            <th width="10%" class="text-center">Periode</th>
                            <th width="20%" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registries as $index => $registry)
                            <tr class="searchable-row">
                                <td>{{ $registries->firstItem() + $index }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $registry->judul }}</strong>
                                        @if($registry->deskripsi)
                                            <br><small class="text-muted">{{ \Str::limit($registry->deskripsi, 60) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ ucfirst($registry->periode_tipe) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <i class="fas fa-{{ $registry->layout_type == 'vertical' ? 'arrows-alt-v' : 'arrows-alt-h' }}"></i>
                                        {{ ucfirst($registry->layout_type) }}
                                    </small>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $registry->numeric_format == 'id' ? 'ID' : 'EN' }} 
                                        ({{ $registry->decimal_places ?? 2 }})
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info">{{ $registry->entries_count }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.data-registry.viewer', $registry) }}" 
                                           class="btn btn-info" title="Lihat Data">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.data-entry.create', $registry) }}" 
                                           class="btn btn-success" title="Tambah Data">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                        <a href="{{ route('admin.data-registry.edit', $registry) }}" 
                                           class="btn btn-warning" title="Edit Template">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger" 
                                                onclick="confirmDelete({{ $registry->id }}, '{{ $registry->judul }}')" 
                                                title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $registries->links() }}
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
// Search functionality
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('.searchable-row');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});

function confirmDelete(id, judul) {
    Swal.fire({
        title: 'Hapus Tabel Data?',
        html: `Tabel <strong>"${judul}"</strong> dan semua data periodenya akan dihapus permanen.`,
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
            form.action = `/admin/data-registry/${id}`;
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
