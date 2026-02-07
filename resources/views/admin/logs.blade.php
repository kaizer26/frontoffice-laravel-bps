@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-history text-primary me-2"></i>Log Aktivitas</h1>
        <p class="text-muted small mb-0">Riwayat tindakan administratif dan operasional sistem.</p>
    </div>
    <span class="badge bg-light text-dark border shadow-sm px-3 py-2">
        <i class="fas fa-clock me-1 text-primary"></i> {{ now()->translatedFormat('d M Y, H:i') }} WITA
    </span>
</div>

<!-- Modern Filter Card -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
    <div class="card-body p-3">
        <form action="{{ route('admin.logs') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-lg-4 col-md-6">
                <label class="form-label small fw-bold text-muted mb-1">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-0 bg-light" 
                           placeholder="Cari aksi, user, atau keterangan..." 
                           value="{{ request('search') }}" style="border-radius: 0 10px 10px 0;">
                </div>
            </div>
            <div class="col-lg-3 col-md-3 col-6">
                <label class="form-label small fw-bold text-muted mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control border-0 bg-light" 
                       value="{{ request('start_date') }}" style="border-radius: 10px;">
            </div>
            <div class="col-lg-3 col-md-3 col-6">
                <label class="form-label small fw-bold text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control border-0 bg-light" 
                       value="{{ request('end_date') }}" style="border-radius: 10px;">
            </div>
            <div class="col-lg-2 col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1 shadow-sm" style="border-radius: 10px;">
                    <i class="fas fa-filter"></i><span class="d-none d-md-inline ms-1">Filter</span>
                </button>
                <a href="{{ route('admin.logs') }}" class="btn btn-outline-secondary" style="border-radius: 10px;" title="Reset">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Waktu</th>
                    <th>User</th>
                    <th>Aksi</th>
                    <th>Target</th>
                    <th>Keterangan</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="ps-4">
                        <div class="fw-bold">{{ $log->created_at->format('H:i:s') }}</div>
                        <div class="text-muted small">{{ $log->created_at->format('d/m/Y') }}</div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            @if($log->user && $log->user->foto)
                                <img src="{{ asset('storage/' . $log->user->foto) }}" class="rounded-circle me-2" style="width: 24px; height: 24px; object-fit: cover;">
                            @else
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 10px; color: #666;">
                                    {{ $log->user ? substr($log->user->name, 0, 1) : 'S' }}
                                </div>
                            @endif
                            <span class="small fw-bold">{{ $log->user->name ?? 'System' }}</span>
                        </div>
                    </td>
                    <td>
                        @php
                            $badgeClass = 'bg-secondary';
                            if(str_contains($log->action, 'CREATE')) $badgeClass = 'bg-success';
                            if(str_contains($log->action, 'UPDATE')) $badgeClass = 'bg-info';
                            if(str_contains($log->action, 'DELETE')) $badgeClass = 'bg-danger';
                            if(str_contains($log->action, 'IMPORT')) $badgeClass = 'bg-warning text-dark';
                        @endphp
                        <span class="badge {{ $badgeClass }}" style="font-size: 0.7rem;">{{ $log->action }}</span>
                    </td>
                    <td>
                        <span class="text-muted small">{{ $log->target_type }}</span>
                        @if($log->target_id)
                            <span class="badge bg-light text-dark border">ID: {{ $log->target_id }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="small text-wrap" style="max-width: 300px;">{{ $log->description }}</div>
                    </td>
                    <td>
                        <code class="small text-muted">{{ $log->ip_address }}</code>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-history fa-3x mb-3 opacity-25"></i>
                        <p>Belum ada log aktivitas yang tercatat.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="card-footer bg-white py-3">
        {{ $logs->links() }}
    </div>
    @endif
</div>
@endsection
