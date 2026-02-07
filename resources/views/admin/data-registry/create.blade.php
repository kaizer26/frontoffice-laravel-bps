@extends('layouts.dashboard')

@section('sidebar')
    @include('partials.sidebar-admin')
@endsection

@section('title_section')
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-plus text-primary me-2"></i>Tambah Tabel Data Baru</h1>
        <p class="text-muted small mb-0">Buat template tabel Excel-like untuk data dinamis</p>
    </div>
@endsection

@section('content')
<form id="registryForm" action="{{ route('admin.data-registry.store') }}" method="POST">
    @csrf
    
    <div class="row">
        <!-- Left Column: Metadata -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Tabel</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Judul Tabel <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul" required placeholder="misal: Produksi Padi per Kecamatan">
                        @error('judul')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3" placeholder="Deskripsi singkat tentang data ini..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" class="form-control" name="satuan" placeholder="Ton, Persen, Orang, dll">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipe Periode <span class="text-danger">*</span></label>
                        <select class="form-select" name="periode_tipe" required>
                            <option value="tahunan">Tahunan</option>
                            <option value="semesteran">Semesteran</option>
                            <option value="triwulanan">Triwulanan</option>
                            <option value="bulanan">Bulanan</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Layout Template <span class="text-danger">*</span></label>
                        <select class="form-select" name="layout_type" required>
                            <option value="vertical">Vertical (Tahun ke Bawah)</option>
                            <option value="horizontal">Horizontal (Tahun ke Kanan)</option>
                        </select>
                        <small class="text-muted">
                            <strong>Vertical:</strong> Kolom tetap, tahun di baris (misal: Penduduk per Kelompok Umur)<br>
                            <strong>Horizontal:</strong> Baris tetap, tahun di kolom (misal: PDRB per Lapangan Usaha)
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Format Angka <span class="text-danger">*</span></label>
                        <select class="form-select" name="numeric_format" required>
                            <option value="id" selected>Indonesia (1.234,56)</option>
                            <option value="en">Internasional (1,234.56)</option>
                        </select>
                        <small class="text-muted">
                            <strong>Indonesia:</strong> Titik untuk ribuan, koma untuk desimal<br>
                            <strong>Internasional:</strong> Koma untuk ribuan, titik untuk desimal
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Digit Desimal <span class="text-danger">*</span></label>
                        <select class="form-select" name="decimal_places" required>
                            <option value="0">0 (Bilangan bulat)</option>
                            <option value="1">1 (  contoh: 1.234,5)</option>
                            <option value="2" selected>2 (contoh: 1.234,56)</option>
                            <option value="3">3 (contoh: 1.234,567)</option>
                            <option value="4">4 (contoh: 1.234,5678)</option>
                        </select>
                        <small class="text-muted">Jumlah digit di belakang koma/titik desimal</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sumber Data</label>
                        <input type="text" class="form-control" name="sumber_data" placeholder="BPS Kab. Tanah Bumbu">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Link Spreadsheet (Opsional)</label>
                        <input type="url" class="form-control" name="link_spreadsheet" placeholder="https://docs.google.com/spreadsheets/d/...">
                        <small class="text-muted">Link ke sumber data di Google Sheets (jika ada)</small>
                        @error('link_spreadsheet')
                            <br><small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Column: Template Builder -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-table"></i> Desain Template Excel</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary" onclick="addRow()">
                                <i class="fas fa-plus"></i> Tambah Baris
                            </button>
                            <button type="button" class="btn btn-outline-primary" onclick="addColumn()">
                                <i class="fas fa-plus"></i> Tambah Kolom
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="removeRow()">
                                <i class="fas fa-minus"></i> Hapus Baris
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="removeColumn()">
                                <i class="fas fa-minus"></i> Hapus Kolom
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="mergeCells()">
                                <i class="fas fa-compress-alt"></i> Merge Cells
                            </button>
                            <button type="button" class="btn btn-outline-warning" onclick="unmergeCells()">
                                <i class="fas fa-expand-alt"></i> Unmerge Cells
                            </button>
                        </div>
                    </div>
                    
                    <div class="handsontable-wrapper">
                        <div id="hot-container" style="height: 500px; overflow: hidden;"></div>
                    </div>
                    <input type="hidden" name="template_json" id="template_json" required>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Template
                    </button>
                    <a href="{{ route('admin.data-registry.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<!-- Handsontable -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css">
<script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>

<style>
/* Scope Handsontable styles to prevent global conflicts */
.handsontable-wrapper {
    width: 100%;
    isolation: isolate;
}

/* Override any conflicting global styles within Handsontable */
.handsontable-wrapper .handsontable {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

/* Ensure buttons outside Handsontable aren't affected */
.card-body .btn-group {
    z-index: 10;
    position: relative;
}
</style>

<script>
let hotInstance;

document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('hot-container');
    
    // Default settings for template builder
    const numericFormat = 'id';
    const decimalPlaces = 2;
    const decimalPattern = '0'.repeat(decimalPlaces);
    const pattern = `0,0.${decimalPattern}`;

    const numericFormatter = {
        pattern: pattern,
        culture: 'id-ID'
    };

    hotInstance = new Handsontable(container, {
        data: [
            ['', 'Kecamatan', 'Tahun 2024', ''],
            ['', '', 'Produksi (Ton)', 'Luas (Ha)'],
            ['1', '', '', ''],
            ['2', '', '', ''],
            ['3', '', '', ''],
        ],
        colHeaders: true,
        rowHeaders: true,
        contextMenu: true,
        mergeCells: [
            {row: 0, col: 2, rowspan: 1, colspan: 2}
        ],
        manualColumnResize: true,
        manualRowResize: true,
        licenseKey: 'non-commercial-and-evaluation',
        numericFormat: numericFormatter,
        language: 'id-ID'
    });
});

function addRow() {
    hotInstance.alter('insert_row_below', hotInstance.countRows());
}

function addColumn() {
    hotInstance.alter('insert_col_end');
}

function removeRow() {
    const selected = hotInstance.getSelected();
    if (selected && selected.length > 0) {
        const rowIndex = selected[0][0];
        hotInstance.alter('remove_row', rowIndex);
        Swal.fire({
            icon: 'success',
            title: 'Baris Dihapus',
            text: `Baris ${rowIndex + 1} berhasil dihapus`,
            timer: 1500,
            showConfirmButton: false
        });
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Baris',
            text: 'Klik pada baris yang ingin dihapus terlebih dahulu'
        });
    }
}

function removeColumn() {
    const selected = hotInstance.getSelected();
    if (selected && selected.length > 0) {
        const colIndex = selected[0][1];
        hotInstance.alter('remove_col', colIndex);
        Swal.fire({
            icon: 'success',
            title: 'Kolom Dihapus',
            text: `Kolom berhasil dihapus`,
            timer: 1500,
            showConfirmButton: false
        });
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Kolom',
            text: 'Klik pada kolom yang ingin dihapus terlebih dahulu'
        });
    }
}

function mergeCells() {
    const selected = hotInstance.getSelected();
    if (selected && selected[0]) {
        const [startRow, startCol, endRow, endCol] = selected[0];
        
        // Check if selection is more than 1 cell
        if (startRow === endRow && startCol === endCol) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Lebih dari 1 Cell',
                text: 'Drag untuk memilih beberapa cell yang ingin di-merge'
            });
            return;
        }
        
        hotInstance.getPlugin('mergeCells').merge(startRow, startCol, endRow, endCol);
        hotInstance.render();
        
        Swal.fire({
            icon: 'success',
            title: 'Cells Merged',
            text: 'Cell berhasil digabungkan',
            timer: 1500,
            showConfirmButton: false
        });
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Cells',
            text: 'Drag untuk memilih cell yang ingin di-merge'
        });
    }
}

function unmergeCells() {
    const selected = hotInstance.getSelected();
    if (selected && selected[0]) {
        const [startRow, startCol] = selected[0];
        const mergePlugin = hotInstance.getPlugin('mergeCells');
        
        // Try to unmerge the selected cell
        mergePlugin.unmerge(startRow, startCol);
        hotInstance.render();
        
        Swal.fire({
            icon: 'success',
            title: 'Cells Unmerged',
            text: 'Cell berhasil dipisahkan',
            timer: 1500,
            showConfirmButton: false
        });
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'Pilih Cell',
            text: 'Klik pada merged cell yang ingin dipisahkan'
        });
    }
}

document.getElementById('registryForm').addEventListener('submit', function(e) {
    const data = hotInstance.getData();
    const mergeCells = hotInstance.getPlugin('mergeCells').mergedCellsCollection.mergedCells;
    
    const template = {
        data: data,
        mergeCells: mergeCells.map(m => ({
            row: m.row,
            col: m.col,
            rowspan: m.rowspan,
            colspan: m.colspan
        })),
        colWidths: hotInstance.getPlugin('manualColumnResize').manualColumnWidths,
        rowHeights: hotInstance.getPlugin('manualRowResize').manualRowHeights
    };
    
    document.getElementById('template_json').value = JSON.stringify(template);
});
</script>
@endpush
