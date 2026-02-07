@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-chart-bar"></i> Rekap Layanan Triwulanan</h1>
    </div>
    <div class="text-end ms-auto me-3">
        <div class="fw-bold small">{{ now()->translatedFormat('l, d F Y') }}</div>
    </div>
@endsection

@section('content')

<div class="card mb-4 shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body">
        <form action="{{ route('admin.rekap') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold">Pilih Tahun</label>
                <select name="year" class="form-select border-0 bg-light" style="border-radius: 10px;">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Pilih Triwulan</label>
                <select name="quarter" class="form-select border-0 bg-light" style="border-radius: 10px;">
                    <option value="1" {{ $quarter == 1 ? 'selected' : '' }}>Triwulan I (Jan - Mar)</option>
                    <option value="2" {{ $quarter == 2 ? 'selected' : '' }}>Triwulan II (Apr - Jun)</option>
                    <option value="3" {{ $quarter == 3 ? 'selected' : '' }}>Triwulan III (Jul - Sep)</option>
                    <option value="4" {{ $quarter == 4 ? 'selected' : '' }}>Triwulan IV (Okt - Des)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 shadow-sm" style="border-radius: 10px; padding: 10px;">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.rekap.export', request()->all()) }}" class="btn btn-success w-100 shadow-sm" style="border-radius: 10px; padding: 10px;">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 15px;">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nama Petugas</th>
                        <th class="d-none d-lg-table-cell">Layanan PST</th>
                        <th class="d-none d-lg-table-cell">Layanan Online</th>
                        <th>Total</th>
                        <th>Selesai</th>
                        <th class="d-none d-md-table-cell">Rata-rata Rating</th>
                        <th class="text-end pe-3">Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rekap as $item)
                        @php
                            $percentage = $item['total'] > 0 ? round(($item['selesai'] / $item['total']) * 100, 1) : 0;
                            $ratingColor = $item['rating'] >= 4.5 ? 'success' : ($item['rating'] >= 3.5 ? 'warning' : 'danger');
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold">{{ $item['user']->name }}</div>
                                <small class="text-muted d-none d-md-block">{{ $item['user']->email }}</small>
                            </td>
                            <td class="d-none d-lg-table-cell"><span class="badge bg-info text-dark rounded-pill">{{ $item['total_pst'] }}</span></td>
                            <td class="d-none d-lg-table-cell"><span class="badge bg-warning text-dark rounded-pill">{{ $item['total_online'] }}</span></td>
                            <td><span class="badge bg-primary rounded-pill">{{ $item['total'] }}</span></td>
                            <td><span class="badge bg-success rounded-pill">{{ $item['selesai'] }}</span></td>
                            <td class="d-none d-md-table-cell">
                                @if($item['rating'] > 0)
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="text-warning small">
                                            @for($i=1; $i<=5; $i++)
                                                <i class="fas fa-star {{ $i <= $item['rating'] ? '' : 'text-muted opacity-25' }}"></i>
                                            @endfor
                                        </div>
                                        <span class="fw-bold text-{{ $ratingColor }} small">{{ $item['rating'] }}</span>
                                    </div>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-3">
                                    <div class="progress flex-grow-1 d-none d-lg-flex" style="height: 8px; max-width: 100px; background: #eef2f7;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <span class="fw-bold text-success">{{ $percentage }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Data tidak ditemukan untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4 row">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 15px; background: linear-gradient(135deg, #1e40af, #3b82f6); color: white;">
            <div class="card-body d-flex align-items-center gap-4 p-4">
                <div style="font-size: 3rem; opacity: 0.5;">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1">Informasi</h5>
                    <p class="mb-0 opacity-75 small">Data ini mencakup semua kunjungan yang ditangani oleh setiap petugas pada Triwulan {{ $quarter }} Tahun {{ $year }}.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #64748b;
        border: none;
        padding: 15px 10px;
    }
    .table tbody td {
        padding: 15px 10px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
    }
    .badge {
        font-weight: 500;
        padding: 6px 12px;
    }
</style>
@endpush
