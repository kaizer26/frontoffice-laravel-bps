@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <h1 class="h3 mb-0"><i class="fas fa-calendar"></i> Jadwal Petugas</h1>
    <div class="text-end ms-auto me-3">
        <div class="fw-bold small">{{ now()->translatedFormat('d M Y') }}</div>
        <div class="text-muted small" id="realtimeClock" style="font-size: 0.7rem;">{{ now()->format('H:i:s') }} WITA</div>
    </div>
@endsection

@section('content')

<!-- Bulk Actions -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title text-primary"><i class="fas fa-file-excel"></i> Bulk Actions (Excel)</h5>
                <div class="d-flex gap-2 align-items-center flex-wrap mt-2">
                    <a href="{{ route('admin.jadwal.template') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-download"></i> Download Template Excel
                    </a>
                    <div class="ms-1">
                        <form action="{{ route('admin.jadwal.import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                            @csrf
                            <div style="max-width: 200px;">
                                <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="fas fa-upload"></i> Import Excel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Tambah Jadwal -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title">Tambah Jadwal</h5>
        <form action="{{ route('admin.jadwal.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" class="form-control" name="tanggal" id="input_tanggal" required onchange="updateShiftLabels('add')">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Shift</label>
                    <select class="form-control" name="shift" id="input_shift" required>
                        @if(\App\Models\SystemSetting::get('shift_pagi_active', 'true') === 'true')
                        <option value="Pagi" 
                            data-normal="{{ \App\Models\SystemSetting::get('shift_pagi_start', '07:30') }} - {{ \App\Models\SystemSetting::get('shift_pagi_end', '12:00') }}"
                            data-friday="{{ \App\Models\SystemSetting::get('shift_friday_pagi_start', '07:30') }} - {{ \App\Models\SystemSetting::get('shift_friday_pagi_end', '11:30') }}">
                            Pagi ({{ \App\Models\SystemSetting::get('shift_pagi_start', '07:30') }} - {{ \App\Models\SystemSetting::get('shift_pagi_end', '12:00') }})
                        </option>
                        @endif
                        @if(\App\Models\SystemSetting::get('shift_siang_active', 'true') === 'true')
                        <option value="Siang"
                            data-normal="{{ \App\Models\SystemSetting::get('shift_siang_start', '12:00') }} - {{ \App\Models\SystemSetting::get('shift_siang_end', '14:30') }}"
                            data-friday="{{ \App\Models\SystemSetting::get('shift_friday_siang_start', '13:30') }} - {{ \App\Models\SystemSetting::get('shift_friday_siang_end', '14:30') }}">
                            Siang ({{ \App\Models\SystemSetting::get('shift_siang_start', '12:00') }} - {{ \App\Models\SystemSetting::get('shift_siang_end', '14:30') }})
                        </option>
                        @endif
                        @if(\App\Models\SystemSetting::get('shift_sore_active', 'true') === 'true')
                        <option value="Sore"
                            data-normal="{{ \App\Models\SystemSetting::get('shift_sore_start', '14:30') }} - {{ \App\Models\SystemSetting::get('shift_sore_end', '16:00') }}"
                            data-friday="{{ \App\Models\SystemSetting::get('shift_friday_sore_start', '14:30') }} - {{ \App\Models\SystemSetting::get('shift_friday_sore_end', '16:30') }}">
                            Sore ({{ \App\Models\SystemSetting::get('shift_sore_start', '14:30') }} - {{ \App\Models\SystemSetting::get('shift_sore_end', '16:00') }})
                        </option>
                        @endif
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Petugas</label>
                    <select class="form-control" name="user_id" required>
                        @foreach($petugas as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-control" name="status" required>
                        <option value="aktif">Aktif</option>
                        <option value="libur">Libur</option>
                        <option value="cuti">Cuti</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Tambah Jadwal
            </button>
        </form>
    </div>
</div>

<!-- Tabel Jadwal -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Daftar Jadwal</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Shift</th>
                    <th>Petugas</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwal as $j)
                <tr>
                    <td>{{ $j->tanggal->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-clock text-primary"></i> {{ $j->shift }}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            @if($j->user->foto)
                                <img src="{{ asset('storage/' . $j->user->foto) }}" class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                    <i class="fas fa-user text-muted" style="font-size: 0.8rem;"></i>
                                </div>
                            @endif
                            {{ $j->user->name }}
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-{{ $j->status == 'aktif' ? 'success' : ($j->status == 'cuti' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($j->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="btn-group">
                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                onclick="editJadwal('{{ $j->id }}', '{{ $j->tanggal->format('Y-m-d') }}', '{{ $j->shift }}', '{{ $j->user_id }}', '{{ $j->status }}')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                onclick="confirmDeleteJadwal('{{ $j->id }}', '{{ $j->tanggal->format('d/m/Y') }} - {{ $j->shift }} ({{ $j->user->name }})')" title="Hapus Jadwal">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">Tidak ada jadwal ditemukan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">
            {{ $jadwal->links() }}
        </div>
    </div>
</div>

<!-- Edit Jadwal Modal -->
<div class="modal fade" id="editJadwalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Jadwal Petugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editJadwalForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal</label>
                        <input type="date" class="form-control" name="tanggal" id="edit_tanggal" required onchange="updateShiftLabels('edit')">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Shift</label>
                        <select class="form-control" name="shift" id="edit_shift" required>
                            @if(\App\Models\SystemSetting::get('shift_pagi_active', 'true') === 'true')
                            <option value="Pagi"
                                data-normal="{{ \App\Models\SystemSetting::get('shift_pagi_start', '07:30') }} - {{ \App\Models\SystemSetting::get('shift_pagi_end', '12:00') }}"
                                data-friday="{{ \App\Models\SystemSetting::get('shift_friday_pagi_start', '07:30') }} - {{ \App\Models\SystemSetting::get('shift_friday_pagi_end', '11:30') }}">
                                Pagi ({{ \App\Models\SystemSetting::get('shift_pagi_start', '07:30') }} - {{ \App\Models\SystemSetting::get('shift_pagi_end', '12:00') }})
                            </option>
                            @endif
                            @if(\App\Models\SystemSetting::get('shift_siang_active', 'true') === 'true')
                            <option value="Siang"
                                data-normal="{{ \App\Models\SystemSetting::get('shift_siang_start', '12:00') }} - {{ \App\Models\SystemSetting::get('shift_siang_end', '14:30') }}"
                                data-friday="{{ \App\Models\SystemSetting::get('shift_friday_siang_start', '13:30') }} - {{ \App\Models\SystemSetting::get('shift_friday_siang_end', '14:30') }}">
                                Siang ({{ \App\Models\SystemSetting::get('shift_siang_start', '12:00') }} - {{ \App\Models\SystemSetting::get('shift_siang_end', '14:30') }})
                            </option>
                            @endif
                            @if(\App\Models\SystemSetting::get('shift_sore_active', 'true') === 'true')
                            <option value="Sore"
                                data-normal="{{ \App\Models\SystemSetting::get('shift_sore_start', '14:30') }} - {{ \App\Models\SystemSetting::get('shift_sore_end', '16:00') }}"
                                data-friday="{{ \App\Models\SystemSetting::get('shift_friday_sore_start', '14:30') }} - {{ \App\Models\SystemSetting::get('shift_friday_sore_end', '16:30') }}">
                                Sore ({{ \App\Models\SystemSetting::get('shift_sore_start', '14:30') }} - {{ \App\Models\SystemSetting::get('shift_sore_end', '16:00') }})
                            </option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Petugas</label>
                        <select class="form-control" name="user_id" id="edit_user_id" required>
                            @foreach($petugas as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status" id="edit_status" required>
                            <option value="aktif">Aktif</option>
                            <option value="libur">Libur</option>
                            <option value="cuti">Cuti</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modern Delete Confirmation Modal -->
<div class="modal fade" id="deleteJadwalModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle rounded-circle" style="width: 80px; height: 80px;">
                        <i class="fas fa-calendar-times text-danger fa-2x"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2">Hapus Jadwal?</h5>
                <p class="text-muted small mb-4">Anda akan menghapus jadwal <strong id="deleteJadwalInfo" class="text-dark"></strong>. Tindakan ini tidak dapat dibatalkan.</p>
                <div class="d-grid gap-2">
                    <form id="deleteJadwalForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 fw-bold">Ya, Hapus Jadwal</button>
                    </form>
                    <button type="button" class="btn btn-light btn-sm text-muted" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateShiftLabels(type = 'add') {
    const dateInput = document.getElementById(type === 'add' ? 'input_tanggal' : 'edit_tanggal');
    const shiftSelect = document.getElementById(type === 'add' ? 'input_shift' : 'edit_shift');
    
    if (!dateInput.value) return;
    
    const date = new Date(dateInput.value);
    const isFriday = date.getDay() === 5; // 0=Sun, 5=Fri
    
    Array.from(shiftSelect.options).forEach(option => {
        const hours = isFriday ? option.dataset.friday : option.dataset.normal;
        if (hours) {
            option.textContent = `${option.value} (${hours})`;
        }
    });
}

function editJadwal(id, tanggal, shift, userId, status) {
    const form = document.getElementById('editJadwalForm');
    form.action = `/admin/jadwal/${id}`;
    
    document.getElementById('edit_tanggal').value = tanggal;
    document.getElementById('edit_shift').value = shift;
    document.getElementById('edit_user_id').value = userId;
    document.getElementById('edit_status').value = status;
    
    // Update labels for Friday check
    updateShiftLabels('edit');
    
    new bootstrap.Modal(document.getElementById('editJadwalModal')).show();
}

function confirmDeleteJadwal(id, info) {
    const form = document.getElementById('deleteJadwalForm');
    form.action = `/admin/jadwal/${id}`;
    document.getElementById('deleteJadwalInfo').textContent = info;
    new bootstrap.Modal(document.getElementById('deleteJadwalModal')).show();
}
</script>
@endpush
@endsection
