@push('styles')
<style>
    @media (max-width: 991.98px) {
        .display-6 {
            font-size: 1.8rem;
        }
        h3.fw-bold {
            font-size: 1.25rem;
        }
        .card-body.p-4 {
            padding: 1.25rem !important;
        }
        .stat-icon-large {
            font-size: 3rem !important;
        }
    }
</style>
@endpush

@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-gauge-high text-primary me-2"></i>Dashboard Admin</h1>
        <div class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 0.65rem; padding-right: 8px;">
            <i class="fas fa-circle-play fa-fade me-1"></i> LIVE UPDATES ACTIVE
            <span id="nextRefreshCounter" class="ms-1 text-muted fw-normal">(30s)</span>
            <button onclick="manualRefresh()" class="btn btn-link btn-sm p-0 ms-2 text-success" style="font-size: 0.7rem; border: none; background: transparent;" title="Refresh Now">
                <i class="fas fa-rotate"></i>
            </button>
        </div>
    </div>
    <div class="text-end ms-auto me-3">
        <div class="fw-bold">{{ now()->translatedFormat('l, d F Y') }}</div>
        <div class="text-muted small" id="realtimeClock">{{ now()->format('H:i:s') }} WITA</div>
    </div>
@endsection

@section('content')

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <!-- Row 1: Triple Primary stats -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden" 
             style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border-radius: 16px;">
            <div class="card-body p-4 position-relative z-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6 class="text-white-50 mb-0 small text-uppercase fw-bold ls-1">Pengunjung Hari Ini</h6>
                    <i class="fas fa-users fa-lg opacity-50"></i>
                </div>
                <h2 class="display-6 fw-bold mb-0" id="statVisitorsToday">0</h2>
                <div class="mt-2 small opacity-75">
                    <i class="fas fa-arrow-up me-1"></i> Live data
                </div>
            </div>
            <div class="position-absolute bottom-0 end-0 opacity-10" style="font-size: 6rem; transform: translate(20%, 20%);">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden" 
             style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border-radius: 16px;">
            <div class="card-body p-4 position-relative z-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6 class="text-white-50 mb-0 small text-uppercase fw-bold ls-1">Total Permintaan</h6>
                    <i class="fas fa-file-alt fa-lg opacity-50"></i>
                </div>
                <h2 class="display-6 fw-bold mb-0" id="statServicesTotal">0</h2>
                <div class="mt-2 small opacity-75">
                    <i class="fas fa-database me-1"></i> Database
                </div>
            </div>
            <div class="position-absolute bottom-0 end-0 opacity-10" style="font-size: 6rem; transform: translate(20%, 20%);">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden" 
             style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border-radius: 16px;">
            <div class="card-body p-4 position-relative z-1">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h6 class="text-white-50 mb-0 small text-uppercase fw-bold ls-1">Belum Selesai</h6>
                    <i class="fas fa-clock fa-lg opacity-50"></i>
                </div>
                <h2 class="display-6 fw-bold mb-0" id="statServicesPending">0</h2>
                <div class="mt-2 small opacity-75 text-white fw-bold">
                    <i class="fas fa-exclamation-triangle me-1"></i> Perlu Tindakan
                </div>
            </div>
            <div class="position-absolute bottom-0 end-0 opacity-10" style="font-size: 6rem; transform: translate(20%, 20%);">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>

    <!-- Row 2: Secondary stats -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-white" style="border-radius: 16px;">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-pill p-3 bg-success-subtle text-success">
                    <i class="fas fa-user-tie fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small fw-bold">Petugas Aktif</h6>
                    <h3 class="fw-bold mb-0" id="petugasAktif">0</h3>
                </div>
                <a href="{{ route('admin.jadwal') }}" class="ms-auto btn btn-light rounded-circle shadow-sm" title="Lihat Jadwal">
                    <i class="fas fa-chevron-right text-primary"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100 bg-white" style="border-radius: 16px;">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-pill p-3 bg-warning-subtle text-warning">
                    <i class="fas fa-star fa-2x"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small fw-bold">IKM Rata-rata</h6>
                    <h3 class="fw-bold mb-0" id="ratingRataRata">0.0</h3>
                </div>
                <a href="{{ route('admin.penilaian') }}" class="ms-auto btn btn-light rounded-circle shadow-sm" title="Lihat Penilaian">
                    <i class="fas fa-chevron-right text-primary"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- Main Content Layout -->
<div class="row g-4 mb-4">
    <!-- Left Column: Trend & Welcome -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="fas fa-chart-line text-primary me-2"></i> Tren Pengunjung (7 Hari Terakhir)
                    </h5>
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm rounded-pill px-3 border" type="button">
                            Last 7 Days <i class="fas fa-chevron-down ms-1 small"></i>
                        </button>
                    </div>
                </div>
                <div style="height: 300px; width: 100%;">
                    <canvas id="visitorTrendChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm bg-primary text-white overflow-hidden" style="border-radius: 16px;">
            <div class="card-body p-4 position-relative z-1">
                <div class="row align-items-center">
                    <div class="col-sm-8 text-center text-sm-start">
                        <h4 class="fw-bold mb-2">Selamat Datang, {{ auth()->user()->name }}!</h4>
                        <p class="text-white-50 mb-3">Sistem Front Office BPS siap membantu Anda mengelola layanan hari ini. Periksa statistik terbaru di atas.</p>
                        <a href="{{ route('admin.logs') }}" class="btn btn-light btn-sm rounded-pill px-4 fw-bold text-primary">
                            Lihat Log Aktivitas
                        </a>
                    </div>
                    <div class="col-sm-4 d-none d-sm-block text-center mt-3 mt-sm-0">
                        <i class="fas fa-laptop-code display-1 opacity-25"></i>
                    </div>
                </div>
            </div>
            <!-- Decorative circle -->
            <div class="position-absolute translate-middle-y end-0 top-50 bg-white opacity-10 rounded-circle" style="width: 150px; height: 150px; margin-right: -75px;"></div>
        </div>
    </div>

    <!-- Right Column: Settings & Quick Actions -->
    <div class="col-lg-4">
        <!-- Attendance Summary Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold text-uppercase small text-muted mb-0 ls-1">Presensi Petugas (Hari Ini)</h6>
                    <button class="btn btn-link btn-sm p-0 text-decoration-none" onclick="loadAttendanceSummaryAdmin()"><i class="fas fa-sync-alt"></i></button>
                </div>
                <div id="attendanceSummaryList">
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <p class="small text-muted mt-2">Memuat data...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Card -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
            <div class="card-body p-4 text-center">
                <h6 class="fw-bold text-uppercase small text-muted mb-3 ls-1">Aksi Cepat</h6>
                <div class="row g-2">
                    <div class="col-12">
                        <a href="{{ route('admin.jadwal') }}" class="btn btn-primary w-100 rounded-pill py-2">
                            <i class="fas fa-calendar-plus me-2"></i> Tambah Jadwal
                        </a>
                    </div>
                    <div class="col-12 mt-2">
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary flex-grow-1 rounded-pill">
                                <i class="fas fa-users-cog"></i> Users
                            </a>
                            <a href="{{ route('admin.backup') }}" class="btn btn-outline-success flex-grow-1 rounded-pill">
                                <i class="fas fa-download"></i> Backup
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security & Integration Cards -->
        <div class="accordion accordion-flush dashboard-accordion shadow-sm mb-4" id="dashboardSettings" style="border-radius: 16px; overflow: hidden;">
            <!-- Login Security -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold text-primary bg-white px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSecurity">
                        <i class="fas fa-shield-halved me-2"></i> Keamanan Login
                    </button>
                </h2>
                <div id="collapseSecurity" class="accordion-collapse collapse" data-bs-parent="#dashboardSettings">
                    <div class="accordion-body bg-light-subtle px-4">
                        <div class="form-check form-switch d-flex justify-content-between align-items-center mb-3">
                            <label class="form-check-label small fw-bold" for="togglePasswordRequired" id="labelPasswordRequired">
                                {!! \App\Models\SystemSetting::get('login_password_required', 'true') === 'true' ? 
                                '<i class="fas fa-lock text-success"></i> Wajib Password (Safe)' : 
                                '<i class="fas fa-unlock text-warning"></i> Cukup Email (Quick)' !!}
                            </label>
                            <input class="form-check-input" type="checkbox" role="switch" id="togglePasswordRequired"
                                {{ \App\Models\SystemSetting::get('login_password_required', 'true') === 'true' ? 'checked' : '' }}
                                onchange="toggleAdminSetting('login_password_required', this.checked, 'Mode login')">
                        </div>
                        <div class="form-check form-switch d-flex justify-content-between align-items-center mb-0">
                            <label class="form-check-label small fw-bold" for="toggleAllowPasswordChange" id="labelAllowPasswordChange">
                                {!! \App\Models\SystemSetting::get('allow_user_password_change', 'false') === 'true' ? 
                                '<i class="fas fa-key text-primary"></i> User Boleh Ganti Password (ON)' : 
                                '<i class="fas fa-user-shield text-muted"></i> Ganti Password Dikunci (OFF)' !!}
                            </label>
                            <input class="form-check-input" type="checkbox" role="switch" id="toggleAllowPasswordChange"
                                {{ \App\Models\SystemSetting::get('allow_user_password_change', 'false') === 'true' ? 'checked' : '' }}
                                onchange="toggleAdminSetting('allow_user_password_change', this.checked, 'Mode ganti password')">
                        </div>
                    </div>
                </div>
            </div>
            <!-- WhatsApp Integration -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold text-success bg-white px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseWA">
                        <i class="fab fa-whatsapp me-2"></i> WhatsApp Group
                    </button>
                </h2>
                <div id="collapseWA" class="accordion-collapse collapse" data-bs-parent="#dashboardSettings">
                    <div class="accordion-body bg-light-subtle border-top px-4">
                        <label class="small text-muted mb-2">Link Grup Koordinasi</label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" class="form-control" 
                                value="{{ \App\Models\SystemSetting::get('whatsapp_group_link', 'https://chat.whatsapp.com/DPrCxwvtrX3DP6Gu84YOef') }}"
                                onchange="updateAdminSetting('whatsapp_group_link', this.value, 'Link Grup WhatsApp')">
                            <button class="btn btn-success" type="button" onclick="window.open(this.previousElementSibling.value, '_blank')">
                                <i class="fas fa-external-link-alt"></i>
                            </button>
                        </div>
                        <label class="small text-muted mb-2">Drive Kumpulan Data (Layanan Saya)</label>
                        <div class="input-group input-group-sm mb-0">
                            <input type="text" class="form-control" 
                                value="{{ \App\Models\SystemSetting::get('data_monitoring_drive_link', 'https://drive.google.com/drive/folders/...') }}"
                                onchange="updateAdminSetting('data_monitoring_drive_link', this.value, 'Link Drive Kumpulan Data')">
                            <button class="btn btn-primary" type="button" onclick="window.open(this.previousElementSibling.value, '_blank')">
                                <i class="fab fa-google-drive"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Reply Letter Configuration -->
            <div class="accordion-item border-0">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed fw-bold text-danger bg-white px-4 py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseReply">
                        <i class="fas fa-envelope-open-text me-2"></i> Surat Balasan
                    </button>
                </h2>
                <div id="collapseReply" class="accordion-collapse collapse" data-bs-parent="#dashboardSettings">
                    <div class="accordion-body bg-light-subtle border-top px-4">
                        <div class="mb-3">
                            <label class="small text-muted mb-2 font-monospace">Format Nomor Surat</label>
                            <input type="text" class="form-control form-control-sm" 
                                value="{{ \App\Models\SystemSetting::get('reply_letter_format', 'B-{nomor_urut}/63101/{kode_surat}/{tahun}') }}"
                                onchange="updateAdminSetting('reply_letter_format', this.value, 'Format Nomor Surat')">
                            <div class="form-text mt-1" style="font-size: 0.7rem;">
                                Variabel: <code>{nomor_urut}</code>, <code>{kode_surat}</code>, <code>{tahun}</code>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted mb-2">Kode Surat Default</label>
                            <input type="text" class="form-control form-control-sm" 
                                value="{{ \App\Models\SystemSetting::get('reply_letter_default_code', '02.04') }}"
                                onchange="updateAdminSetting('reply_letter_default_code', this.value, 'Kode Surat Default')">
                            <div class="form-text mt-1" style="font-size: 0.7rem;">Digunakan jika kode surat tidak diinput manual.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jam Pelayanan (Shift) Card -->
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-1">
                <h6 class="fw-bold mb-0 text-uppercase small text-muted ls-1">
                    <i class="fas fa-clock text-primary me-2"></i> Jam Pelayanan
                </h6>
            </div>
            <div class="card-body p-4">
                <nav>
                    <div class="nav nav-tabs nav-fill mb-3 border-0 bg-light p-1 rounded-pill" id="shiftTab" role="tablist">
                        <button class="nav-link active rounded-pill border-0 py-1 small fw-bold" id="normal-tab" data-bs-toggle="tab" data-bs-target="#normal-shift" type="button" role="tab">Senin-Kamis</button>
                        <button class="nav-link rounded-pill border-0 py-1 small fw-bold" id="friday-tab" data-bs-toggle="tab" data-bs-target="#friday-shift" type="button" role="tab">Jumat</button>
                    </div>
                </nav>
                <div class="tab-content" id="shiftTabContent">
                    <div class="tab-pane fade show active" id="normal-shift" role="tabpanel">
                        @foreach(['pagi', 'siang', 'sore'] as $s)
                        <div class="mb-3 p-2 bg-light rounded-3 border-start border-4 border-primary shadow-xs">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-white text-primary border shadow-xs fw-bold">{{ ucfirst($s) }}</span>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        {{ \App\Models\SystemSetting::get("shift_{$s}_active", 'true') === 'true' ? 'checked' : '' }}
                                        onchange="toggleAdminSetting('shift_{{ $s }}_active', this.checked, 'Shift {{ ucfirst($s) }}')">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label style="font-size: 0.65rem;" class="text-muted fw-bold mb-1 d-block text-uppercase">Mulai</label>
                                    <input type="time" class="form-control form-control-sm border-0 shadow-xs" 
                                        value="{{ \App\Models\SystemSetting::get("shift_{$s}_start", ($s == 'pagi' ? '07:30' : ($s == 'siang' ? '12:00' : '14:30'))) }}"
                                        onchange="updateAdminSetting('shift_{{ $s }}_start', this.value, 'Jam Mulai')">
                                </div>
                                <div class="col-6">
                                    <label style="font-size: 0.65rem;" class="text-muted fw-bold mb-1 d-block text-uppercase">Selesai</label>
                                    <input type="time" class="form-control form-control-sm border-0 shadow-xs" 
                                        value="{{ \App\Models\SystemSetting::get("shift_{$s}_end", ($s == 'pagi' ? '12:00' : ($s == 'siang' ? '14:30' : '16:00'))) }}"
                                        onchange="updateAdminSetting('shift_{{ $s }}_end', this.value, 'Jam Selesai')">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="tab-pane fade" id="friday-shift" role="tabpanel">
                        @foreach(['pagi', 'siang', 'sore'] as $s)
                        <div class="mb-3 p-2 bg-white border-bottom">
                            <h6 class="small fw-bold mb-2 text-primary">{{ ucfirst($s) }} (Jumat)</h6>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="time" class="form-control form-control-sm border-light bg-light" 
                                        value="{{ \App\Models\SystemSetting::get("shift_friday_{$s}_start", ($s == 'pagi' ? '07:30' : ($s == 'siang' ? '13:30' : '14:30'))) }}"
                                        onchange="updateAdminSetting('shift_friday_{{ $s }}_start', this.value, 'Jam Jumat Mulai')">
                                </div>
                                <div class="col-6">
                                    <input type="time" class="form-control form-control-sm border-light bg-light" 
                                        value="{{ \App\Models\SystemSetting::get("shift_friday_{$s}_end", ($s == 'pagi' ? '11:30' : ($s == 'siang' ? '14:30' : '16:30'))) }}"
                                        onchange="updateAdminSetting('shift_friday_{{ $s }}_end', this.value, 'Jam Jumat Selesai')">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
loadStats();
loadAttendanceSummaryAdmin();

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
    const labelId = key === 'login_password_required' ? 'labelPasswordRequired' : (key === 'allow_user_password_change' ? 'labelAllowPasswordChange' : null);
    const labelEl = labelId ? document.getElementById(labelId) : null;
    
    if (labelEl) {
        if (key === 'login_password_required') {
            labelEl.innerHTML = isChecked ? 
                '<i class="fas fa-lock text-success"></i> Wajib Password (Safe)' : 
                '<i class="fas fa-unlock text-warning"></i> Cukup Email (Quick)';
        } else if (key === 'allow_user_password_change') {
            labelEl.innerHTML = isChecked ? 
                '<i class="fas fa-key text-primary"></i> User Boleh Ganti Password (ON)' : 
                '<i class="fas fa-user-shield text-muted"></i> Ganti Password Dikunci (OFF)';
        }
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
let nextRefresh = 30;
function startRealtimeUpdates() {
    // 1. Clock & Counter Update
    setInterval(() => {
        const now = new Date();
        document.getElementById('realtimeClock').textContent = now.toLocaleTimeString('en-GB') + ' WITA';
        
        nextRefresh--;
        if (nextRefresh <= 0) {
            refreshDashboardData();
            nextRefresh = 30;
        }
        document.getElementById('nextRefreshCounter').textContent = `(${nextRefresh}s)`;
    }, 1000);

    // 3. Auto-sync GAS Ratings every 2 minutes
    syncRatings(); // Trigger immediately
    setInterval(syncRatings, 120000);
}

function refreshDashboardData() {
    loadStats();
    loadAttendanceSummaryAdmin();
}

function manualRefresh() {
    refreshDashboardData();
    nextRefresh = 30;
    document.getElementById('nextRefreshCounter').textContent = `(${nextRefresh}s)`;
    showToast('Data diperbarui (Manual)', 'info');
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

function loadAttendanceSummaryAdmin() {
    fetch('/api/absensi/today')
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;
            
            const container = document.getElementById('attendanceSummaryList');
            if (!container) return;

            if (data.logs.length === 0) {
                container.innerHTML = '<p class="small text-muted text-center py-3">Belum ada petugas yang absen hari ini.</p>';
                return;
            }
            
            let html = '<div class="d-flex flex-column gap-3">';
            data.logs.forEach(log => {
                const clockIn = new Date(log.jam_masuk).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
                const clockOut = log.jam_pulang ? new Date(log.jam_pulang).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'}) : '--:--';
                const statusBadge = log.status_masuk === 'Terlambat' ? 'bg-warning' : 'bg-success';
                
                html += `
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px;">
                             <i class="fas fa-user-tie text-primary"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <h6 class="mb-0 text-truncate fw-bold small">${log.user.name}</h6>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge ${statusBadge}" style="font-size: 0.6rem;">${log.status_masuk}</span>
                                <small class="text-muted" style="font-size: 0.7rem;"><i class="fas fa-clock"></i> ${clockIn} - ${clockOut}</small>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            container.innerHTML = html;
        });
}

// Start everything
startRealtimeUpdates();
</script>
@endpush
