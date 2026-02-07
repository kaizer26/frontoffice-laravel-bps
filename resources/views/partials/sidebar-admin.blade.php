<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-building-columns"></i> Front Office</h3>
        <div class="d-flex align-items-center gap-2 mt-2">
            @if(auth()->user()->foto)
                <img src="{{ asset('storage/' . auth()->user()->foto) }}" class="rounded-circle border border-2 border-white shadow-sm" style="width: 32px; height: 32px; object-fit: cover;">
            @else
                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; color: var(--primary); font-weight: bold; font-size: 0.8rem;">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
            @endif
            <div class="overflow-hidden">
                <div class="text-truncate fw-bold" style="font-size: 0.85rem;">{{ auth()->user()->name }}</div>
                <span class="badge bg-danger" style="font-size:0.6rem; vertical-align: middle;">Admin</span>
            </div>
        </div>
    </div>
    
    <nav>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a href="{{ route('admin.jadwal') }}" class="nav-item {{ request()->routeIs('admin.jadwal') ? 'active' : '' }}">
            <i class="fas fa-calendar"></i>
            <span>Jadwal Petugas</span>
        </a>
        <a href="{{ route('admin.penilaian') }}" class="nav-item {{ request()->routeIs('admin.penilaian') ? 'active' : '' }}">
            <i class="fas fa-star"></i>
            <span>Penilaian</span>
        </a>
        <a href="{{ route('admin.rekap') }}" class="nav-item {{ request()->routeIs('admin.rekap') ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i>
            <span>Rekap Layanan</span>
        </a>
        <a href="{{ route('admin.data-registry.index') }}" class="nav-item {{ request()->routeIs('admin.data-registry.*') || request()->routeIs('admin.data-entry.*') ? 'active' : '' }}">
            <i class="fas fa-table"></i>
            <span>Daftar Data</span>
        </a>
        <a href="{{ route('admin.data-summary.index') }}" class="nav-item {{ request()->routeIs('admin.data-summary.*') ? 'active' : '' }}">
            <i class="fas fa-layer-group"></i>
            <span>Rekap Data</span>
        </a>
        <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <span>User Management</span>
        </a>
        <a href="{{ route('admin.logs') }}" class="nav-item {{ request()->routeIs('admin.logs') ? 'active' : '' }}">
            <i class="fas fa-history"></i>
            <span>Log Aktivitas</span>
        </a>
        <a href="{{ \App\Models\SystemSetting::get('whatsapp_group_link', 'https://chat.whatsapp.com/DPrCxwvtrX3DP6Gu84YOef') }}" target="_blank" class="nav-item">
            <i class="fab fa-whatsapp"></i>
            <span>Grup Koordinasi WA</span>
        </a>
    </nav>
    
    <div style="position: absolute; bottom: 20px; width: 100%; padding: 0 20px;">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-light w-100 btn-sm fw-bold">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </div>
</div>
