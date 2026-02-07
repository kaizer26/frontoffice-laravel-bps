@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <h1 class="h3 mb-0"><i class="fas fa-star"></i> Penilaian Petugas</h1>
    <div class="text-end ms-auto me-3">
        <div class="fw-bold small">{{ now()->translatedFormat('d M Y') }}</div>
        <div class="text-muted small" id="realtimeClock" style="font-size: 0.7rem;">{{ now()->format('H:i:s') }} WITA</div>
    </div>
@endsection

@section('content')
<!-- Leaderboard -->
<div class="card mb-4">
    <div class="card-body">
        <h5 class="card-title"><i class="fas fa-trophy text-warning"></i> Peringkat Petugas</h5>
        <div class="row">
            @foreach($officerRatings->take(5) as $rank => $officer)
            <div class="col-12 col-md-4 mb-3">
                <div class="stat-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rank-badge" style="width:40px;height:40px;background:#{{ $rank == 0 ? 'fbbf24' : ($rank == 1 ? 'cbd5e1' : ($rank == 2 ? 'f97316' : '64748b')) }};border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;color:white;">
                            {{ $rank + 1 }}
                        </div>
                        <div>
                            <strong>{{ $officer->user->name ?? 'Unknown' }}</strong>
                            <div class="text-warning">
                                <i class="fas fa-star"></i> {{ number_format($officer->avg_rating, 1) }}
                            </div>
                            <small class="text-muted">{{ $officer->total_ratings }} penilaian</small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chart-bar"></i> Perbandingan Rating Petugas</h5>
                <canvas id="ratingBarChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chart-pie"></i> Distribusi Rating</h5>
                <canvas id="ratingPieChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Penilaian -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Riwayat Penilaian</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th class="d-none d-md-table-cell">Petugas</th>
                    <th>Pengunjung</th>
                    <th>Rating</th>
                    <th class="d-none d-lg-table-cell">Komentar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ratings as $rating)
                <tr>
                    <td>{{ $rating->created_at->format('d/m/Y') }}</td>
                    <td class="d-none d-md-table-cell">{{ $rating->user->name ?? '-' }}</td>
                    <td>
                        {{ $rating->bukuTamu->nama_pengunjung ?? '-' }}
                        <div class="d-md-none small text-muted">vs {{ $rating->user->name ?? '-' }}</div>
                    </td>
                    <td>
                        <span class="text-warning">
                            @for($i = 0; $i < $rating->rating_keseluruhan; $i++)
                            <i class="fas fa-star" style="font-size: 0.8rem;"></i>
                            @endfor
                        </span>
                    </td>
                    <td class="d-none d-lg-table-cell">{{ $rating->komentar ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Belum ada penilaian</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-3">
            {{ $ratings->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data for charts
    const officerData = @json($officerChartData);
    const ratingDistribution = @json($distributionChartData);
    
    // Bar Chart - Officer Ratings Comparison
    if (officerData.length > 0) {
        new Chart(document.getElementById('ratingBarChart'), {
            type: 'bar',
            data: {
                labels: officerData.map(o => o.name.split(' ').slice(0, 2).join(' ')),
                datasets: [{
                    label: 'Rating Rata-rata',
                    data: officerData.map(o => o.rating),
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }
    
    // Pie Chart - Rating Distribution
    const totalRatings = Object.values(ratingDistribution).reduce((a, b) => a + b, 0);
    if (totalRatings > 0) {
        new Chart(document.getElementById('ratingPieChart'), {
            type: 'doughnut',
            data: {
                labels: ['⭐⭐⭐⭐⭐ (5)', '⭐⭐⭐⭐ (4)', '⭐⭐⭐ (3)', '⭐⭐ (2)', '⭐ (1)'],
                datasets: [{
                    data: [
                        ratingDistribution['5'],
                        ratingDistribution['4'],
                        ratingDistribution['3'],
                        ratingDistribution['2'],
                        ratingDistribution['1']
                    ],
                    backgroundColor: [
                        '#10b981',
                        '#3b82f6',
                        '#fbbf24',
                        '#f97316',
                        '#ef4444'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 8 }
                    }
                }
            }
        });
    }

    // Auto-sync GAS Ratings
    syncRatings(); // Initial sync
    setInterval(syncRatings, 120000); // Every 2 minutes
});

function syncRatings() {
    fetch('{{ route("admin.ratings.sync") }}')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.synced_count > 0) {
                // Show toast if Swal or custom toast exists (using Swal if available, or just refresh)
                if (typeof showToast === 'function') {
                    showToast(`Sinkronisasi: ${data.synced_count} penilaian baru berhasil diimpor.`);
                }
                
                // Refresh page after a short delay if new ratings were imported to show them in the table
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        })
        .catch(err => console.error('Sync error:', err));
}

</script>
@endpush
