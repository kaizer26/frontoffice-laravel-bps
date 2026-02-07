@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-eye text-primary me-2"></i>{{ $registry->judul }}</h1>
        <p class="text-muted small mb-0">Lihat dan filter data per periode</p>
    </div>
@endsection

@section('content')
<!-- Header Actions -->
<div class="row mb-3">
    <div class="col-md-6">
        <a href="{{ route('admin.data-registry.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <a href="{{ route('admin.data-entry.create', $registry) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Data
        </a>
    </div>
</div>

@if($entries->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
            <h5>Belum ada data untuk tabel ini</h5>
            <p class="text-muted">Mulai dengan menambahkan data periode pertama.</p>
            <a href="{{ route('admin.data-entry.create', $registry) }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tambah Data Pertama
            </a>
        </div>
    </div>
@else
    <!-- Filter Panel -->
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3"><i class="fas fa-filter"></i> Filter Periode</h6>
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label small">Pilih Periode (bisa lebih dari 1)</label>
                            <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    <label class="form-check-label fw-bold" for="selectAll">
                                        Pilih Semua
                                    </label>
                                </div>
                                <hr class="my-2">
                                @foreach($entries as $entry)
                                    <div class="form-check">
                                        <input class="form-check-input periode-checkbox" type="checkbox" 
                                               value="{{ $entry->id }}" id="periode{{ $entry->id }}"
                                               onchange="updateSelectedPeriods()">
                                        <label class="form-check-label" for="periode{{ $entry->id }}">
                                            {{ $entry->periode }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <button class="btn btn-primary btn-sm mt-2 w-100" onclick="loadSelectedPeriods()">
                                <i class="fas fa-sync"></i> Tampilkan Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="mb-2"><i class="fas fa-info-circle"></i> Info Data</h6>
                    <div class="small">
                        <div><strong>Periode Dipilih:</strong> <span id="selectedPeriode">{{ $entries->first()->periode }}</span></div>
                        <div><strong>Total Baris:</strong> <span id="totalRows">-</span></div>
                        <div><strong>Format:</strong> {{ $registry->numeric_format == 'id' ? 'Indonesia' : 'Internasional' }} ({{ $registry->decimal_places }} desimal)</div>
                        @if($registry->link_spreadsheet)
                            <div class="mt-2">
                                <a href="{{ $registry->link_spreadsheet }}" target="_blank" class="btn btn-xs btn-outline-success py-0 px-1" style="font-size: 0.75rem;">
                                    <i class="fas fa-file-excel"></i> Buka Spreadsheet Source
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Display Card -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-table"></i> Data Tabel
                </h6>
                <button class="btn btn-sm btn-success" onclick="exportTable()">
                    <i class="fas fa-download"></i> Export Excel
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="tableContainer" class="custom-table-container">
                <table class="table table-bordered table-hover mb-0" id="dataTable" style="min-width: 800px;">
                    <thead id="tableHead">
                        <!-- Headers will be populated by JavaScript -->
                    </thead>
                    <tbody id="tableBody">
                        <!-- Data will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
#dataTable {
    font-size: 0.9rem;
    border-collapse: separate; /* Required for sticky border */
    border-spacing: 0;
}

#dataTable th {
    background-color: #f8f9fa;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 10;
    border: 1px solid #dee2e6;
}

/* For multi-row headers, adjust sticky top */
#dataTable thead tr:nth-child(2) th { top: 41px; }
#dataTable thead tr:nth-child(3) th { top: 82px; }

#dataTable tbody td,
#dataTable tbody th {
    border: 1px solid #dee2e6;
    padding: 0.5rem;
    vertical-align: middle;
}

#dataTable tbody tr:hover {
    background-color: #f8f9fa;
}

/* Numeric columns alignment */
.text-end {
    text-align: right;
}

.custom-table-container {
    display: block !important;
    max-height: 400px !important;
    overflow-y: auto !important;
    overflow-x: auto !important;
    border: 1px solid #dee2e6;
    width: 100% !important;
}
</style>
@endpush

@push('scripts')
<script>
@if($entries->isNotEmpty())
// All entry data
const entriesData = @json($entries->map(function($entry) {
    return [
        'id' => $entry->id,
        'periode' => $entry->periode,
        'data_json' => $entry->data_json
    ];
}));

const numericFormat = "{{ $registry->numeric_format ?? 'id' }}";
const decimalPlaces = {{ $registry->decimal_places ?? 2 }};

function parseLocalNumber(value) {
    if (value === null || value === undefined || value === '') return NaN;
    if (typeof value === 'number') return value;
    
    let str = value.toString().trim();
    if (numericFormat === 'id') {
        // Indonesia: 1.234,56 -> 1234.56
        str = str.replace(/\./g, '').replace(',', '.');
    } else {
        // International: 1,234.56 -> 1234.56
        str = str.replace(/,/g, '');
    }
    return parseFloat(str);
}

function formatNumber(value) {
    const num = parseLocalNumber(value);
    if (isNaN(num)) return value;
    
    // Format based on settings
    if (numericFormat === 'id') {
        // Indonesia: 1.234,56
        return num.toLocaleString('id-ID', {
            minimumFractionDigits: decimalPlaces,
            maximumFractionDigits: decimalPlaces,
            useGrouping: true
        });
    } else {
        // International: 1,234.56
        return num.toLocaleString('en-US', {
            minimumFractionDigits: decimalPlaces,
            maximumFractionDigits: decimalPlaces,
            useGrouping: true
        });
    }
}

function isNumeric(value) {
    const num = parseLocalNumber(value);
    return !isNaN(num) && isFinite(num);
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.periode-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateSelectedPeriods();
}

function updateSelectedPeriods() {
    const checkboxes = document.querySelectorAll('.periode-checkbox:checked');
    const selectAll = document.getElementById('selectAll');
    const totalCheckboxes = document.querySelectorAll('.periode-checkbox').length;
    
    selectAll.checked = checkboxes.length === totalCheckboxes;
    
    // Update info
    const selectedCount = checkboxes.length;
    const selectedPeriods = Array.from(checkboxes).map(cb => {
        const entry = entriesData.find(e => e.id == cb.value);
        return entry ? entry.periode : '';
    }).join(', ');
    
    document.getElementById('selectedPeriode').textContent = 
        selectedCount === 0 ? '-' : 
        selectedCount === 1 ? selectedPeriods : 
        `${selectedCount} periode (${selectedPeriods})`;
}

function loadSelectedPeriods() {
    const checkboxes = document.querySelectorAll('.periode-checkbox:checked');
    
    if (checkboxes.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Pilih minimal 1 periode!',
            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b'
        });
        return;
    }
    
    const selectedIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
    const selectedEntries = entriesData.filter(e => selectedIds.includes(e.id));
    const layoutType = "{{ $registry->layout_type ?? 'vertical' }}";
    
    let combinedData = [];
    
    // Heuristic to find header row count from the first entry
    const getHeaderRowCount = (data) => {
        if (!data || data.length === 0) return 1;
        // First row is always header
        for (let i = 1; i < data.length; i++) {
            // If first column is not empty/null, it's likely a data row start
            // (e.g., has a Year like 2010 or a label like 'Pertanian')
            if (data[i][0] !== null && data[i][0] !== undefined && data[i][0].toString().trim() !== "") {
                return i;
            }
        }
        return 1;
    };

    if (layoutType === 'vertical') {
        // VERTICAL: Stack rows (tahun ke bawah)
        selectedEntries.forEach((entry, index) => {
            const dataJson = typeof entry.data_json === 'string' ? JSON.parse(entry.data_json) : entry.data_json;
            const tableData = dataJson.data || [];
            const headerRowCount = getHeaderRowCount(tableData);
            
            if (index === 0) {
                // First entry: include all rows
                combinedData = tableData.map(row => [...row]);
            } else {
                // Subsequent entries: skip header rows
                combinedData = combinedData.concat(tableData.slice(headerRowCount));
            }
        });
    } else {
        // HORIZONTAL: Merge columns (tahun ke kanan)
        const firstEntry = selectedEntries[0];
        const firstData = typeof firstEntry.data_json === 'string' ? JSON.parse(firstEntry.data_json) : firstEntry.data_json;
        const firstTable = firstData.data || [[]];
        const headerRowCount = getHeaderRowCount(firstTable);
        
        if (firstTable.length > 0) {
            const headers = firstTable.slice(0, headerRowCount);
            const dataRows = firstTable.slice(headerRowCount);
            
            // Determine how many header rows to keep
            // If we have multi-level headers, we might want to add period label on top
            let periodHeader = [""]; // New top header for period labels
            let processedHeaders = headers.map(h => [h[0] || ""]);
            let processedDataRows = dataRows.map(row => [row[0] || ""]);
            
            selectedEntries.forEach((entry) => {
                const dataJson = typeof entry.data_json === 'string' ? JSON.parse(entry.data_json) : entry.data_json;
                const tableData = dataJson.data || [];
                const entryHeaders = tableData.slice(0, headerRowCount);
                const entryDataRows = tableData.slice(headerRowCount);
                
                // For each data column in the template
                for (let colIndex = 1; colIndex < (firstTable[0] || []).length; colIndex++) {
                    // Add period name to top header
                    periodHeader.push(entry.periode);
                    
                    // Add existing header labels
                    processedHeaders.forEach((ph, hIndex) => {
                        ph.push((entryHeaders[hIndex] || [])[colIndex] || "");
                    });
                    
                    // Add data rows
                    processedDataRows.forEach((pdr, rIndex) => {
                        const sourceRow = entryDataRows[rIndex] || [];
                        pdr.push(sourceRow[colIndex] || "");
                    });
                }
            });
            
            combinedData = [periodHeader, ...processedHeaders, ...processedDataRows];
        }
    }
    
    // Update total rows
    const firstDataRaw = typeof selectedEntries[0].data_json === 'string' ? JSON.parse(selectedEntries[0].data_json).data : selectedEntries[0].data_json.data;
    const initialHeaderRowCount = getHeaderRowCount(firstDataRaw);
    const totalHeaderRowsInCombined = layoutType === 'horizontal' ? (1 + initialHeaderRowCount) : initialHeaderRowCount;
    document.getElementById('totalRows').textContent = combinedData.length - totalHeaderRowsInCombined;
    
    // Render combined table
    const thead = document.getElementById('tableHead');
    const tbody = document.getElementById('tableBody');
    thead.innerHTML = '';
    tbody.innerHTML = '';
    
    combinedData.forEach((row, rowIndex) => {
        const isHeader = rowIndex < totalHeaderRowsInCombined;
        const tr = document.createElement('tr');
        
        row.forEach((cell, colIndex) => {
            const td = document.createElement(isHeader ? 'th' : 'td');
            
            if (!isHeader && colIndex > 0 && isNumeric(cell)) {
                td.textContent = formatNumber(cell);
                td.className = 'text-end';
            } else {
                td.textContent = cell || '';
            }
            
            if (isHeader) {
                td.className = 'bg-light text-center';
            }
            
            tr.appendChild(td);
        });
        
        if (isHeader) {
            thead.appendChild(tr);
        } else {
            tbody.appendChild(tr);
        }
    });
}

function exportTable() {
    const checkboxes = document.querySelectorAll('.periode-checkbox:checked');
    if (checkboxes.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: 'Pilih minimal 1 periode untuk export!',
            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b'
        });
        return;
    }
    
    const selectedIds = Array.from(checkboxes).map(cb => cb.value);
    // TODO: Implement multi-period export
    Swal.fire({
        icon: 'info',
        title: 'Informasi',
        text: 'Fitur export untuk multiple periode akan segera tersedia!',
        background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
        color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b'
    });
}

// Auto-select first period and load on page load
document.addEventListener('DOMContentLoaded', function() {
    const firstCheckbox = document.querySelector('.periode-checkbox');
    if (firstCheckbox) {
        firstCheckbox.checked = true;
        updateSelectedPeriods();
        loadSelectedPeriods();
    }
});
@endif
</script>
@endpush
