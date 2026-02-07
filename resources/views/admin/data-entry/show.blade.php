@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-eye text-primary me-2"></i>Lihat Data: {{ $registry->judul }}</h1>
        <p class="text-muted small mb-0">Periode {{ $entry->periode }} - Read Only</p>
    </div>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-filter"></i> Filter Periode</h6>
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small">Mode Filter</label>
                        <select id="filterMode" class="form-select form-select-sm">
                            <option value="all">Semua Tahun</option>
                            <option value="range">Rentang Tahun</option>
                            <option value="single">Tahun Tertentu</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4" id="rangeStartDiv" style="display:none;">
                        <label class="form-label small">Tahun Mulai</label>
                        <input type="number" id="yearStart" class="form-control form-control-sm" placeholder="2010">
                    </div>
                    
                    <div class="col-md-4" id="rangeEndDiv" style="display:none;">
                        <label class="form-label small">Tahun Akhir</label>
                        <input type="number" id="yearEnd" class="form-control form-control-sm" placeholder="2035">
                    </div>
                    
                    <div class="col-md-4" id="singleYearDiv" style="display:none;">
                        <label class="form-label small">Pilih Tahun</label>
                        <select id="singleYear" class="form-select form-select-sm">
                            <!-- Will be populated by JS -->
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label small">&nbsp;</label>
                        <button type="button" class="btn btn-primary btn-sm w-100" onclick="applyFilter()">
                            <i class="fas fa-search"></i> Terapkan Filter
                        </button>
                    </div>
                </div>
                
                <div class="mt-3">
                    <span class="badge bg-info" id="filterInfo">Menampilkan semua data</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-light">
            <div class="card-body">
                <h6 class="mb-2"><i class="fas fa-info-circle"></i> Info Data</h6>
                <div class="small">
                    <div><strong>Periode:</strong> {{ $entry->periode }}</div>
                    <div><strong>Total Baris:</strong> <span id="totalRows">-</span></div>
                    <div><strong>Terakhir Update:</strong> {{ $entry->updated_at->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-table"></i> {{ $registry->judul }}
            </h6>
            <div>
                <button class="btn btn-success btn-sm" onclick="exportToExcel()">
                    <i class="fas fa-download"></i> Export Excel
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="handsontable-wrapper" style="padding: 20px;">
            <div id="hot-container" style="height: 600px; overflow: auto;"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>

<style>
/* Scope Handsontable styles to prevent global conflicts */
.handsontable-wrapper {
    width: 100%;
    isolation: isolate;
}
.handsontable-wrapper .handsontable {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
</style>

<script>
const dataEntry = @json($entry->data_json);
const periodRange = "{{ $entry->periode }}"; // e.g., "2010-2035"
let hotInstance;
let allData = [];
let filteredData = [];

// Parse if it's a string
const parsedData = typeof dataEntry === 'string' ? JSON.parse(dataEntry) : dataEntry;

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('hot-container');
    
    // Store all data
    allData = parsedData.data || [];
    filteredData = [...allData];
    
    const numericFormat = "{{ $registry->numeric_format ?? 'id' }}";
    const decimalPlaces = {{ $registry->decimal_places ?? 2 }};
    const decimalPattern = '0'.repeat(decimalPlaces);
    const pattern = decimalPlaces > 0 ? `0,0.${decimalPattern}` : '0,0';

    const numericFormatter = {
        pattern: pattern,
        culture: numericFormat === 'id' ? 'id-ID' : 'en-US'
    };

    hotInstance = new Handsontable(container, {
        data: filteredData,
        colHeaders: true,
        rowHeaders: true,
        mergeCells: parsedData.mergeCells || [],
        readOnly: true,
        manualColumnResize: true,
        manualRowResize: true,
        licenseKey: 'non-commercial-and-evaluation',
        className: 'htCenter htMiddle',
        stretchH: 'all',
        numericFormat: numericFormatter,
        language: numericFormat === 'id' ? 'id-ID' : 'en-US'
    });
    
    // Update info
    updateInfo();
    
    // Populate year dropdown if period is a range
    populateYearDropdown();
});

// Filter mode change handler
document.getElementById('filterMode').addEventListener('change', function() {
    const mode = this.value;
    document.getElementById('rangeStartDiv').style.display = mode === 'range' ? 'block' : 'none';
    document.getElementById('rangeEndDiv').style.display = mode === 'range' ? 'block' : 'none';
    document.getElementById('singleYearDiv').style.display = mode === 'single' ? 'block' : 'none';
});

function populateYearDropdown() {
    // Extract years from period range (e.g., "2010-2035")
    const match = periodRange.match(/(\d{4})-(\d{4})/);
    if (match) {
        const startYear = parseInt(match[1]);
        const endYear = parseInt(match[2]);
        
        const select = document.getElementById('singleYear');
        select.innerHTML = '';
        
        for (let year = startYear; year <= endYear; year++) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            select.appendChild(option);
        }
        
        // Set default values for range inputs
        document.getElementById('yearStart').value = startYear;
        document.getElementById('yearEnd').value = endYear;
    }
}

function applyFilter() {
    const mode = document.getElementById('filterMode').value;
    
    if (mode === 'all') {
        filteredData = [...allData];
        document.getElementById('filterInfo').textContent = 'Menampilkan semua data';
    } else if (mode === 'range') {
        const startYear = parseInt(document.getElementById('yearStart').value);
        const endYear = parseInt(document.getElementById('yearEnd').value);
        
        if (isNaN(startYear) || isNaN(endYear)) {
            Swal.fire('Error', 'Mohon isi tahun mulai dan akhir', 'error');
            return;
        }
        
        filteredData = filterByYearRange(startYear, endYear);
        document.getElementById('filterInfo').textContent = `Menampilkan data tahun ${startYear} - ${endYear}`;
    } else if (mode === 'single') {
        const year = parseInt(document.getElementById('singleYear').value);
        filteredData = filterByYear(year);
        document.getElementById('filterInfo').textContent = `Menampilkan data tahun ${year}`;
    }
    
    // Update Handsontable
    hotInstance.loadData(filteredData);
    updateInfo();
}

function filterByYearRange(startYear, endYear) {
    // Assume first column contains years (row index 0 is header)
    return allData.filter((row, index) => {
        if (index === 0 || index === 1) return true; // Keep headers
        
        const yearValue = parseInt(row[0]);
        if (isNaN(yearValue)) return false;
        
        return yearValue >= startYear && yearValue <= endYear;
    });
}

function filterByYear(year) {
    return allData.filter((row, index) => {
        if (index === 0 || index === 1) return true; // Keep headers
        
        const yearValue = parseInt(row[0]);
        return yearValue === year;
    });
}

function updateInfo() {
    // Don't count header rows (first 2 rows)
    const dataRowCount = filteredData.length > 2 ? filteredData.length - 2 : 0;
    document.getElementById('totalRows').textContent = dataRowCount;
}

function exportToExcel() {
    const exportPlugin = hotInstance.getPlugin('exportFile');
    exportPlugin.downloadFile('csv', {
        bom: false,
        columnDelimiter: ',',
        columnHeaders: true,
        exportHiddenColumns: true,
        exportHiddenRows: true,
        fileExtension: 'csv',
        filename: '{{ $registry->judul }}_{{ $entry->periode }}_[YYYY]-[MM]-[DD]',
        mimeType: 'text/csv',
        rowDelimiter: '\r\n',
        rowHeaders: true
    });
}
</script>
@endpush
