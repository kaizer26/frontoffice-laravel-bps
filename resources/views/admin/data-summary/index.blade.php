@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-layer-group text-primary me-2"></i>Rekap Data Antar Tabel</h1>
        <p class="text-muted small mb-0">Gabungkan data dari berbagai tabel ke dalam satu laporan</p>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="fas fa-cog me-2"></i>Pengaturan Rekap</h6>
            </div>
            <div class="card-body">
                <form id="rekapForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">1. Pilih Tabel Data</label>
                        <div class="border rounded p-2" style="max-height: 250px; overflow-y: auto;">
                            @foreach($registries as $registry)
                                <div class="form-check mb-2">
                                    <input class="form-check-input registry-checkbox" type="checkbox" 
                                           name="registry_ids[]" value="{{ $registry->id }}" 
                                           id="reg{{ $registry->id }}" data-type="{{ $registry->periode_tipe }}">
                                    <label class="form-check-label" for="reg{{ $registry->id }}">
                                        {{ $registry->judul }}
                                        <span class="badge bg-light text-dark border small ms-1">{{ ucfirst($registry->periode_tipe) }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted mt-1 d-block">Pilih tabel dengan tipe periode yang sama.</small>
                    </div>

                    <div class="mb-3" id="periodSection" style="display: none;">
                        <label class="form-label fw-bold">2. Pilih Periode (Bisa > 1)</label>
                        <div id="periodCheckboxes" class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                            <!-- Checkboxes will be rendered here -->
                        </div>
                        <div id="loadingPeriods" class="text-primary small mt-1" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Mencari periode...
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">3. Mode Rekapitulasi</label>
                        <select name="mode" class="form-select shadow-sm">
                            <option value="compact">Ringkasan Indikator (Tahun sebagai Baris)</option>
                            <option value="join">Gabungkan per Keterangan (Kecamatan/Wilayah)</option>
                        </select>
                        <small class="text-muted mt-1 d-block" id="modeDesc">Cocok untuk membandingkan indikator berbeda antar tahun.</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="btnGenerate" disabled>
                        <i class="fas fa-sync me-1"></i> Buat Rekapitulasi
                    </button>
                </form>
            </div>
        </div>

        <div class="alert alert-info small border-0 shadow-sm">
            <h6><i class="fas fa-lightbulb me-2"></i>Tips</h6>
            <ul class="ps-3 mb-0">
                <li>Sistem menampilkan <strong>semua</strong> periode yang tersedia dari tabel-tabel yang dipilih.</li>
                <li>Jika data suatu periode tidak ada di salah satu tabel, kolom akan terisi <span class="text-muted">null</span>.</li>
            </ul>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card shadow-sm" id="resultCard" style="display: none;">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-table me-2"></i>Hasil Rekapitulasi: <span id="displayPeriode">-</span></h6>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" onclick="copyToClipboard()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                    <button class="btn btn-success" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="tableContainer" class="custom-table-container">
                    <table class="table table-bordered table-hover mb-0" id="resultTable" style="min-width: 800px;">
                        <thead class="table-light" id="tableHeader">
                            <!-- Headers go here -->
                        </thead>
                        <tbody id="tableBody">
                            <!-- Data goes here -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="rekapSources" class="card-footer bg-light p-2 small" style="display: none;">
                <div class="fw-bold mb-1"><i class="fas fa-link me-1"></i> Link Sumber Spreadsheet:</div>
                <div id="sourcesList" class="d-flex flex-wrap gap-2">
                    <!-- Links will be populated here -->
                </div>
            </div>
        </div>
        
        <!-- Fallback Textarea for Copying -->
        <textarea id="copyBuffer" style="position: absolute; left: -9999px; top: 0;"></textarea>

        <div id="emptyResult" class="text-center py-5 border rounded bg-light">
            <i class="fas fa-chart-bar fa-3x text-muted mb-3 opacity-25"></i>
            <h5 class="text-muted">Hasil rekap akan muncul di sini</h5>
            <p class="text-muted small">Pilih tabel dan periode di sebelah kiri untuk memulai.</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
#resultTable {
    font-size: 0.85rem;
    border-collapse: separate;
    border-spacing: 0;
}
#resultTable th {
    position: sticky;
    top: 0;
    z-index: 10;
    vertical-align: middle;
    background-color: #f8f9fa !important;
    border-bottom: 2px solid #dee2e6 !important;
}
.text-end {
    text-align: right;
}
.custom-table-container {
    display: block !important;
    max-height: 500px !important;
    overflow: auto !important;
    border: 1px solid #dee2e6;
    width: 100% !important;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    $('.registry-checkbox').on('change', function() {
        const checked = $('.registry-checkbox:checked');
        const registryIds = checked.map(function() { return $(this).val(); }).get();
        
        if (registryIds.length > 0) {
            fetchPeriods(registryIds);
        } else {
            $('#periodSection').hide();
            $('#btnGenerate').prop('disabled', true);
        }
    });

    $('select[name="mode"]').on('change', function() {
        const mode = $(this).val();
        const desc = $('#modeDesc');
        if (mode === 'compact') {
            desc.text('Cocok untuk membandingkan indikator berbeda antar tahun. (Contoh: P0, P1, P2)');
        } else {
            desc.text('Cocok untuk tabel wilayah/kecamatan (membandingkan satu tabel antar tahun).');
        }
    });

    $('#rekapForm').on('submit', function(e) {
        e.preventDefault();
        generateRekap();
    });
});

function fetchPeriods(ids) {
    $('#loadingPeriods').show();
    
    $.ajax({
        url: "{{ route('admin.data-summary.periods') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            registry_ids: ids
        },
        success: function(response) {
            $('#loadingPeriods').hide();
            $('#periodSection').show();
            
            const container = $('#periodCheckboxes');
            container.empty();
            
            if (response.periods.length > 0) {
                response.periods.forEach(p => {
                    container.append(`
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="periods[]" value="${p}" id="p${p}">
                            <label class="form-check-label" for="p${p}">${p}</label>
                        </div>
                    `);
                });
                $('#btnGenerate').prop('disabled', false);
            } else {
                container.append('<p class="text-muted small mb-0">Tidak ada periode tersedia.</p>');
                $('#btnGenerate').prop('disabled', true);
            }
        },
        error: function(xhr) {
            $('#loadingPeriods').hide();
            Swal.fire({
                icon: 'error',
                title: 'Opps...',
                text: xhr.responseJSON?.error || 'Terjadi kesalahan saat mengambil periode.',
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b'
            });
            $('.registry-checkbox').prop('checked', false);
            $('#periodSection').hide();
        }
    });
}

function generateRekap() {
    if ($('input[name="periods[]"]:checked').length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Pilih minimal 1 periode!',
            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b'
        });
        return;
    }

    const formData = $('#rekapForm').serialize();
    
    $.ajax({
        url: "{{ route('admin.data-summary.generate') }}",
        method: "POST",
        data: formData,
        success: function(response) {
            renderTable(response);
            $('#emptyResult').hide();
            $('#resultCard').show();
            showToast('Rekapitulasi berhasil dibuat');
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Gagal membuat rekapitulasi.',
                background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b'
            });
        }
    });
}

function renderTable(data) {
    $('#displayPeriode').text(data.periods.join(', '));
    
    // Header
    const head = $('#tableHeader');
    head.empty();
    const trHead = $('<tr></tr>');
    data.headers.forEach(h => {
        trHead.append(`<th>${h}</th>`);
    });
    head.append(trHead);
    
    // Body
    const body = $('#tableBody');
    body.empty();
    data.summary.forEach(row => {
        const tr = $('<tr></tr>');
        row.forEach((cell, idx) => {
            const isNumeric = idx > 0 && cell !== null && !isNaN(parseFloat(cell));
            const content = cell === null ? '<span class="text-muted italic">null</span>' : cell;
            const td = $(`<td>${content}</td>`);
            if (isNumeric) td.addClass('text-end');
            tr.append(td);
        });
        body.append(tr);
    });

    // Sources
    const sourcesContainer = $('#rekapSources');
    const sourcesList = $('#sourcesList');
    sourcesList.empty();
    
    let hasLinks = false;
    data.registries.forEach(reg => {
        if (reg.link_spreadsheet) {
            hasLinks = true;
            sourcesList.append(`
                <a href="${reg.link_spreadsheet}" target="_blank" class="btn btn-xs btn-outline-success py-0 px-2" style="font-size: 0.75rem;">
                    <i class="fas fa-file-excel me-1"></i> ${reg.judul}
                </a>
            `);
        }
    });

    if (hasLinks) {
        sourcesContainer.show();
    } else {
        sourcesContainer.hide();
    }
}

function copyToClipboard() {
    const table = document.getElementById('resultTable');
    let text = '';
    
    // Headers
    const headers = [];
    table.querySelectorAll('thead th').forEach(h => headers.push(h.innerText));
    text += headers.join('\t') + '\n';
    
    // Body
    table.querySelectorAll('tbody tr').forEach(tr => {
        const row = [];
        tr.querySelectorAll('td').forEach(td => row.push(td.innerText));
        text += row.join('\t') + '\n';
    });
    
    // Attempt navigator.clipboard first
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Data berhasil disalin ke clipboard');
        }).catch(err => {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const textArea = document.getElementById('copyBuffer');
    textArea.value = text;
    textArea.select();
    try {
        document.execCommand('copy');
        showToast('Data berhasil disalin ke clipboard (fallback)');
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Gagal menyalin data. Silakan blok tabel dan copy manual.',
            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b'
        });
    }
}

function exportToExcel() {
    Swal.fire({
        icon: 'info',
        title: 'Informasi',
        text: 'Fungsi Export Excel sedang disiapkan. Sementara gunakan fitur Copy dan Paste ke Excel.',
        background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
        color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b'
    });
}
</script>
@endpush
