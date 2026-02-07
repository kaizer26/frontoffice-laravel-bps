@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-edit text-primary me-2"></i>Edit Data Periode</h1>
        <p class="text-muted small mb-0">{{ $registry->judul }} - {{ $entry->periode }}</p>
    </div>
@endsection

@section('content')
<form id="entryForm" action="{{ route('admin.data-entry.update', [$registry, $entry]) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-calendar"></i> Info Periode</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Periode <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="periode" required 
                               value="{{ $entry->periode }}"
                               placeholder="misal: 2024, 2024-S1, 2024-Q1">
                        <small class="text-muted">Format sesuai tipe: {{ ucfirst($registry->periode_tipe) }}</small>
                        @error('periode')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle"></i> Data akan mengikuti template yang sudah dibuat.
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
                        <i class="fas fa-save"></i> Update Data
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
let template = @json($registry->template_json);
let existingData = @json($entry->data_json);

// Handle case where template_json might still be a string
if (typeof template === 'string') {
    try {
        template = JSON.parse(template);
    } catch (e) {
        console.error('Error parsing template JSON:', e);
        template = { data: [[]], mergeCells: [] };
    }
}

// Handle case where data_json might still be a string
if (typeof existingData === 'string') {
    try {
        existingData = JSON.parse(existingData);
    } catch (e) {
        console.error('Error parsing data JSON:', e);
        existingData = { data: [[]], mergeCells: [] };
    }
}

// Ensure both have required properties
if (!template || typeof template !== 'object') {
    template = { data: [[]], mergeCells: [] };
}
if (!existingData || typeof existingData !== 'object') {
    existingData = { data: [[]], mergeCells: [] };
}

console.log('Template:', template);
console.log('Existing data:', existingData);

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('hot-container');
    
    // Use existing data if available, otherwise use template
    const dataToLoad = existingData && existingData.data ? existingData.data : template.data;
    const mergeCellsToLoad = existingData && existingData.mergeCells ? existingData.mergeCells : (template.mergeCells || []);
    
    hotInstance = new Handsontable(container, {
        data: dataToLoad || [[]],
        colHeaders: true,
        rowHeaders: true,
        contextMenu: ['row_above', 'row_below', 'remove_row'],
        mergeCells: mergeCellsToLoad,
        manualColumnResize: true,
        manualRowResize: true,
        colWidths: template.colWidths || undefined,
        rowHeights: template.rowHeights || undefined,
        licenseKey: 'non-commercial-and-evaluation'
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
