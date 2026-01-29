@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <div class="d-flex align-items-center gap-3">
        <h1 class="h3 mb-0"><i class="fas fa-users"></i> User Management</h1>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus"></i> Tambah
        </button>
    </div>
    <div class="text-end ms-auto me-3">
        <div class="fw-bold small">{{ now()->translatedFormat('d M Y') }}</div>
        <div class="text-muted small" id="realtimeClock" style="font-size: 0.7rem;">{{ now()->format('H:i:s') }} WITA</div>
    </div>
@endsection

@section('content')

<!-- Tabel Users -->
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="card-title mb-0">Daftar User</h5>
            <div class="d-flex align-items-center gap-2">
                <label class="text-muted small">Show:</label>
                <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href='{{ route('admin.users') }}?per_page=' + this.value">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nama & NIP</th>
                        <th>Email</th>
                        <th>No. HP</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="me-2">
                                    @if($user->foto)
                                        <img src="{{ asset('storage/' . $user->foto) }}" class="rounded-circle border border-2 border-white shadow-sm" style="width: 38px; height: 38px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border border-2 border-white shadow-sm" style="width: 38px; height: 38px; font-size: 14px; color: #666; font-weight: bold;">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="fw-bold text-dark">{{ $user->name }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">NIP: {{ $user->nip_bps ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td><small>{{ $user->no_hp ?? '-' }}</small></td>
                        <td>
                            <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : 'primary' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $user->status == 'aktif' ? 'success' : 'secondary' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" onclick="editUser({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $user->nip_bps ?? '' }}', '{{ $user->nip_pns ?? '' }}', '{{ $user->no_hp ?? '' }}', '{{ $user->role }}', '{{ $user->status }}')" title="Edit User">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($user->id !== auth()->id())
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')" title="Hapus User">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Tidak ada user</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="email" placeholder="username" required>
                            <span class="input-group-text">@bps.go.id</span>
                        </div>
                        <small class="text-muted">Masukkan username tanpa @bps.go.id</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP BPS (9 digit)</label>
                            <input type="text" class="form-control" name="nip_bps" maxlength="9">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP PNS (18 digit)</label>
                            <input type="text" class="form-control" name="nip_pns" maxlength="18">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP (628...)</label>
                        <input type="text" class="form-control" name="no_hp" placeholder="628xxxxxxxxxx" oninput="formatPhoneNumber(this)">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-control" name="role" required>
                                <option value="petugas">Petugas</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="password" required minlength="6">
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-edit"></i> Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editUserForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="editName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="text" class="form-control bg-light" id="editEmail" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP BPS</label>
                            <input type="text" class="form-control" name="nip_bps" id="editNipBps" maxlength="9">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP PNS</label>
                            <input type="text" class="form-control" name="nip_pns" id="editNipPns" maxlength="18">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP (628...)</label>
                        <input type="text" class="form-control" name="no_hp" id="editNoHp" placeholder="628xxxxxxxxxx" oninput="formatPhoneNumber(this)">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-control" name="role" id="editRole" required>
                                <option value="petugas">Petugas</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" id="editStatus" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" class="form-control" name="password" minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modern Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle rounded-circle" style="width: 80px; height: 80px;">
                        <i class="fas fa-user-times text-danger fa-2x"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2">Hapus User?</h5>
                <p class="text-muted small mb-4">Anda akan menghapus user <strong id="deleteUserName" class="text-dark"></strong>. Tindakan ini tidak dapat dibatalkan.</p>
                <div class="d-grid gap-2">
                    <form id="deleteUserForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100 fw-bold">Ya, Hapus Permanen</button>
                    </form>
                    <button type="button" class="btn btn-light btn-sm text-muted" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editUser(id, name, email, nipBps, nipPns, noHp, role, status) {
    document.getElementById('editUserForm').action = '/admin/users/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editEmail').value = email;
    document.getElementById('editNipBps').value = nipBps || '';
    document.getElementById('editNipPns').value = nipPns || '';
    document.getElementById('editNoHp').value = noHp || '';
    document.getElementById('editRole').value = role;
    document.getElementById('editStatus').value = status;
    
    var modal = new bootstrap.Modal(document.getElementById('editUserModal'));
    modal.show();
}

function confirmDeleteUser(id, name) {
    document.getElementById('deleteUserForm').action = '/admin/users/' + id;
    document.getElementById('deleteUserName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
}
</script>
@endpush
