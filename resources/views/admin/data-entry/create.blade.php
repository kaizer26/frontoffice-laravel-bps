@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-plus text-primary me-2"></i>Tambah Data Periode</h1>
        <p class="text-muted small mb-0">{{ $registry->judul }}</p>
    </div>
@endsection

@section('content')
<form id="entryForm" action="{{ route('admin.data-entry.store', $registry) }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-calendar"></i> Info Periode</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Periode <span class="text-danger">*</span></label>
                        @php
                            $example = match($registry->periode_tipe) {
                                'bulanan' => '2024-01',
                                'triwulanan' => '2024-Q1',
                                'semesteran' => '2024-S1',
                                default => '2024'
                            };
                            $rangeExample = match($registry->periode_tipe) {
                                'bulanan' => '2024-01--2024-12',
                                'triwulanan' => '2024-Q1--2024-Q4',
                                'semesteran' => '2024-S1--2024-S2',
                                default => '2010-2035'
                            };
                        @endphp
                        <input type="text" class="form-control" name="periode" required 
                               placeholder="Contoh: {{ $example }} atau {{ $rangeExample }}">
                        <div class="form-text small">
                            <i class="fas fa-info-circle me-1"></i> Tipe: <span class="badge bg-light text-dark border">{{ ucfirst($registry->periode_tipe) }}</span>
                        </div>
                        @error('periode')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="alert alert-primary small border-0 shadow-xs">
                        <i class="fas fa-magic me-1"></i> 
                        <strong>Fitur Smart Bulk Import:</strong><br>
                        Gunakan format <code>{{ $rangeExample }}</code> untuk membuat banyak periode sekaligus!<br>
                        <hr class="my-2 opacity-10">
                        <ol class="ps-3 mb-0">
                            <li>Isi kolom periode dengan rentang (misal: <code>{{ $rangeExample }}</code>).</li>
                            <li>Paste tabel dari Excel yang berisi label periode di <strong>Kolom Pertama</strong>.</li>
                            <li>Sistem akan otomatis memecah setiap baris menjadi entri periode sendiri.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-table"></i> Input Data</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary" onclick="hotInstance.alter('insert_row_below', hotInstance.countRows())">
                                <i class="fas fa-plus"></i> Tambah Baris Data
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="removeLastDataRow()">
                                <i class="fas fa-minus"></i> Hapus Baris Terakhir
                            </button>
                        </div>
                    </div>
                    
                    <div class="handsontable-wrapper">
                        <div id="hot-container" style="height: 500px; overflow: hidden;"></div>
                    </div>
                    <input type="hidden" name="data_json" id="data_json" required>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Data
                    </button>
                    <a href="{{ route('admin.data-entry.index', $registry) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
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
let hotInstance;
// Get template directly as JSON - Laravel already decoded it
const templateRaw = {!! json_encode($registry->template_json) !!};
const numericFormat = "{{ $registry->numeric_format ?? 'id' }}";
const decimalPlaces = {{ $registry->decimal_places ?? 2 }};

// Parse if it's a string
const templateData = typeof templateRaw === 'string' ? JSON.parse(templateRaw) : templateRaw;

console.log('Template type:', typeof templateRaw);
console.log('Template parsed:', templateData);
console.log('Template data rows:', templateData.data ? templateData.data.length : 0);
console.log('Numeric format:', numericFormat, 'Decimal places:', decimalPlaces);

// Configure number format based on setting
const decimalPattern = '0'.repeat(decimalPlaces);
const pattern = decimalPlaces > 0 ? `0,0.${decimalPattern}` : '0,0';

const numericFormatter = {
    pattern: pattern,
    culture: numericFormat === 'id' ? 'id-ID' : 'en-US'
};

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('hot-container');
    
    // Initialize with template data
    hotInstance = new Handsontable(container, {
        data: templateData.data || [[]],
        colHeaders: true,
        rowHeaders: true,
        contextMenu: ['row_above', 'row_below', 'remove_row'],
        mergeCells: templateData.mergeCells || [],
        manualColumnResize: true,
        manualRowResize: true,
        colWidths: templateData.colWidths || undefined,
        rowHeights: templateData.rowHeights || undefined,
        licenseKey: 'non-commercial-and-evaluation',
        // Apply numeric formatting
        numericFormat: numericFormatter,
        language: numericFormat === 'id' ? 'id-ID' : 'en-US'
    });
    
    console.log('Handsontable initialized with', hotInstance.countRows(), 'rows and', hotInstance.countCols(), 'cols');
});

function removeLastDataRow() {
    const rowCount = hotInstance.countRows();
    if (rowCount > 0) {
        hotInstance.alter('remove_row', rowCount - 1);
    }
}

document.getElementById('entryForm').addEventListener('submit', function(e) {
    const data = hotInstance.getData();
    const mergeCells = hotInstance.getPlugin('mergeCells').mergedCellsCollection.mergedCells;
    
    const dataEntry = {
        data: data,
        mergeCells: mergeCells.map(m => ({
            row: m.row,
            col: m.col,
            rowspan: m.rowspan,
            colspan: m.colspan
        }))
    };
    
    document.getElementById('data_json').value = JSON.stringify(dataEntry);
});
</script>
@endpush
