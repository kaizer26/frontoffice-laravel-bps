@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-gauge-high text-primary me-2"></i>Dashboard Admin</h1>
        <div class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 0.65rem;">
            <i class="fas fa-circle-play fa-fade me-1"></i> LIVE UPDATES ACTIVE
        </div>
    </div>
    <div class="text-end ms-auto me-3">
        <div class="fw-bold">{{ now()->translatedFormat('l, d F Y') }}</div>
        <div class="text-muted small" id="realtimeClock">{{ now()->format('H:i:s') }} WITA</div>
    </div>
@endsection

@section('content')

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-4">
        <div class="stat-card h-100" style="border-left: 4px solid #3b82f6;">
            <div class="d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;background:#dbeafe;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-users fa-lg text-primary"></i>
                </div>
                <div>
                    <div class="stat-value" id="statVisitorsToday">0</div>
                    <div class="stat-label">Pengunjung Hari Ini</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="stat-card h-100" style="border-left: 4px solid #f59e0b;">
            <div class="d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;background:#fef3c7;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-file-alt fa-lg text-warning"></i>
                </div>
                <div>
                    <div class="stat-value" id="statServicesTotal">0</div>
                    <div class="stat-label">Total Permintaan</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="stat-card h-100" style="border-left: 4px solid #ef4444;">
            <div class="d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;background:#fee2e2;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-clock fa-lg text-danger"></i>
                </div>
                <div>
                    <div class="stat-value" id="statServicesPending">0</div>
                    <div class="stat-label">Belum Selesai</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card h-100" style="border-left: 4px solid #10b981;">
            <div class="d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;background:#d1fae5;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-user-tie fa-lg text-success"></i>
                </div>
                <div>
                    <div class="stat-value" id="petugasAktif">0</div>
                    <div class="stat-label">Petugas Aktif</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card h-100" style="border-left: 4px solid #fbbf24;">
            <div class="d-flex align-items-center gap-3">
                <div style="width:50px;height:50px;background:#fef3c7;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-star fa-lg text-warning"></i>
                </div>
                <div>
                    <div class="stat-value" id="ratingRataRata">0.0</div>
                    <div class="stat-label">Rating Rata-rata</div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-bolt"></i> Aksi Cepat</h5>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.jadwal') }}" class="btn btn-outline-primary">
                        <i class="fas fa-plus"></i> Tambah Jadwal Petugas
                    </a>
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-user-plus"></i> Kelola User
                    </a>
                    <a href="{{ route('admin.penilaian') }}" class="btn btn-outline-warning">
                        <i class="fas fa-star"></i> Lihat Penilaian
                    </a>
                    <a href="{{ route('admin.backup') }}" class="btn btn-outline-success">
                        <i class="fas fa-download"></i> Backup Database (SQLite)
                    </a>
                    <button onclick="syncRatings()" class="btn btn-primary mt-2">
                        <i class="fas fa-sync-alt"></i> Sinkronkan Penilaian Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 d-flex flex-column gap-4">
        <!-- Keamanan Login -->
        <div class="card bg-info-subtle border-0">
            <div class="card-body">
                <h5 class="card-title text-info-emphasis mb-1">
                    <i class="fas fa-shield-alt"></i> Keamanan Login
                </h5>
                <p class="text-muted small mb-3">
                    Tentukan apakah petugas wajib menggunakan password atau cukup email saja.
                </p>
                <div class="d-flex flex-column gap-3 mt-2">
                    <div class="form-check form-switch p-0 m-0 d-flex align-items-center gap-2" style="min-height: auto;">
                        <input class="form-check-input m-0" type="checkbox" role="switch" id="togglePasswordRequired" style="width: 1.8rem; height: 0.9rem;"
                            {{ \App\Models\SystemSetting::get('login_password_required', 'true') === 'true' ? 'checked' : '' }}
                            onchange="toggleAdminSetting('login_password_required', this.checked, 'Mode login')">
                        <label class="form-check-label fw-bold small mb-0" for="togglePasswordRequired" id="labelPasswordRequired">
                            @if(\App\Models\SystemSetting::get('login_password_required', 'true') === 'true')
                                <i class="fas fa-lock text-success"></i> Wajib Password (Safe)
                            @else
                                <i class="fas fa-unlock text-warning"></i> Cukup Email (Quick)
                            @endif
                        </label>
                    </div>
                    <div class="form-check form-switch p-0 m-0 d-flex align-items-center gap-2" style="min-height: auto;">
                        <input class="form-check-input m-0" type="checkbox" role="switch" id="toggleAllowPasswordChange" style="width: 1.8rem; height: 0.9rem;"
                            {{ \App\Models\SystemSetting::get('allow_user_password_change', 'false') === 'true' ? 'checked' : '' }}
                            onchange="toggleAdminSetting('allow_user_password_change', this.checked, 'Fitur ganti password')">
                        <label class="form-check-label fw-bold small mb-0" for="toggleAllowPasswordChange" id="labelAllowPasswordChange">
                            @if(\App\Models\SystemSetting::get('allow_user_password_change', 'false') === 'true')
                                <i class="fas fa-key text-primary"></i> Boleh Ganti Password (ON)
                            @else
                                <i class="fas fa-user-shield text-muted"></i> Ganti Password Dikunci (OFF)
                            @endif
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jam Pelayanan -->
        <div class="card bg-primary-subtle border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title text-primary-emphasis mb-0">
                        <i class="fas fa-clock"></i> Jam Pelayanan (Shift)
                    </h5>
                    <ul class="nav nav-pills nav-pills-custom" id="shiftTab" role="tablist" style="--bs-nav-pills-link-active-bg: var(--bs-primary); --bs-nav-link-padding-y: 0.2rem; --bs-nav-link-font-size: 0.75rem;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold" id="normal-tab" data-bs-toggle="pill" data-bs-target="#normal-shift" type="button" role="tab">Senin-Kamis</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="friday-tab" data-bs-toggle="pill" data-bs-target="#friday-shift" type="button" role="tab">Jumat</button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="shiftTabContent">
                    <!-- Tab Normal -->
                    <div class="tab-pane fade show active" id="normal-shift" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="form-check form-switch p-0 m-0 d-flex align-items-center" style="width: 2.2rem; min-height: auto;">
                                        <input class="form-check-input m-0" type="checkbox" role="switch" style="width: 1.8rem; height: 0.9rem;"
                                            {{ \App\Models\SystemSetting::get('shift_pagi_active', 'true') === 'true' ? 'checked' : '' }}
                                            onchange="toggleAdminSetting('shift_pagi_active', this.checked, 'Shift Pagi')">
                                    </div>
                                    <label class="small fw-bold text-muted mb-0">Pagi Mulai</label>
                                </div>
                                <input type="time" class="form-control form-control-sm" 
                                    value="{{ \App\Models\SystemSetting::get('shift_pagi_start', '07:30') }}"
                                    onchange="updateAdminSetting('shift_pagi_start', this.value, 'Jam Pagi Mulai')">
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-1">
                                    <div style="width: 2.2rem;"></div>
                                    <label class="small fw-bold text-muted mb-0">Pagi Selesai</label>
                                </div>
                                <input type="time" class="form-control form-control-sm" 
                                    value="{{ \App\Models\SystemSetting::get('shift_pagi_end', '12:00') }}"
                                    onchange="updateAdminSetting('shift_pagi_end', this.value, 'Jam Pagi Selesai')">
                            </div>
        
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="form-check form-switch p-0 m-0 d-flex align-items-center" style="width: 2.2rem; min-height: auto;">
                                        <input class="form-check-input m-0" type="checkbox" role="switch" style="width: 1.8rem; height: 0.9rem;"
                                            {{ \App\Models\SystemSetting::get('shift_siang_active', 'true') === 'true' ? 'checked' : '' }}
                                            onchange="toggleAdminSetting('shift_siang_active', this.checked, 'Shift Siang')">
                                    </div>
                                    <label class="small fw-bold text-muted mb-0">Siang Mulai</label>
                                </div>
                                <input type="time" class="form-control form-control-sm" 
                                    value="{{ \App\Models\SystemSetting::get('shift_siang_start', '12:00') }}"
                                    onchange="updateAdminSetting('shift_siang_start', this.value, 'Jam Siang Mulai')">
                            </div>
                            <div class="col-6 text-start">
                                <div class="d-flex align-items-center mb-1">
                                    <div style="width: 2.2rem;"></div>
                                    <label class="small fw-bold text-muted mb-0">Siang Selesai</label>
                                </div>
                                <input type="time" class="form-control form-control-sm" 
                                    value="{{ \App\Models\SystemSetting::get('shift_siang_end', '14:30') }}"
                                    onchange="updateAdminSetting('shift_siang_end', this.value, 'Jam Siang Selesai')">
                            </div>
        
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-1">
                                    <div class="form-check form-switch p-0 m-0 d-flex align-items-center" style="width: 2.2rem; min-height: auto;">
                                        <input class="form-check-input m-0" type="checkbox" role="switch" style="width: 1.8rem; height: 0.9rem;"
                                            {{ \App\Models\SystemSetting::get('shift_sore_active', 'true') === 'true' ? 'checked' : '' }}
                                            onchange="toggleAdminSetting('shift_sore_active', this.checked, 'Shift Sore')">
                                    </div>
                                    <label class="small fw-bold text-muted mb-0">Sore Mulai</label>
                                </div>
                                <input type="time" class="form-control form-control-sm" 
                                    value="{{ \App\Models\SystemSetting::get('shift_sore_start', '14:30') }}"
                                    onchange="updateAdminSetting('shift_sore_start', this.value, 'Jam Sore Mulai')">
                            </div>
                            <div class="col-6 text-start">
                                <div class="d-flex align-items-center mb-1">
                                    <div style="width: 2.2rem;"></div>
                                    <label class="small fw-bold text-muted mb-0">Sore Selesai</label>
                                </div>
                                <input type="time" class="form-control form-control-sm" 
                                    value="{{ \App\Models\SystemSetting::get('shift_sore_end', '16:00') }}"
                                    onchange="updateAdminSetting('shift_sore_end', this.value, 'Jam Sore Selesai')">
                            </div>
                        </div>
                    </div>

                    <!-- Tab Jumat -->
                    <div class="tab-pane fade" id="friday-shift" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-1">
                                    <div style="width: 2.2rem;"></div>
                                    <label class="small fw-bold text-muted mb-0">Pagi Mulai (Jumat)</label>
                                </div>
                                <input type="time" class="form-control form-control-sm" 
                                    value="{{ \App\Models\SystemSetting::get('shift_friday_pagi_start', '07:30') }}"
                                    onchange="updateAdminSetting('shift_friday_pagi_start', this.value, 'Jam Pagi Jumat Mulai')">
                            </div>
                            <div class="col-6 text-start">
                                <div class="d-flex align-items-center mb-1">
                                    <div style="width: 2.2rem;"></div>
                                    <label class="small fw-bold text-muted mb-0">Pagi Selesai (Jumat)</label>
                                </div>
                                <input type="time" class="form-control form-control-sm" 
                                    value="{{ \App\Models\SystemSetting::get('shift_friday_pagi_end', '11:30') }}"
                                    onchange="updateAdminSetting('shift_friday_pagi_end', this.value, 'Jam Pagi Jumat Selesai')">
                            </div>
        
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-1">
                                    <div style="width: 2.2rem;"></div>
                                    <label class="small fw-bold text-muted mb-0">Siang Mulai (Jumat)</label>
                                </div>
                                <input type="time" class="form-control form-control-sm" 
                                    value="{{ \App\Models\SystemSetting::get('shift_friday_siang_start', '13:30') }}"
                                    onchange="updateAdminSetting('shift_friday_siang_start', this.value, 'Jam Siang Jumat Mulai')">
                            </div>
                            <div class="col-6 text-start">
                                <div class="d-flex align-items-center mb-1">
                                    <div style="width: 2.2rem;"></div>
                                    <label class="small fw-bold text-muted mb-0">Siang Selesai (Jumat)</label>
                                </div>
                                <input type="time" class="form-control form-control-sm" 
                                    value="{{ \App\Models\SystemSetting::get('shift_friday_siang_end', '14:30') }}"
                                    onchange="updateAdminSetting('shift_friday_siang_end', this.value, 'Jam Siang Jumat Selesai')">
                            </div>
        
                            <div class="col-6">
                                <div class="d-flex align-items-center mb-1">
                                    <div style="width: 2.2rem;"></div>
                                    <label class="small fw-bold text-muted mb-0">Sore Mulai (Jumat)</label>
                                </div>
                                <input type="time" class="form-control form-control-sm" 
                                    value="{{ \App\Models\SystemSetting::get('shift_friday_sore_start', '14:30') }}"
                                    onchange="updateAdminSetting('shift_friday_sore_start', this.value, 'Jam Sore Jumat Mulai')">
                            </div>
                            <div class="col-6 text-start">
                                <div class="d-flex align-items-center mb-1">
                                    <div style="width: 2.2rem;"></div>
                                    <label class="small fw-bold text-muted mb-0">Sore Selesai (Jumat)</label>
                                </div>
                                <input type="time" class="form-control form-control-sm" 
                                    value="{{ \App\Models\SystemSetting::get('shift_friday_sore_end', '16:30') }}"
                                    onchange="updateAdminSetting('shift_friday_sore_end', this.value, 'Jam Sore Jumat Selesai')">
                            </div>
                        </div>
                        <div class="mt-3 small text-primary fw-bold" style="font-size: 0.7rem;">
                            <i class="fas fa-info-circle"></i> Status aktif/nonaktif shift mengikuti pengaturan Senin-Kamis.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title text-primary"><i class="fas fa-chart-line"></i> Tren Pengunjung (7 Hari Terakhir)</h5>
        <div style="height: 250px; width: 100%;">
            <canvas id="visitorTrendChart"></canvas>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title"><i class="fas fa-hand-wave"></i> Selamat Datang, {{ auth()->user()->name }}!</h5>
        <p class="text-muted mb-0">Gunakan menu di sidebar untuk mengelola sistem Front Office BPS.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
loadStats();

function loadStats() {
    fetch('/api/stats/admin')
        .then(res => res.json())
        .then(data => {
            document.getElementById('statVisitorsToday').textContent = data.visitors?.today || 0;
            document.getElementById('statServicesTotal').textContent = data.services?.total || 0;
            document.getElementById('statServicesPending').textContent = data.services?.pending || 0;
            document.getElementById('petugasAktif').textContent = data.officers?.active || 0;
            document.getElementById('ratingRataRata').textContent = (data.rating?.average || 0).toFixed(1);
            
            // Render Trend Chart
            if (data.visitors?.trends) {
                renderTrendChart(data.visitors.trends);
            }
        })
        .catch(err => console.error('Stats error:', err));
}

let trendChart = null;
function renderTrendChart(trends) {
    const ctx = document.getElementById('visitorTrendChart').getContext('2d');
    
    if (trendChart) {
        trendChart.destroy();
    }
    
    trendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: trends.map(t => t.date),
            datasets: [{
                label: 'Jumlah Pengunjung',
                data: trends.map(t => t.count),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: '#3b82f6',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(30, 64, 175, 0.9)',
                    titleFont: { weight: 'bold' },
                    padding: 10,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: 5,
                    ticks: { 
                        stepSize: 1, 
                        color: '#64748b' 
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    ticks: { color: '#64748b' },
                    grid: { display: false }
                }
            }
        }
    });
}

function toggleAdminSetting(key, isChecked, label) {
    const value = isChecked ? 'true' : 'false';
    const labelId = key === 'login_password_required' ? 'labelPasswordRequired' : 'labelAllowPasswordChange';
    const labelEl = document.getElementById(labelId);
    
    if (key === 'login_password_required') {
        labelEl.innerHTML = isChecked ? 
            '<i class="fas fa-lock text-success"></i> Wajib Password (Safe)' : 
            '<i class="fas fa-unlock text-warning"></i> Cukup Email (Quick)';
    } else {
        labelEl.innerHTML = isChecked ? 
            '<i class="fas fa-key text-primary"></i> User Boleh Ganti Password (ON)' : 
            '<i class="fas fa-user-shield text-muted"></i> Ganti Password Dikunci (OFF)';
    }

    fetch('{{ route("admin.settings.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ key, value })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(`${label} berhasil diperbarui`);
        } else {
            showToast(`Gagal memperbarui ${label.toLowerCase()}`, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Terjadi kesalahan', 'error');
    });
}

function updateAdminSetting(key, value, title) {
    fetch('{{ route("admin.settings.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ key, value })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(`${title} berhasil diperbarui`);
        } else {
            showToast(`Gagal memperbarui ${title}`, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Terjadi kesalahan', 'error');
    });
}

// Real-time Updates & Polling
function startRealtimeUpdates() {
    // 1. Clock Update
    setInterval(() => {
        const now = new Date();
        document.getElementById('realtimeClock').textContent = now.toLocaleTimeString('en-GB') + ' WITA';
    }, 1000);

    // 2. Refresh Stats every 30 seconds
    setInterval(loadStats, 30000);

    // 3. Auto-sync GAS Ratings every 2 minutes
    setInterval(syncRatings, 120000);
}

function syncRatings() {
    fetch('{{ route("admin.ratings.sync") }}')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.synced_count > 0) {
                showToast(`Sinkronisasi: ${data.synced_count} penilaian baru berhasil diimpor.`);
                loadStats();
            }
        })
        .catch(err => console.error('Sync error:', err));
}

// Start everything
startRealtimeUpdates();
</script>
@endpush
