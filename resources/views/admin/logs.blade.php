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
