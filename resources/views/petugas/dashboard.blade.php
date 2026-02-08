@extends('layouts.dashboard')

@push('styles')
<style>
        /* Modern Service Card Improvements */
        .service-card {
            background: var(--bg-card);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.3s ease;
        }
        .service-card:hover {
            box-shadow: 0 8px 16px rgba(0,0,0,0.06);
            border-color: var(--primary);
        }

        /* Action Menu for Mobile */
        @media (max-width: 991.98px) {
            .sidebar {
                z-index: 2000; /* Ensure sidebar is above everything */
            }
            .service-card {
                padding: 12px;
                margin-bottom: 12px;
            }
            .mobile-action-dropdown .dropdown-menu {
                border-radius: 12px;
                border: 1px solid var(--border-color);
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                padding: 8px;
                z-index: 1050;
            }
            .mobile-action-dropdown .dropdown-item {
                border-radius: 8px;
                padding: 10px 15px;
                margin-bottom: 4px;
                font-size: 0.9rem;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .mobile-action-dropdown .dropdown-item:last-child {
                margin-bottom: 0;
            }
            .mobile-action-dropdown .dropdown-item i {
                width: 20px;
                text-align: center;
            }

            /* Main Layout Adjustments */
            .main-content {
                padding: 12px !important;
            }
            .stat-card {
                padding: 15px;
                margin-bottom: 12px;
            }
            .stat-value {
                font-size: 1.3rem;
            }
            .stat-label {
                font-size: 0.75rem;
            }
            
            /* Typography */
            h1, .h3 { font-size: 1.25rem !important; }
            h5 { font-size: 1rem !important; }
            .form-label { font-size: 0.85rem; }
            .badge { font-size: 0.65rem !important; }
            
            /* Table Adjustments */
            .table {
                font-size: 0.8rem;
            }
            .table th, .table td {
                padding: 0.5rem;
            }
            
            /* Form Adjustments */
            .card-body {
                padding: 15px !important;
            }
        }
</style>
@endpush

@section('sidebar')
<div class="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-building-columns"></i> Front Office</h3>
        <small>{{ auth()->user()->name }}</small>
        <span class="badge bg-primary" style="font-size:0.7rem;">Petugas</span>
    </div>
    
    <nav>
        <a href="#" class="nav-item active" onclick="showTab(event, 'bukutamu'); return false;">
            <i class="fas fa-book"></i>
            <span>Buku Tamu</span>
        </a>
        <a href="#" class="nav-item" onclick="showTab(event, 'layanan'); return false;">
            <i class="fas fa-tasks"></i>
            <span>Layanan Saya</span>
        </a>
        <a href="#" class="nav-item" onclick="showTab(event, 'status'); return false;">
            <i class="fas fa-chart-line"></i>
            <span>Status Layanan</span>
        </a>
    </nav>
    
    <div style="position: absolute; bottom: 20px; width: 100%; padding: 0 20px;">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-light w-100">
                <i class="fas fa-sign-out-alt"></i> Logout
            </button>
        </form>
    </div>
</div>
@endsection

@section('title_section')
    <div>
        <h1 class="h3 mb-0">Dashboard Petugas</h1>
        <div class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 0.65rem; padding-right: 8px;">
            <i class="fas fa-circle-play fa-fade me-1"></i> LIVE UPDATES ACTIVE 
            <span id="nextRefreshCounter" class="ms-1 text-muted fw-normal">(30s)</span>
            <button onclick="manualRefresh()" class="btn btn-link btn-sm p-0 ms-2 text-success" style="font-size: 0.7rem; border: none; background: transparent;" title="Refresh Now">
                <i class="fas fa-rotate"></i>
            </button>
        </div>
    </div>
    <div class="text-end d-none d-md-block ms-auto me-3">
        <div class="fw-bold small">{{ now()->translatedFormat('d M Y') }}</div>
        <div class="text-muted small" id="realtimeClock" style="font-size: 0.7rem;">{{ now()->format('H:i:s') }} WITA</div>
    </div>
@endsection

@section('content')

<!-- Attendance Widget -->
<div class="card mb-4 border-0 shadow-sm overflow-hidden" style="border-radius: 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="card-body p-3 p-md-4 text-white position-relative">
        <div class="row align-items-center position-relative" style="z-index: 2;">
            <div class="col-md-7 mb-3 mb-md-0">
                <h5 class="fw-bold mb-1"><i class="fas fa-fingerprint me-2"></i> Presensi Petugas</h5>
                <p class="small mb-0 opacity-75" id="attendanceMessage">Memuat status kehadiran...</p>
            </div>
            <div class="col-md-5 text-md-end" id="attendanceActions">
                <!-- Action buttons will be injected here -->
            </div>
        </div>
        <!-- Decorative background icon -->
        <i class="fas fa-clock position-absolute" style="right: -20px; bottom: -20px; font-size: 8rem; opacity: 0.1; transform: rotate(-15deg);"></i>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-2 g-md-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-value" id="statVisitorsToday">0</div>
            <div class="stat-label">Pengunjung Hari Ini</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-value" id="statVisitorsWeek">0</div>
            <div class="stat-label">Minggu Ini</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-value" id="statVisitorsTotal">0</div>
            <div class="stat-label">Total Dilayani</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-value" id="statRating">0.0</div>
            <div class="stat-label">Rating Saya</div>
        </div>
    </div>
</div>

<!-- Tab Contents -->
<div id="tab-bukutamu" class="tab-content active">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-book"></i> Form Buku Tamu</h5>
            <form id="bukuTamuForm" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Pengunjung <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_pengunjung" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Instansi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="instansi" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">No. HP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="no_hp" id="no_hp" required 
                               placeholder="628xxxxxxxxxx" oninput="handlePhoneInput(this)" autocomplete="off">
                        <div id="phoneSuggestions" class="list-group position-absolute shadow-lg d-none" style="z-index: 1000; width: calc(100% - 24px);"></div>
                        <small class="text-muted">Format: 628xxxxxxxxxx (tanpa spasi/tanda baca)</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Jenis Layanan <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3 flex-wrap">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Konsultasi" id="konsultasi">
                                <label class="form-check-label" for="konsultasi">Konsultasi</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Permintaan Data" id="permintaan" onchange="toggleSuratFields()">
                                <label class="form-check-label" for="permintaan">Permintaan Data</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Rekomendasi Statistik" id="rekomendasi">
                                <label class="form-check-label" for="rekomendasi">Rekomendasi Statistik</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Perpustakaan" id="perpustakaan">
                                <label class="form-check-label" for="perpustakaan">Perpustakaan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Pengaduan" id="pengaduan">
                                <label class="form-check-label" for="pengaduan">Pengaduan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Lainnya" id="lainnya" onchange="toggleLainnyaField()">
                                <label class="form-check-label" for="lainnya">Lainnya</label>
                            </div>
                        </div>
                        <div id="lainnyaField" class="mt-2" style="display:none;">
                            <input type="text" class="form-control" name="jenis_layanan_lainnya" id="jenis_layanan_lainnya" placeholder="Tuliskan jenis layanan lainnya...">
                        </div>
                    </div>
                    
                    <!-- Surat Fields (shown when Permintaan Data selected) -->
                    <div id="suratFields" style="display:none;" class="col-12">
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-file-alt"></i> Detail Surat Permintaan</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nomor Surat <small class="text-muted">(opsional)</small></label>
                                        <input type="text" class="form-control" name="nomor_surat" id="nomor_surat">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal Surat <small class="text-muted">(opsional)</small></label>
                                        <input type="date" class="form-control" name="tanggal_surat" id="tanggal_surat">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Upload Surat (PDF/Gambar, max 5MB) <small class="text-muted">(opsional)</small></label>
                                        <input type="file" class="form-control" name="file_surat" accept=".pdf,.jpg,.jpeg,.png">
                                    </div>
                                    <div class="col-12">
                                        <div class="alert alert-info py-2 mb-0">
                                            <small><i class="fas fa-info-circle"></i> Jika pengunjung belum membawa surat, bisa diisi dan dilengkapi nanti melalui Edit Data.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label class="form-label">Sarana Kunjungan <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sarana_kunjungan" value="Langsung" id="saranaLangsung" checked onchange="toggleOnlineDetails()">
                                <label class="form-check-label" for="saranaLangsung">Kunjungan Langsung (PST)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="sarana_kunjungan" value="Online" id="saranaOnline" onchange="toggleOnlineDetails()">
                                <label class="form-check-label" for="saranaOnline">Layanan Online</label>
                            </div>
                        </div>
                    </div>

                    <!-- Online Service Details (shown when Online selected) -->
                    <div id="onlineChannelDetails" style="display:none;" class="col-12">
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-headset"></i> Saluran Layanan Online</h6>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Dilayani Melalui <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="online_channel" value="Pegawai" id="channelPegawai" onchange="togglePegawaiSelect()">
                                                <label class="form-check-label" for="channelPegawai">Melalui Pegawai BPS</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="online_channel" value="Kontak Admin" id="channelAdmin" onchange="togglePegawaiSelect()">
                                                <label class="form-check-label" for="channelAdmin">Kontak Admin BPS</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-3" id="pegawaiSelectField" style="display:none;">
                                        <label class="form-label">Pilih Pegawai <span class="text-danger">*</span></label>
                                        <select class="form-select" name="petugas_online_id" id="petugas_online_id">
                                            <option value="">-- Pilih Pegawai --</option>
                                            @foreach($petugas as $p)
                                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Keperluan <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="keperluan" rows="3" required placeholder="Jelaskan keperluan kunjungan..."></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Simpan Buku Tamu
                </button>
            </form>
        </div>
    </div>
</div>

<div id="tab-layanan" class="tab-content" style="display:none;">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title d-flex justify-content-between align-items-center">
                <span><i class="fas fa-tasks"></i> Layanan yang Saya Tangani</span>
                @php $driveLink = \App\Models\SystemSetting::get('data_monitoring_drive_link', '#'); @endphp
                <a href="{{ $driveLink }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Buka Google Drive Kumpulan Data">
                    <i class="fab fa-google-drive me-1"></i> Drive Kumpulan Data
                </a>
            </h5>
            <div id="layananList" class="mt-3">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-2 text-muted">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Link Monitor Modal -->
<div class="modal fade" id="linkMonitorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-link text-primary me-2"></i> Link Spreadsheet/Monitoring</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="linkMonitorForm">
                    <input type="hidden" id="linkMonitorBtId">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Link Spreadsheet / Drive Data</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-table"></i></span>
                            <input type="url" class="form-control" id="modalLinkMonitor" placeholder="https://docs.google.com/spreadsheets/d/...">
                        </div>
                        <div class="form-text mt-2 small">
                            <i class="fas fa-info-circle me-1"></i> Masukkan link spreadsheet atau folder drive tempat pengumpulan data untuk layanan ini.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm px-4" onclick="saveLinkMonitor()">
                    <i class="fas fa-save me-1"></i> Simpan Link
                </button>
            </div>
        </div>
    </div>
</div>

<div id="tab-status" class="tab-content" style="display:none;">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-chart-line"></i> Status Semua Layanan</h5>
            
            <!-- Stats Row -->
            <div class="row g-2 g-md-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card" style="background:#dbeafe;">
                        <div class="stat-value text-primary" id="statusDiterima">0</div>
                        <div class="stat-label text-primary">Diterima</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card" style="background:#fef3c7;">
                        <div class="stat-value text-warning" id="statusDiproses">0</div>
                        <div class="stat-label text-warning">Diproses</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card" style="background:#d1fae5;">
                        <div class="stat-value text-success" id="statusSiap">0</div>
                        <div class="stat-label text-success">Siap Diambil</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card" style="background:#e0e7ff;">
                        <div class="stat-value" style="color:#4f46e5;" id="statusSelesai">0</div>
                        <div class="stat-label" style="color:#4f46e5;">Selesai</div>
                    </div>
                </div>
            </div>
            
            <div id="statusList" class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Pengunjung</th>
                            <th class="d-none d-md-table-cell">Instansi</th>
                            <th class="d-none d-lg-table-cell">Jenis</th>
                            <th class="d-none d-xl-table-cell">No. Surat</th>
                            <th class="d-none d-md-table-cell">Petugas</th>
                            <th>Status</th>
                            <th class="d-none d-md-table-cell">Update</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="statusTableBody">
                        <tr>
                            <td colspan="6" class="text-center">Memuat data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Update Status Layanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Pengunjung:</strong> <span id="modalPengunjung"></span>
                </div>
                <div class="mb-3">
                    <strong>No. Surat:</strong> <span id="modalNoSurat"></span>
                </div>
                <div class="mb-3">
                    <label class="form-label">Status Layanan</label>
                    <select class="form-control" id="modalStatus">
                        <option value="Diterima">Diterima</option>
                        <option value="Diproses">Diproses</option>
                        <option value="Menunggu Persetujuan">Menunggu Persetujuan</option>
                        <option value="Siap Diambil">Siap Diambil</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan (opsional)</label>
                    <textarea class="form-control" id="modalCatatan" rows="2"></textarea>
                </div>

                <!-- Pegawai Terlibat Section -->
                <div class="card bg-info-subtle border-info-subtle mb-3">
                    <div class="card-body p-3">
                        <h6 class="card-title text-info"><i class="fas fa-users"></i> Pegawai Terlibat</h6>
                        <p class="small text-muted mb-2">Tambahkan pegawai lain yang membantu (pengumpul data, konsultan, dll). Mereka akan ikut menerima penilaian dari pengunjung.</p>
                        <div id="handlersListContainer">
                            <!-- Dynamic handler rows will be added here -->
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-info w-100" onclick="addHandlerRow()">
                            <i class="fas fa-plus"></i> Tambah Pegawai
                        </button>
                    </div>
                </div>

                <hr>
                <div id="reportFields" style="display:none;">
                    <h6 class="mb-3 text-primary"><i class="fas fa-file-contract"></i> Laporan Hasil Pelayanan</h6>
                    
                    <!-- Common Fields: Consultation/Complaints only -->
                    <div id="commonReportFields" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label">Topik Pelayanan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modalTopik" placeholder="Misal: Penjelasan terkait DTSEN">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Ringkasan Hasil <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="modalRingkasan" rows="3" placeholder="Jelaskan secara ringkas hasil pelayanan..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Foto Bukti Pelayanan <span class="text-danger">* (min. 1 foto)</span></label>
                            <input type="file" class="form-control" id="modalFotoBukti" multiple accept="image/png, image/jpeg, image/jpg, image/webp">
                            <small class="text-muted">Wajib upload minimal 1 foto dokumentasi pelayanan untuk Konsultasi, Rekomendasi Statistik, dan Pengaduan.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Feedback Final Pengunjung</label>
                            <select class="form-control" id="modalFeedback">
                                <option value="Puas">Puas</option>
                                <option value="Perlu penjelasan lebih lanjut">Perlu penjelasan lebih lanjut</option>
                                <option value="Tidak puas">Tidak puas</option>
                            </select>
                        </div>
                    </div>

                    <!-- Information specific to Permintaan Data -->
                    <div id="permintaanDataFields" style="display:none;">
                        <div class="mb-3">
                            <label class="form-label">Tags Data (pisahkan dengan koma)</label>
                            <input type="text" class="form-control" id="modalTags" placeholder="ekonomi, sosial, inflasi">
                        </div>
                        
                        <div class="card bg-primary-subtle border-primary-subtle mb-3">
                            <div class="card-body p-3">
                                <h6 class="card-title text-primary"><i class="fas fa-envelope-open-text"></i> Surat Balasan Otomatis</h6>
                                <p class="small text-muted mb-3">Gunakan fitur ini untuk membuat nomor surat balasan secara otomatis sesuai standar.</p>
                                
                                <div id="replyLetterDraft" style="display:none;">
                                    <div class="row g-2 mb-2">
                                        <div class="col-8">
                                            <label class="small text-muted mb-1">Nomor Surat Tergenerate</label>
                                            <input type="text" class="form-control form-control-sm border-primary" id="modalReplyNomor" readonly>
                                        </div>
                                        <div class="col-4">
                                            <label class="small text-muted mb-1">Kode Surat</label>
                                            <input type="text" class="form-control form-control-sm" id="modalReplyKode" placeholder="02.04">
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="small text-muted mb-1">Tujuan / Penerima</label>
                                        <input type="text" class="form-control form-control-sm text-capitalize" id="modalReplyTujuan" placeholder="Nama/Jabatan Penerima">
                                    </div>
                                    <div class="mb-2">
                                        <label class="small text-muted mb-1">Perihal</label>
                                        <input type="text" class="form-control form-control-sm" id="modalReplyPerihal" placeholder="Penyampaian Data ...">
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-primary flex-grow-1" onclick="generateReplyNumber()">
                                            <i class="fas fa-sync"></i> Refresh No
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success flex-grow-1" onclick="saveReplyLetter()">
                                            <i class="fas fa-check"></i> Simpan & Lanjutkan
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="cancelReplyDraft()">
                                            <i class="fas fa-times"></i> Batal
                                        </button>
                                    </div>
                                    <input type="hidden" id="modalReplyUrut">
                                    <input type="hidden" id="modalReplyTanggal">
                                </div>
                                
                                <div id="replyLetterPreview" style="display:none;" class="bg-white p-2 rounded border border-success-subtle mb-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="badge bg-success">Tersimpan</span>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-danger" onclick="removeReplyLetter()"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <div class="small fw-bold" id="previewReplyNomor"></div>
                                    <div class="small text-muted" id="previewReplyTujuan"></div>
                                    <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-2" onclick="generateWordDocument()">
                                        <i class="fas fa-file-word"></i> Download Surat (Word)
                                    </button>
                                </div>

                                <button type="button" class="btn btn-primary btn-sm w-100" id="btnShowReplyForm" onclick="showReplyForm()">
                                    <i class="fas fa-magic"></i> Generate Nomor Surat Balasan
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Upload Surat Balasan (PDF) <small class="text-muted text-xs">Jika sudah ada file mandiri</small></label>
                            <input type="file" class="form-control" id="modalSuratBalasan" accept=".pdf">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitUpdateStatus(event)">
                    <i class="fas fa-save"></i> Update Status
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Report Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-invoice"></i> Detail Laporan Hasil Pelayanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reportDetailContent">
                <!-- Content injected via JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal (Lightbox) -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" style="background: rgba(0,0,0,0.85); z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-body p-0 text-center position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" style="z-index: 1070;"></button>
                <img id="previewImageSource" src="" class="img-fluid rounded shadow-lg" style="max-height: 90vh; cursor: zoom-out;" data-bs-dismiss="modal">
            </div>
        </div>
    </div>
</div>

<!-- Generic Document Preview Modal -->
<div class="modal fade" id="docPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt"></i> Pratinjau Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="docPreviewContent" style="height: 80vh;">
                    <!-- Content injected via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upcoming Schedule Modal -->
<div class="modal fade" id="upcomingScheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-primary text-white py-3 border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-calendar-check me-2"></i> Jadwal Jaga Mendatang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="bg-primary-subtle rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-clock fa-2x text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Halo, {{ auth()->user()->name }}!</h5>
                    <p class="text-muted small">Anda sedang tidak dalam jadwal jaga saat ini.</p>
                </div>
                
                <div class="schedule-timeline">
                    @forelse($upcomingSchedules as $schedule)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom border-light last-child-no-border">
                            <div class="bg-light rounded p-2 text-center me-3" style="min-width: 60px;">
                                <div class="fw-bold text-primary">{{ $schedule->tanggal->format('d') }}</div>
                                <div class="small text-muted text-uppercase" style="font-size: 0.65rem;">{{ $schedule->tanggal->translatedFormat('M') }}</div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">{{ $schedule->tanggal->translatedFormat('l, d F Y') }}</div>
                                <div class="badge bg-{{ $schedule->shift == 1 ? 'info' : 'warning' }}-subtle text-{{ $schedule->shift == 1 ? 'info' : 'warning' }} mt-1">
                                    <i class="fas fa-sun me-1"></i> Shift {{ $schedule->shift }}
                                    ({{ $schedule->shift == 1 ? \App\Models\SystemSetting::get('shift1_start','08:00').'-'.\App\Models\SystemSetting::get('shift1_end','12:00') : \App\Models\SystemSetting::get('shift2_start','12:00').'-'.\App\Models\SystemSetting::get('shift2_end','16:00') }})
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-alt fa-3x text-muted opacity-25 mb-3"></i>
                            <p class="text-muted">Belum ada jadwal jaga yang terdaftar.</p>
                        </div>
                    @endforelse
                </div>
                
                <div class="mt-4">
                    <button type="button" class="btn btn-primary w-100 py-2 fw-bold" data-bs-dismiss="modal" style="border-radius: 12px;">
                        Siap Bertugas!
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .last-child-no-border:last-child { border-bottom: none !important; margin-bottom: 0 !important; }
    .bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
</style>

<!-- Edit Visitor Modal -->
<div class="modal fade" id="editVisitorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="fas fa-user-edit"></i> Edit Data Pengunjung</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editVisitorForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Pengunjung</label>
                            <input type="text" class="form-control" name="nama_pengunjung" id="edit_nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Instansi</label>
                            <input type="text" class="form-control" name="instansi" id="edit_instansi" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. HP</label>
                            <input type="text" class="form-control" name="no_hp" id="edit_no_hp" required oninput="formatPhoneNumber(this)">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Jenis Layanan</label>
                            <div class="d-flex gap-3 flex-wrap" id="edit_jenis_layanan_container">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Konsultasi" id="edit_konsultasi">
                                    <label class="form-check-label" for="edit_konsultasi">Konsultasi</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Permintaan Data" id="edit_permintaan" onchange="toggleEditSuratFields()">
                                    <label class="form-check-label" for="edit_permintaan">Permintaan Data</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Rekomendasi Statistik" id="edit_rekomendasi">
                                    <label class="form-check-label" for="edit_rekomendasi">Rekomendasi Statistik</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Perpustakaan" id="edit_perpustakaan">
                                    <label class="form-check-label" for="edit_perpustakaan">Perpustakaan</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Pengaduan" id="edit_pengaduan">
                                    <label class="form-check-label" for="edit_pengaduan">Pengaduan</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Lainnya" id="edit_lainnya" onchange="toggleEditLainnyaField()">
                                    <label class="form-check-label" for="edit_lainnya">Lainnya</label>
                                </div>
                            </div>
                            <div id="editLainnyaField" class="mt-2" style="display:none;">
                                <input type="text" class="form-control" name="jenis_layanan_lainnya" id="edit_jenis_layanan_lainnya" placeholder="Tuliskan jenis layanan lainnya...">
                            </div>
                        </div>

                        <div id="editSuratFields" style="display:none;" class="col-12">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="fas fa-file-alt"></i> Detail Surat Permintaan</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nomor Surat</label>
                                            <input type="text" class="form-control" name="nomor_surat" id="edit_nomor_surat">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tanggal Surat</label>
                                            <input type="date" class="form-control" name="tanggal_surat" id="edit_tanggal_surat">
                                        </div>
                                        <div class="col-12 mt-2" id="existingFileSuratContainer" style="display:none;">
                                            <a href="#" id="edit_preview_file_surat" target="_blank" class="btn btn-sm btn-info text-white">
                                                <i class="fas fa-eye"></i> Lihat Surat yang Sudah Ada
                                            </a>
                                        </div>
                                        <div class="col-12 mt-2">
                                            <label class="form-label">Update Surat (PDF/Gambar, kosongkan jika tidak diubah)</label>
                                            <input type="file" class="form-control" name="file_surat" accept=".pdf,.jpg,.jpeg,.png">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Sarana Kunjungan</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sarana_kunjungan" value="Langsung" id="edit_saranaLangsung" onchange="toggleEditOnlineDetails()">
                                    <label class="form-check-label" for="edit_saranaLangsung">Kunjungan Langsung (PST)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="sarana_kunjungan" value="Online" id="edit_saranaOnline" onchange="toggleEditOnlineDetails()">
                                    <label class="form-check-label" for="edit_saranaOnline">Layanan Online</label>
                                </div>
                            </div>
                        </div>

                        <div id="editOnlineChannelDetails" style="display:none;" class="col-12">
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="fas fa-headset"></i> Saluran Layanan Online</h6>
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Dilayani Melalui</label>
                                            <div class="d-flex gap-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="online_channel" value="Pegawai" id="edit_channelPegawai" onchange="toggleEditPegawaiSelect()">
                                                    <label class="form-check-label" for="edit_channelPegawai">Melalui Pegawai BPS</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="online_channel" value="Kontak Admin" id="edit_channelAdmin" onchange="toggleEditPegawaiSelect()">
                                                    <label class="form-check-label" for="edit_channelAdmin">Kontak Admin BPS</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 mb-3" id="editPegawaiSelectField" style="display:none;">
                                            <label class="form-label">Pilih Pegawai</label>
                                            <select class="form-select" name="petugas_online_id" id="edit_petugas_online_id">
                                                <option value="">-- Pilih Pegawai --</option>
                                                @foreach($petugas as $p)
                                                    <option value="{{ $p->id }}">{{ $p->id }} - {{ $p->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">Keperluan</label>
                            <textarea class="form-control" name="keperluan" id="edit_keperluan" rows="3" required></textarea>
                        </div>

                        <!-- Pegawai Terlibat Section for Edit Modal -->
                        <div class="col-12">
                            <div class="card bg-info-subtle border-info-subtle">
                                <div class="card-body p-3">
                                    <h6 class="card-title text-info mb-2"><i class="fas fa-users"></i> Pegawai Terlibat</h6>
                                    <p class="small text-muted mb-2">Pegawai lain yang membantu layanan ini (pengumpul data, konsultan, dll).</p>
                                    <div id="editHandlersListContainer">
                                        <!-- Dynamic handler rows will be populated here -->
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-info w-100" onclick="addEditHandlerRow()">
                                        <i class="fas fa-plus"></i> Tambah Pegawai
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitEditVisitor()">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>


<style>
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .nav-item.active { background: rgba(255,255,255,0.2) !important; }
    
    @keyframes slideIn {
        from { transform: translateX(100px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    .service-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.2s;
    }
    
    .service-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .btn-xs {
        padding: 0.1rem 0.3rem;
        font-size: 0.75rem;
    }

    /* Photo Gallery Styles */
    .photo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 12px;
        margin-top: 10px;
    }
    .photo-item {
        position: relative;
        aspect-ratio: 4/3;
        overflow: hidden;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #eef2f7;
        background: #f8fafc;
    }
    .photo-item:hover {
        transform: scale(1.04) translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        z-index: 10;
        border-color: #3b82f6;
    }
    .photo-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        cursor: zoom-in;
        display: block;
    }
    .photo-download-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 32px;
        height: 32px;
        background: rgba(255,255,255,0.9);
        border: none;
        border-radius: 50%;
        color: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transform: translateY(-5px);
        transition: all 0.25s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        z-index: 15;
        text-decoration: none !important;
    }
    .photo-item:hover .photo-download-btn {
        opacity: 1;
        transform: translateY(0);
    }
    .photo-download-btn:hover {
        background: #3b82f6;
        color: white;
        transform: scale(1.1);
    }
</style>
@endsection

@push('scripts')
<script>
let currentServiceId = null;
let currentPermintaanDataId = null;

// Load stats on page load
loadStats();
loadAttendanceStatus();
startRealtimeUpdates();

// Show upcoming schedule modal if not on duty
@if(!$isOnDuty)
document.addEventListener('DOMContentLoaded', function() {
    // Check if modal was already shown in this session to avoid annoyance
    if (!sessionStorage.getItem('schedule_modal_shown')) {
        setTimeout(() => {
            const myModal = new bootstrap.Modal(document.getElementById('upcomingScheduleModal'));
            myModal.show();
            sessionStorage.setItem('schedule_modal_shown', 'true');
        }, 1000);
    }
});
@endif

function toggleSuratFields() {
    const permintaan = document.getElementById('permintaan');
    const suratFields = document.getElementById('suratFields');
    
    if (permintaan.checked) {
        suratFields.style.display = 'block';
    } else {
        suratFields.style.display = 'none';
        document.getElementById('nomor_surat').value = '';
        document.getElementById('tanggal_surat').value = '';
    }
}

function toggleLainnyaField() {
    const lainnya = document.getElementById('lainnya');
    const lainnyaField = document.getElementById('lainnyaField');
    const lainnyaInput = document.getElementById('jenis_layanan_lainnya');
    
    if (lainnya && lainnya.checked) {
        lainnyaField.style.display = 'block';
        lainnyaInput.required = true;
    } else {
        lainnyaField.style.display = 'none';
        lainnyaInput.required = false;
        lainnyaInput.value = '';
    }
}

function toggleOnlineDetails() {
    const saranaOnline = document.getElementById('saranaOnline');
    const details = document.getElementById('onlineChannelDetails');
    const channelPegawai = document.getElementById('channelPegawai');
    const channelAdmin = document.getElementById('channelAdmin');
    const pegawaiSelectField = document.getElementById('pegawaiSelectField');
    const pegawaiId = document.getElementById('petugas_online_id');

    if (saranaOnline && saranaOnline.checked) {
        details.style.display = 'block';
        if (channelPegawai) channelPegawai.required = true;
        if (channelAdmin) channelAdmin.required = true;
    } else {
        details.style.display = 'none';
        if (channelPegawai) {
            channelPegawai.required = false;
            channelPegawai.checked = false;
        }
        if (channelAdmin) {
            channelAdmin.required = false;
            channelAdmin.checked = false;
        }
        if (pegawaiId) {
            pegawaiId.value = '';
            pegawaiId.required = false;
        }
        if (pegawaiSelectField) pegawaiSelectField.style.display = 'none';
    }
}

function togglePegawaiSelect() {
    const channelPegawai = document.getElementById('channelPegawai');
    const selectField = document.getElementById('pegawaiSelectField');
    const pegawaiId = document.getElementById('petugas_online_id');

    if (channelPegawai && channelPegawai.checked) {
        if (selectField) selectField.style.display = 'block';
        if (pegawaiId) pegawaiId.required = true;
    } else {
        if (selectField) selectField.style.display = 'none';
        if (pegawaiId) {
            pegawaiId.required = false;
            pegawaiId.value = '';
        }
    }
}

// Handler Management for Multi-Employee Rating
const allPetugas = @json($petugas->map(fn($p) => ['id' => $p->id, 'name' => $p->name]));
let handlerCounter = 0;

function addHandlerRow() {
    handlerCounter++;
    const container = document.getElementById('handlersListContainer');
    const rowId = `handler-row-${handlerCounter}`;
    
    let options = '<option value="">-- Pilih Pegawai --</option>';
    allPetugas.forEach(p => {
        options += `<option value="${p.id}">${p.name}</option>`;
    });
    
    const rowHtml = `
        <div id="${rowId}" class="d-flex gap-2 mb-2 align-items-center">
            <select class="form-select form-select-sm flex-grow-1 handler-select" name="handler_user_id">
                ${options}
            </select>
            <select class="form-select form-select-sm" name="handler_role" style="width: 40%;">
                <option value="Membantu">Membantu</option>
                <option value="Pengumpul Data">Pengumpul Data</option>
                <option value="Konsultan">Konsultan</option>
                <option value="Verifikator">Verifikator</option>
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeHandlerRow('${rowId}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', rowHtml);
}

function removeHandlerRow(rowId) {
    document.getElementById(rowId).remove();
}

function collectHandlersData() {
    const handlers = [];
    const rows = document.querySelectorAll('#handlersListContainer .d-flex');
    rows.forEach(row => {
        const userSelect = row.querySelector('select[name="handler_user_id"]');
        const roleSelect = row.querySelector('select[name="handler_role"]');
        if (userSelect && userSelect.value) {
            handlers.push({
                user_id: parseInt(userSelect.value),
                role: roleSelect ? roleSelect.value : 'Membantu'
            });
        }
    });
    return handlers;
}

function loadUpdateHandlers(serviceId) {
    const container = document.getElementById('handlersListContainer');
    container.innerHTML = '<div class="text-muted small text-center py-2">Memuat...</div>';
    
    fetch(`/api/services/${serviceId}/handlers`)
        .then(res => res.json())
        .then(data => {
            container.innerHTML = '';
            if (data.success && data.handlers && data.handlers.length > 0) {
                data.handlers.forEach(h => {
                    addHandlerRowWithData(h.user_id, h.role);
                });
            }
        })
        .catch(err => {
            container.innerHTML = '';
            console.error('Error loading handlers:', err);
        });
}

function addHandlerRowWithData(selectedUserId, selectedRole) {
    handlerCounter++;
    const container = document.getElementById('handlersListContainer');
    const rowId = `handler-row-${handlerCounter}`;
    
    let options = '<option value="">-- Pilih Pegawai --</option>';
    allPetugas.forEach(p => {
        const selected = (p.id == selectedUserId) ? 'selected' : '';
        options += `<option value="${p.id}" ${selected}>${p.name}</option>`;
    });
    
    const roles = ['Membantu', 'Pengumpul Data', 'Konsultan', 'Verifikator'];
    let roleOptions = roles.map(r => `<option value="${r}" ${r === selectedRole ? 'selected' : ''}>${r}</option>`).join('');
    
    const rowHtml = `
        <div id="${rowId}" class="d-flex gap-2 mb-2 align-items-center">
            <select class="form-select form-select-sm flex-grow-1 handler-select" name="handler_user_id">
                ${options}
            </select>
            <select class="form-select form-select-sm" name="handler_role" style="width: 40%;">
                ${roleOptions}
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeHandlerRow('${rowId}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', rowHtml);
}

function loadStats() {
    fetch('/api/stats/petugas', {
        headers: { 'Accept': 'application/json' }
    })
        .then(res => res.json())
        .then(data => {
            document.getElementById('statVisitorsToday').textContent = data.visitors?.today || 0;
            document.getElementById('statVisitorsWeek').textContent = data.visitors?.week || 0;
            document.getElementById('statVisitorsTotal').textContent = data.visitors?.total || 0;
            document.getElementById('statRating').textContent = data.rating?.average || '0.0';
        })
        .catch(err => console.error('Stats error:', err));
}

function loadAttendanceStatus() {
    fetch('/api/absensi/status', {
        headers: { 'Accept': 'application/json' }
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;
            
            const { is_clocked_in, has_clocked_out, absensi, schedule, shift_settings } = data;
            const actionContainer = document.getElementById('attendanceActions');
            const messageEl = document.getElementById('attendanceMessage');
            
            let html = '';
            let message = '';
            
            if (has_clocked_out) {
                const clockOutTime = new Date(absensi.jam_pulang).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
                message = `Sudah absen pulang pada ${clockOutTime} WITA. Terima kasih!`;
                html = `<span class="badge bg-light text-white p-2 px-3 rounded-pill" style="background: rgba(255,255,255,0.2) !important;"><i class="fas fa-check-circle me-1"></i> Selesai Bertugas</span>`;
            } else if (is_clocked_in) {
                const clockInTime = new Date(absensi.jam_masuk).toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'});
                const statusColor = absensi.status_masuk === 'Terlambat' ? 'text-warning' : 'text-white';
                message = `Sudah absen masuk pada ${clockInTime} WITA. <span class="${statusColor} fw-bold">(${absensi.status_masuk})</span>`;
                html = `<button class="btn btn-light rounded-pill px-4 fw-bold text-danger btn-sm shadow-sm" onclick="clockOut()"><i class="fas fa-sign-out-alt me-1"></i> Selesai Bertugas</button>`;
                
                // If it's HTML, we need to use innerHTML for message
                messageEl.innerHTML = message;
            } else {
                if (schedule) {
                    const shiftStart = shift_settings['s'+schedule.shift+'_start'];
                    const shiftEnd = shift_settings['s'+schedule.shift+'_end'];
                    message = `Jadwal hari ini: Shift ${schedule.shift} (${shiftStart} - ${shiftEnd} WITA). Silakan klik tombol untuk mulai.`;
                    html = `<button class="btn btn-light rounded-pill px-4 fw-bold text-primary btn-sm shadow-sm" onclick="clockIn()"><i class="fas fa-sign-in-alt me-1"></i> Mulai Bertugas</button>`;
                } else {
                    message = "Tidak ada jadwal tugas untuk Anda hari ini.";
                    html = `<button class="btn btn-light rounded-pill px-4 fw-bold text-secondary btn-sm shadow-sm" onclick="clockIn()" title="Klik jika Anda tetap bertugas hari ini"><i class="fas fa-sign-in-alt me-1"></i> Mulai Bertugas</button>`;
                }
                messageEl.textContent = message;
            }
            
            actionContainer.innerHTML = html;
        });
}

function clockIn() {
    Swal.fire({
        title: 'Mulai Bertugas?',
        text: "Anda akan melakukan absen masuk untuk hari ini.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#667eea',
        confirmButtonText: 'Ya, Mulai',
        cancelButtonText: 'Batal',
        borderRadius: '15px'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/api/absensi/clock-in', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    loadAttendanceStatus();
                    // Optional: Refresh schedules or stats
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message,
                        icon: 'error',
                        borderRadius: '15px'
                    });
                }
            })
            .catch(err => {
                showToast('Terjadi kesalahan jaringan', 'error');
            });
        }
    });
}

function clockOut() {
    Swal.fire({
        title: 'Selesai Bertugas?',
        text: "Pastikan semua pelayanan Anda sudah diperbarui statusnya.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#764ba2',
        confirmButtonText: 'Ya, Selesai',
        cancelButtonText: 'Batal',
        borderRadius: '15px'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/api/absensi/clock-out', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    loadAttendanceStatus();
                } else {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message,
                        icon: 'error',
                        borderRadius: '15px'
                    });
                }
            })
            .catch(err => {
                showToast('Terjadi kesalahan jaringan', 'error');
            });
        }
    });
}

// Tab switching
function showTab(e, tab) {
    document.querySelectorAll('.tab-content').forEach(t => t.style.display = 'none');
    document.querySelectorAll('.sidebar .nav-item').forEach(n => n.classList.remove('active'));
    
    document.getElementById('tab-' + tab).style.display = 'block';
    if (e && e.target) {
        e.target.closest('.nav-item').classList.add('active');
    }
    
    if (tab === 'layanan') loadMyServices();
    if (tab === 'status') loadStatus();
}

// Buku Tamu form submission
document.getElementById('bukuTamuForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    
    const formData = new FormData(this);
    
    fetch('/buku-tamu', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Simpan Buku Tamu';
        
        if (data.success) {
            showToast('Buku tamu berhasil ditambahkan!', 'success');
            this.reset();
            document.getElementById('suratFields').style.display = 'none';
            document.getElementById('onlineChannelDetails').style.display = 'none';
            document.getElementById('pegawaiSelectField').style.display = 'none';
            loadStats();
            
            // Show rating link modal with flexible local URL
            if (data.rating_token || data.remote_rating_url) {
                const localRatingUrl = data.rating_token ? `${window.location.origin}/rating/${data.rating_token}` : null;
                showRatingLinkModal(localRatingUrl, data.skd_token, data.remote_rating_url, data.skd_short_url, data.remote_rating_long_url, data.skd_long_url, data.whatsapp_group_link, data.visitor_name, data.visitor_purpose, data.visitor_instansi, data.visitor_service, null, data.visitor_phone, data.visitor_email, 'Diterima');
            }
        } else {
            showToast('Error: ' + (data.message || 'Terjadi kesalahan'), 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save"></i> Simpan Buku Tamu';
        showToast('Error: ' + err.message, 'error');
    });
});

function showRatingLinkModal(ratingUrl, skdToken, remoteRatingUrl = null, skdShortUrl = null, remoteRatingLongUrl = null, skdLongUrl = null, waGroupLink = null, visitorName = null, visitorPurpose = null, visitorInstansi = null, visitorService = null, linkMonitor = null, visitorPhone = null, visitorEmail = null, status = 'Diterima') {
    const defaultSkdLongUrl = `https://script.google.com/macros/s/AKfycbx6NAMQSZTBFuda4tpddVggCK87wr0pCLUxpCarjLJYH7OvbTXJ80j_fPLBAXtXWO0/exec?token=${skdToken}`;
    const fullSkdUrl = skdShortUrl || defaultSkdLongUrl;
    const finalSkdLongUrl = skdLongUrl || defaultSkdLongUrl;
    const finalWaGroupLink = waGroupLink || '{{ \App\Models\SystemSetting::get("whatsapp_group_link", "https://chat.whatsapp.com/DPrCxwvtrX3DP6Gu84YOef") }}';
    
    // Create modal if not exists
    let modal = document.getElementById('ratingLinkModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.innerHTML = `
            <div class="modal fade" id="ratingLinkModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title"><i class="fas fa-check-circle"></i> Berhasil Disimpan</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row text-center">
                                <!-- Rating Column (Local) -->
                                <div class="col-md-4 border-end" id="ratingCol">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-star text-warning"></i> 1. Rating Instan</h6>
                                    <p class="small text-muted mb-2">Peralatan di meja FO</p>
                                    <div id="qrRatingContainer" class="mb-3 p-2 bg-white d-inline-block rounded shadow-sm border"></div>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control form-control-sm bg-light" id="ratingInput" readonly>
                                        <button class="btn btn-sm btn-primary" onclick="copyToClipboard('ratingInput')"><i class="fas fa-copy"></i></button>
                                    </div>
                                    <div class="d-grid">
                                        <a id="waRating" href="#" target="_blank" class="btn btn-sm btn-success">
                                            <i class="fab fa-whatsapp"></i> WA
                                        </a>
                                    </div>
                                </div>

                                <!-- Rating Column (Remote/GAS) -->
                                <div class="col-md-4 border-end" id="remoteRatingCol">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-home text-info"></i> 2. Rating Remote</h6>
                                    <p class="small text-muted mb-2">Bisa isi dari rumah</p>
                                    <div id="qrRemoteRatingContainer" class="mb-3 p-2 bg-white d-inline-block rounded shadow-sm border"></div>
                                    
                                    <div class="text-start mb-3">
                                        <label class="small text-muted mb-1" style="font-size: 0.7rem;">Shortlink (is.gd):</label>
                                        <div class="input-group input-group-sm mb-2">
                                            <input type="text" class="form-control form-control-sm bg-light" id="remoteRatingInput" readonly>
                                            <button class="btn btn-sm btn-primary" onclick="copyToClipboard('remoteRatingInput')"><i class="fas fa-copy"></i></button>
                                        </div>
                                        <label class="small text-muted mb-1" style="font-size: 0.7rem;">Link Asli (Panjang):</label>
                                        <div class="input-group input-group-sm mb-2">
                                            <input type="text" class="form-control form-control-sm bg-light" id="remoteRatingLongInput" readonly style="font-size: 0.65rem;">
                                            <button class="btn btn-sm btn-secondary" onclick="copyToClipboard('remoteRatingLongInput')"><i class="fas fa-copy"></i></button>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <a id="waRemoteRating" href="#" target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="fab fa-whatsapp"></i> WA Shortlink
                                        </a>
                                        <a id="waRemoteRatingFull" href="#" target="_blank" class="btn btn-sm btn-outline-dark">
                                            <i class="fab fa-whatsapp"></i> WA Full Link
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- SKD Column -->
                                <div class="col-md-4" id="skdCol">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-poll-h text-primary"></i> 3. Survei SKD</h6>
                                    <p class="small text-muted mb-2">Hanya Layanan Data</p>
                                    <div id="qrSkdContainer" class="mb-3 p-2 bg-white d-inline-block rounded shadow-sm border"></div>
                                    
                                    <div class="text-start mb-3">
                                        <label class="small text-muted mb-1" style="font-size: 0.7rem;">Shortlink (is.gd):</label>
                                        <div class="input-group input-group-sm mb-2">
                                            <input type="text" class="form-control form-control-sm bg-light" id="skdInput" readonly>
                                            <button class="btn btn-sm btn-primary" onclick="copyToClipboard('skdInput')"><i class="fas fa-copy"></i></button>
                                        </div>
                                        <label class="small text-muted mb-1" style="font-size: 0.7rem;">Link Asli (Panjang):</label>
                                        <div class="input-group input-group-sm mb-2">
                                            <input type="text" class="form-control form-control-sm bg-light" id="skdLongInput" readonly style="font-size: 0.65rem;">
                                            <button class="btn btn-sm btn-secondary" onclick="copyToClipboard('skdLongInput')"><i class="fas fa-copy"></i></button>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <a id="waSkd" href="#" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fab fa-whatsapp"></i> WA Shortlink
                                        </a>
                                        <a id="waSkdFull" href="#" target="_blank" class="btn btn-sm btn-outline-dark">
                                            <i class="fab fa-whatsapp"></i> WA Full Link
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div id="waInternalNotification" class="mt-3 p-3 bg-success-subtle rounded border border-success-subtle text-start">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold text-success mb-0"><i class="fab fa-whatsapp"></i> Koordinasi Internal</h6>
                                    <a id="waGroupJoinLink" href="#" target="_blank" class="small text-decoration-none text-success fw-bold" title="Hanya buka link bergabung grup">
                                        <i class="fas fa-users-viewfinder"></i> Link Join Grup
                                    </a>
                                </div>
                                <p class="small text-muted mb-3">Klik tombol di bawah untuk mengirim <b>template notifikasi</b> ke grup koordinasi (Anda akan diminta memilih grup di WA).</p>
                                <div class="d-grid">
                                    <a id="waInternalBtn" href="#" target="_blank" class="btn btn-success fw-bold">
                                        <i class="fab fa-whatsapp"></i> Kirim Notifikasi ke Grup WA
                                    </a>
                                </div>
                            </div>

                            <div class="alert alert-primary mt-4 mb-0 py-2 border-0 bg-primary-subtle" style="font-size: 0.8rem;" id="modalInstruction">
                                <i class="fas fa-info-circle me-1"></i> Gunakan <b>Shortlink</b> untuk WhatsApp agar lebih ringkas, atau <b>Link Asli</b> jika Shortlink bermasalah.
                            </div>
                            
                            <!-- Visitor Notification Section -->
                            <div id="visitorNotificationSection" class="mt-3 p-3 bg-warning-subtle rounded border border-warning-subtle text-start" style="display:none;">
                                <h6 class="fw-bold text-warning mb-2"><i class="fas fa-bell"></i> Notifikasi Pengunjung</h6>
                                <p class="small text-muted mb-3">Kirim konfirmasi registrasi ke pengunjung via WhatsApp atau Email.</p>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <a id="waVisitorBtn" href="#" target="_blank" class="btn btn-success w-100 fw-bold">
                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                        </a>
                                    </div>
                                    <div class="col-6">
                                        <button type="button" id="emailVisitorBtn" class="btn btn-primary w-100 fw-bold" onclick="sendVisitorEmail()">
                                            <i class="fas fa-envelope"></i> Email
                                        </button>
                                    </div>
                                </div>
                                <div id="emailStatus" class="small mt-2" style="display:none;"></div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-top-0">
                            <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Selesai</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    const ratingCol = document.getElementById('ratingCol');
    const remoteRatingCol = document.getElementById('remoteRatingCol');
    const skdCol = document.getElementById('skdCol');

    // Handle Rating Local
    if (ratingUrl) {
        const qrRating = document.getElementById('qrRatingContainer');
        qrRating.innerHTML = '';
        try {
            new QRCode(qrRating, { text: ratingUrl, width: 120, height: 120 });
        } catch(e) {
            qrRating.innerHTML = '<small class="text-danger">QR tidak tersedia<br>(URL terlalu panjang)</small>';
            console.error('QR Rating Error:', e);
        }
        document.getElementById('ratingInput').value = ratingUrl;
        document.getElementById('waRating').href = 'https://wa.me/?text=' + encodeURIComponent('Halo, mohon bantu kami meningkatkan layanan dengan memberikan penilaian petugas PST melalui link: ' + ratingUrl);
        ratingCol.style.display = 'block';
    } else {
        ratingCol.style.display = 'none';
    }

    // Handle Remote Rating - show both short and long URL clearly
    if (remoteRatingUrl || remoteRatingLongUrl) {
        const qrRemote = document.getElementById('qrRemoteRatingContainer');
        qrRemote.innerHTML = '';
        // Use shortlink for QR if available, otherwise skip QR
        const qrUrl = remoteRatingUrl; // Only use shortlink for QR, long URL is too long
        if (qrUrl) {
            try {
                new QRCode(qrRemote, { text: qrUrl, width: 120, height: 120 });
            } catch(e) {
                qrRemote.innerHTML = '<small class="text-danger">QR tidak tersedia<br>(URL terlalu panjang)</small>';
                console.error('QR Remote Rating Error:', e);
            }
        } else {
            qrRemote.innerHTML = '<small class="text-warning">QR tidak tersedia<br>(Shortlink gagal)</small>';
        }
        // Short URL field: only show shortlink (is.gd), or "Tidak tersedia" if no shortlink
        document.getElementById('remoteRatingInput').value = remoteRatingUrl || '(Shortlink tidak tersedia)';
        // Long URL field: always show the long URL
        document.getElementById('remoteRatingLongInput').value = remoteRatingLongUrl || '';
        // WA buttons: use the appropriate URL
        document.getElementById('waRemoteRating').href = remoteRatingUrl 
            ? 'https://wa.me/?text=' + encodeURIComponent('Halo, terima kasih telah berkunjung ke BPS. Anda dapat mengisi penilaian layanan kami dari rumah melalui link ini: ' + remoteRatingUrl)
            : 'https://wa.me/?text=' + encodeURIComponent('Halo, terima kasih telah berkunjung ke BPS. Anda dapat mengisi penilaian layanan kami dari rumah melalui link ini: ' + remoteRatingLongUrl);
        document.getElementById('waRemoteRatingFull').href = 'https://wa.me/?text=' + encodeURIComponent('Halo, terima kasih telah berkunjung ke BPS. Anda dapat mengisi penilaian layanan kami dari rumah melalui link ini: ' + remoteRatingLongUrl);
        remoteRatingCol.style.display = 'block';
    } else {
        remoteRatingCol.style.display = 'none';
    }

    // Handle SKD Content - show both short and long URL clearly
    if (skdToken) {
        const qrSkd = document.getElementById('qrSkdContainer');
        qrSkd.innerHTML = '';
        // Use shortlink for QR if available, otherwise skip QR
        const skdQrUrl = fullSkdUrl; // Only use shortlink for QR, long URL is too long
        if (skdQrUrl) {
            try {
                new QRCode(qrSkd, { text: skdQrUrl, width: 120, height: 120 });
            } catch(e) {
                qrSkd.innerHTML = '<small class="text-danger">QR tidak tersedia<br>(URL terlalu panjang)</small>';
                console.error('QR SKD Error:', e);
            }
        } else {
            qrSkd.innerHTML = '<small class="text-warning">QR tidak tersedia<br>(Shortlink gagal)</small>';
        }
        // Short URL field: only show shortlink (is.gd), or "Tidak tersedia" if no shortlink
        document.getElementById('skdInput').value = fullSkdUrl || '(Shortlink tidak tersedia)';
        // Long URL field: always show the long URL
        document.getElementById('skdLongInput').value = finalSkdLongUrl || '';
        // WA buttons: use the appropriate URL
        document.getElementById('waSkd').href = fullSkdUrl 
            ? 'https://wa.me/?text=' + encodeURIComponent('Halo, mohon bantu kami meningkatkan kualitas data dengan mengisi Survei SKD melalui link: ' + fullSkdUrl)
            : 'https://wa.me/?text=' + encodeURIComponent('Halo, mohon bantu kami meningkatkan kualitas data dengan mengisi Survei SKD melalui link: ' + finalSkdLongUrl);
        document.getElementById('waSkdFull').href = 'https://wa.me/?text=' + encodeURIComponent('Halo, mohon bantu kami meningkatkan kualitas data dengan mengisi Survei SKD melalui link: ' + finalSkdLongUrl);
        skdCol.style.display = 'block';
    } else {
        skdCol.style.display = 'none';
    }

    // Handle Internal WA Notification
    const waInternalBtn = document.getElementById('waInternalBtn');
    const waGroupJoinLink = document.getElementById('waGroupJoinLink');
    
    if (finalWaGroupLink) {
        document.getElementById('waInternalNotification').style.display = 'block';
        waGroupJoinLink.href = finalWaGroupLink;
        
        // Always generate message even if visitorName is technically missing (fallback to "Pengunjung")
        const name = visitorName || 'Pengunjung';
        const instansi = visitorInstansi || '-';
        const layanan = visitorService || '-';
        const purpose = visitorPurpose || '-';
        let waMessage = `🔔 *NOTIFIKASI PENGUNJUNG BARU*\n\n*Nama:* ${name}\n*Instansi:* ${instansi}\n*Layanan:* ${layanan}\n*Tujuan:* ${purpose}\n*Status:* Menunggu Pelayanan`;
        
        if (linkMonitor) {
            waMessage += `\n\nUntuk pengisian data nya bisa diakses pada link berikut:\n${linkMonitor}`;
        }

        waMessage += `\n\n_Mohon petugas terkait segera menindaklanjuti._`;
        waInternalBtn.href = `https://wa.me/?text=${encodeURIComponent(waMessage)}`;
        waInternalBtn.innerHTML = '<i class="fab fa-whatsapp"></i> Kirim Notifikasi ke Grup WA';
    } else {
        document.getElementById('waInternalNotification').style.display = 'none';
    }

    // Handle Visitor Notification Section
    const visitorNotificationSection = document.getElementById('visitorNotificationSection');
    if (visitorPhone || visitorEmail) {
        visitorNotificationSection.style.display = 'block';
        
        // Store visitor info for email sending
        window.currentVisitorData = {
            name: visitorName,
            phone: visitorPhone,
            email: visitorEmail,
            instansi: visitorInstansi,
            layanan: visitorService,
            keperluan: visitorPurpose,
            status: status
        };
        
        // WhatsApp notification to visitor
        if (visitorPhone) {
            let visitorWaMsg = `Halo ${visitorName || 'Bapak/Ibu'},\n\nTerima kasih telah mengunjungi Pelayanan Statistik Terpadu BPS.\n\n📋 *Data Kunjungan Anda:*\n*Layanan:* ${visitorService || '-'}\n*Keperluan:* ${visitorPurpose || '-'}\n*Status:* ${status}\n\nPetugas kami akan segera memproses permintaan Anda. Mohon menunggu informasi selanjutnya.\n\n_Salam,_\n_Tim PST BPS_`;
            document.getElementById('waVisitorBtn').href = `https://wa.me/${visitorPhone}?text=${encodeURIComponent(visitorWaMsg)}`;
        } else {
            document.getElementById('waVisitorBtn').classList.add('disabled');
            document.getElementById('waVisitorBtn').removeAttribute('href');
        }
        
        // Email button disabled if no email
        if (!visitorEmail) {
            document.getElementById('emailVisitorBtn').disabled = true;
            document.getElementById('emailVisitorBtn').classList.add('disabled');
        }
    } else {
        visitorNotificationSection.style.display = 'none';
    }

    var myModal = new bootstrap.Modal(document.getElementById('ratingLinkModal'));
    myModal.show();
}

function copyToClipboard(id) {
    const input = document.getElementById(id);
    input.select();
    document.execCommand('copy');
    showToast('Link berhasil dicopy!', 'success');
}

// Phone number formatting function
function formatPhoneNumber(input) {
    // Remove all non-digit characters
    let value = input.value.replace(/\D/g, '');
    
    // Convert +62 or 62 prefix if present
    if (value.startsWith('62')) {
        // Already in correct format
    } else if (value.startsWith('0')) {
        // Convert 08xx to 628xx
        value = '62' + value.substring(1);
    }
    
    // Limit to 15 digits
    value = value.substring(0, 15);
    
    input.value = value;
}

// Send Email notification to visitor
function sendVisitorEmail() {
    const data = window.currentVisitorData;
    if (!data || !data.email) {
        showToast('Email pengunjung tidak tersedia', 'error');
        return;
    }
    
    const btn = document.getElementById('emailVisitorBtn');
    const statusDiv = document.getElementById('emailStatus');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
    statusDiv.style.display = 'none';
    
    fetch('/api/send-visitor-notification', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            email: data.email,
            name: data.name,
            instansi: data.instansi,
            layanan: data.layanan,
            keperluan: data.keperluan,
            status: data.status
        })
    })
    .then(res => res.json())
    .then(response => {
        btn.innerHTML = '<i class="fas fa-envelope"></i> Email';
        if (response.success) {
            btn.disabled = true;
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-secondary');
            btn.innerHTML = '<i class="fas fa-check"></i> Terkirim';
            statusDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> Email konfirmasi berhasil terkirim!</span>';
            statusDiv.style.display = 'block';
            showToast('Email konfirmasi berhasil terkirim!', 'success');
        } else {
            btn.disabled = false;
            statusDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> ' + (response.message || 'Gagal mengirim email') + '</span>';
            statusDiv.style.display = 'block';
            showToast('Gagal mengirim email: ' + (response.message || 'Unknown error'), 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-envelope"></i> Email';
        statusDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> Error: ' + err.message + '</span>';
        statusDiv.style.display = 'block';
        showToast('Error: ' + err.message, 'error');
    });
}

// Visitor Edit Logic
let allMyServices = []; // Store locally to fill edit form faster

function loadMyServices() {
    fetch('/api/my-services')
        .then(res => res.json())
        .then(services => {
            allMyServices = services; // Cache for editing
            if (services.length === 0) {
                document.getElementById('layananList').innerHTML = `
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Belum ada layanan</h5>
                        <p class="text-muted">Data permintaan yang Anda tangani akan muncul di sini</p>
                    </div>`;
                return;
            }
            
            const html = services.map(s => `
                <div class="service-card" id="service-card-${s.id}">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0 text-truncate"><i class="fas fa-user-circle text-primary me-1"></i> ${s.nama_pengunjung}</h6>
                                <span class="badge bg-${getStatusColor(s.status_layanan)} small">${s.status_layanan}</span>
                            </div>
                            <div class="small fw-bold text-muted mb-1">
                                <i class="fas fa-calendar-day me-1"></i> ${s.tanggal_kunjungan || '-'}
                            </div>
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                <span class="badge bg-light text-muted border small"><i class="fas fa-tag"></i> ${s.jenis_layanan}</span>
                                ${s.sarana_kunjungan === 'Online' ? `
                                    <span class="badge bg-light text-dark border small"><i class="fas fa-globe"></i> ${s.online_channel}</span>
                                ` : ''}
                                ${s.status_layanan === 'Selesai' && s.skd_token ? (
                                    s.skd_filled ? 
                                    '<span class="badge bg-success-subtle text-success border-success-subtle small"><i class="fas fa-check-circle"></i> SKD</span>' : 
                                    `<span class="badge bg-warning-subtle text-warning border-warning-subtle small" id="skd-badge-${s.skd_token}"><i class="fas fa-clock"></i> SKD</span>`
                                ) : ''}
                                ${s.rated ? '<span class="badge bg-info-subtle text-info border-info-subtle small"><i class="fas fa-star"></i> Rated</span>' : ''}
                                ${s.jenis_layanan && s.jenis_layanan.includes('Permintaan Data') ? (
                                    s.surat_lengkap ? 
                                    '<span class="badge bg-success-subtle text-success border-success-subtle small"><i class="fas fa-file-check"></i> Surat Lengkap</span>' : 
                                    '<span class="badge bg-warning-subtle text-warning border-warning-subtle small"><i class="fas fa-file-alt"></i> Surat Belum Lengkap</span>'
                                ) : ''}
                            </div>
                            <p class="mb-1 text-muted small text-truncate"><i class="fas fa-building me-1"></i> ${s.instansi}</p>
                            <small class="text-muted d-block text-truncate"><i class="fas fa-file-invoice me-1"></i> No. Surat: ${s.nomor_surat || '-'}</small>
                        </div>
                        
                        <!-- Actions -->
                        <div class="actions-container">
                            <!-- Desktop Actions -->
                            <div class="d-none d-lg-flex flex-column gap-1">
                                ${s.link_monitor ? `
                                    <a href="${s.link_monitor}" target="_blank" class="btn btn-xs btn-success" title="Buka Link Monitoring">
                                        <i class="fas fa-external-link-alt"></i> Buka
                                    </a>
                                ` : ''}
                                <button class="btn btn-xs btn-outline-dark" onclick="openLinkMonitorModal(${s.id}, '${s.link_monitor || ''}')">
                                    <i class="fas fa-link"></i> ${s.link_monitor ? 'Edit Link' : 'Link Monitor'}
                                </button>
                                <button class="btn btn-xs btn-outline-dark" onclick="openEditVisitorModal(${s.id})">
                                    <i class="fas fa-user-edit"></i> Edit Data
                                </button>
                                ${s.status_layanan !== 'Selesai' ? `
                                    <button class="btn btn-xs btn-primary" onclick="openUpdateModal(${s.id}, '${s.nama_pengunjung}', '${s.nomor_surat || '-'}', '${s.status_layanan}', '${s.jenis_layanan}', ${s.permintaan_data_id || 'null'})">
                                        <i class="fas fa-edit"></i> Update
                                    </button>
                                ` : ''}
                                ${s.file_surat ? `
                                    <button class="btn btn-xs btn-outline-info" onclick="previewDocument('${s.file_surat}')">
                                        <i class="fas fa-eye"></i> Preview
                                    </button>
                                ` : ''}
                                ${s.rating_token && !s.rated ? `
                                    <button class="btn btn-xs btn-outline-warning" onclick="showRatingLinkModal('${window.location.origin}/rating/${s.rating_token}', '${s.skd_token || ''}', '${s.remote_rating_url || ''}', '${s.skd_short_url || ''}', '${s.remote_rating_long_url || ''}', '${s.skd_long_url || ''}', null, '${(s.nama_pengunjung || "Pengunjung").replace(/'/g, "\\'")}', '${(s.keperluan || "-").replace(/'/g, "\\'").replace(/\n/g, " ")}', '${(s.instansi || "-").replace(/'/g, "\\'")}', '${(s.jenis_layanan || "-").replace(/'/g, "\\'")}', '${s.link_monitor || ''}', '${s.no_hp || ''}', '${s.email || ''}', '${s.status_layanan}')">
                                        <i class="fas fa-star"></i> Rating
                                    </button>
                                ` : ''}
                                ${s.skd_token ? `
                                    <button class="btn btn-xs btn-outline-success" onclick="showRatingLinkModal(null, '${s.skd_token}', null, '${s.skd_short_url || ''}', null, '${s.skd_long_url || ''}', null, '${(s.nama_pengunjung || "Pengunjung").replace(/'/g, "\\'")}', '${(s.keperluan || "-").replace(/'/g, "\\'").replace(/\n/g, " ")}', '${(s.instansi || "-").replace(/'/g, "\\'")}', '${(s.jenis_layanan || "-").replace(/'/g, "\\'")}', '${s.link_monitor || ''}', '${s.no_hp || ''}', '${s.email || ''}', '${s.status_layanan}')">
                                        <i class="fas fa-link"></i> SKD
                                    </button>
                                ` : ''}
                            </div>

                            <!-- Mobile Actions (Dropdown) -->
                            <div class="d-lg-none dropdown mobile-action-dropdown">
                                <button class="btn btn-sm btn-outline-primary rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 35px; height: 35px; padding: 0;">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    ${s.link_monitor ? `
                                        <li><a class="dropdown-item text-success" href="${s.link_monitor}" target="_blank"><i class="fas fa-external-link-alt"></i> Buka Link Monitor</a></li>
                                    ` : ''}
                                    <li><button class="dropdown-item" onclick="openLinkMonitorModal(${s.id}, '${s.link_monitor || ''}')"><i class="fas fa-link"></i> Link Monitor</button></li>
                                    <li><button class="dropdown-item" onclick="openEditVisitorModal(${s.id})"><i class="fas fa-user-edit"></i> Edit Data</button></li>
                                    ${s.status_layanan !== 'Selesai' ? `
                                        <li><button class="dropdown-item text-primary fw-bold" onclick="openUpdateModal(${s.id}, '${s.nama_pengunjung}', '${s.nomor_surat || '-'}', '${s.status_layanan}', '${s.jenis_layanan}', ${s.permintaan_data_id || 'null'})"><i class="fas fa-edit"></i> Update Status</button></li>
                                    ` : ''}
                                    ${s.file_surat ? `
                                        <li><button class="dropdown-item" onclick="previewDocument('${s.file_surat}')"><i class="fas fa-eye"></i> Preview Surat</button></li>
                                    ` : ''}
                                    ${s.rating_token && !s.rated ? `
                                        <li><button class="dropdown-item text-warning" onclick="showRatingLinkModal('${window.location.origin}/rating/${s.rating_token}', '${s.skd_token || ''}', '${s.remote_rating_url || ''}', '${s.skd_short_url || ''}', '${s.remote_rating_long_url || ''}', '${s.skd_long_url || ''}', null, '${(s.nama_pengunjung || "Pengunjung").replace(/'/g, "\\'")}', '${(s.keperluan || "-").replace(/'/g, "\\'").replace(/\n/g, " ")}', '${(s.instansi || "-").replace(/'/g, "\\'")}', '${(s.jenis_layanan || "-").replace(/'/g, "\\'")}', '${s.link_monitor || ''}', '${s.no_hp || ''}', '${s.email || ''}', '${s.status_layanan}')"><i class="fas fa-star"></i> Kirim Rating WA</button></li>
                                    ` : ''}
                                    ${s.skd_token ? `
                                        <li><button class="dropdown-item text-success" onclick="showRatingLinkModal(null, '${s.skd_token}', null, '${s.skd_short_url || ''}', null, '${s.skd_long_url || ''}', null, '${(s.nama_pengunjung || "Pengunjung").replace(/'/g, "\\'")}', '${(s.keperluan || "-").replace(/'/g, "\\'").replace(/\n/g, " ")}', '${(s.instansi || "-").replace(/'/g, "\\'")}', '${(s.jenis_layanan || "-").replace(/'/g, "\\'")}', '${s.link_monitor || ''}', '${s.no_hp || ''}', '${s.email || ''}', '${s.status_layanan}')"><i class="fas fa-link"></i> Kirim Link SKD WA</button></li>
                                    ` : ''}
                                    ${s.status_layanan === 'Selesai' && s.skd_token && !s.skd_filled ? `
                                        <li><button class="dropdown-item" onclick="checkSkdStatus('${s.skd_token}', true)"><i class="fas fa-sync-alt"></i> Refresh Status SKD</button></li>
                                    ` : ''}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
            document.getElementById('layananList').innerHTML = html;
            
            // Auto check for pending SKDs
            services.forEach(s => {
                if (!s.skd_filled && s.skd_token) {
                    checkSkdStatus(s.skd_token);
                }
            });
        })
        .catch(err => {
            document.getElementById('layananList').innerHTML = `<p class="text-danger">Error: ${err.message}</p>`;
        });
}

function openEditVisitorModal(id) {
    const s = allMyServices.find(item => item.id == id);
    if (!s) {
        console.error('Service not found for ID:', id);
        return;
    }

    console.log('Opening edit modal for service:', s);

    document.getElementById('edit_id').value = s.id;
    document.getElementById('edit_nama').value = s.nama_pengunjung || '';
    document.getElementById('edit_instansi').value = s.instansi || '';
    document.getElementById('edit_no_hp').value = s.no_hp || '';
    document.getElementById('edit_email').value = s.email || '';
    document.getElementById('edit_keperluan').value = s.keperluan || '';
    
    // Autofill Nomor Surat & Tanggal Surat
    const nomorSurat = (s.nomor_surat && s.nomor_surat !== '-') ? s.nomor_surat : '';
    document.getElementById('edit_nomor_surat').value = nomorSurat;
    
    if (s.tanggal_surat) {
        const datePart = s.tanggal_surat.split(' ')[0];
        document.getElementById('edit_tanggal_surat').value = datePart;
    } else {
        document.getElementById('edit_tanggal_surat').value = '';
    }

    // Existing File Preview
    const previewContainer = document.getElementById('existingFileSuratContainer');
    const previewLink = document.getElementById('edit_preview_file_surat');
    if (s.file_surat) {
        previewLink.href = s.file_surat;
        previewContainer.style.display = 'block';
    } else {
        previewContainer.style.display = 'none';
    }

    // Checkboxes for Jenis Layanan
    const jenisText = s.jenis_layanan || '';
    const jenis = jenisText.split(', ');
    document.getElementById('edit_konsultasi').checked = jenis.includes('Konsultasi');
    document.getElementById('edit_permintaan').checked = jenis.includes('Permintaan Data');
    document.getElementById('edit_rekomendasi').checked = jenis.includes('Rekomendasi Statistik');
    document.getElementById('edit_perpustakaan').checked = jenis.includes('Perpustakaan');
    document.getElementById('edit_pengaduan').checked = jenis.includes('Pengaduan');
    
    // Check for Lainnya (any value that doesn't match standard types)
    const standardTypes = ['Konsultasi', 'Permintaan Data', 'Rekomendasi Statistik', 'Perpustakaan', 'Pengaduan', 'Lainnya'];
    const hasLainnya = jenis.includes('Lainnya') || jenis.some(j => !standardTypes.includes(j) && j.trim() !== '');
    document.getElementById('edit_lainnya').checked = hasLainnya;
    
    // If there's a custom type (non-standard), put it in the Lainnya input
    const customTypes = jenis.filter(j => !standardTypes.includes(j) && j.trim() !== '');
    if (customTypes.length > 0) {
        document.getElementById('edit_jenis_layanan_lainnya').value = customTypes.join(', ');
    } else {
        document.getElementById('edit_jenis_layanan_lainnya').value = s.jenis_layanan_lainnya || '';
    }
    toggleEditLainnyaField();

    // Radios for Sarana
    if (s.sarana_kunjungan === 'Online') {
        document.getElementById('edit_saranaOnline').checked = true;
    } else {
        document.getElementById('edit_saranaLangsung').checked = true;
    }

    // Radios for Channel
    if (s.online_channel === 'Pegawai') {
        document.getElementById('edit_channelPegawai').checked = true;
    } else if (s.online_channel === 'Kontak Admin') {
        document.getElementById('edit_channelAdmin').checked = true;
    } else {
        document.getElementById('edit_channelPegawai').checked = false;
        document.getElementById('edit_channelAdmin').checked = false;
    }

    document.getElementById('edit_petugas_online_id').value = s.petugas_online_id || '';

    toggleEditSuratFields();
    toggleEditOnlineDetails();
    
    // Load existing handlers for this service
    loadEditHandlers(s.id);

    new bootstrap.Modal(document.getElementById('editVisitorModal')).show();
}

function toggleEditSuratFields() {
    const isChecked = document.getElementById('edit_permintaan').checked;
    document.getElementById('editSuratFields').style.display = isChecked ? 'block' : 'none';
}

function toggleEditOnlineDetails() {
    const isOnline = document.getElementById('edit_saranaOnline').checked;
    document.getElementById('editOnlineChannelDetails').style.display = isOnline ? 'block' : 'none';
    toggleEditPegawaiSelect();
}

function toggleEditPegawaiSelect() {
    const isPegawai = document.getElementById('edit_channelPegawai').checked;
    document.getElementById('editPegawaiSelectField').style.display = isPegawai ? 'block' : 'none';
}

function toggleEditLainnyaField() {
    const lainnya = document.getElementById('edit_lainnya');
    const lainnyaField = document.getElementById('editLainnyaField');
    const lainnyaInput = document.getElementById('edit_jenis_layanan_lainnya');
    
    if (lainnya && lainnya.checked) {
        lainnyaField.style.display = 'block';
        lainnyaInput.required = true;
    } else {
        lainnyaField.style.display = 'none';
        lainnyaInput.required = false;
        lainnyaInput.value = '';
    }
}

// Edit Modal Handler Functions
let editHandlerCounter = 0;

function loadEditHandlers(serviceId) {
    const container = document.getElementById('editHandlersListContainer');
    container.innerHTML = '<div class="text-muted small text-center py-2">Memuat...</div>';
    
    fetch(`/api/services/${serviceId}/handlers`)
        .then(res => res.json())
        .then(data => {
            container.innerHTML = '';
            if (data.success && data.handlers && data.handlers.length > 0) {
                data.handlers.forEach(h => {
                    addEditHandlerRow(h.user_id, h.role);
                });
            }
        })
        .catch(err => {
            container.innerHTML = '';
            console.error('Error loading handlers:', err);
        });
}

function addEditHandlerRow(selectedUserId = '', selectedRole = 'Membantu') {
    editHandlerCounter++;
    const container = document.getElementById('editHandlersListContainer');
    const rowId = `edit-handler-row-${editHandlerCounter}`;
    
    let options = '<option value="">-- Pilih Pegawai --</option>';
    allPetugas.forEach(p => {
        const selected = (p.id == selectedUserId) ? 'selected' : '';
        options += `<option value="${p.id}" ${selected}>${p.name}</option>`;
    });
    
    const roles = ['Membantu', 'Pengumpul Data', 'Konsultan', 'Verifikator'];
    let roleOptions = roles.map(r => `<option value="${r}" ${r === selectedRole ? 'selected' : ''}>${r}</option>`).join('');
    
    const rowHtml = `
        <div id="${rowId}" class="d-flex gap-2 mb-2 align-items-center">
            <select class="form-select form-select-sm flex-grow-1 edit-handler-select" name="edit_handler_user_id">
                ${options}
            </select>
            <select class="form-select form-select-sm" name="edit_handler_role" style="width: 40%;">
                ${roleOptions}
            </select>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeEditHandlerRow('${rowId}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', rowHtml);
}

function removeEditHandlerRow(rowId) {
    document.getElementById(rowId).remove();
}

function collectEditHandlersData() {
    const handlers = [];
    const rows = document.querySelectorAll('#editHandlersListContainer .d-flex');
    rows.forEach(row => {
        const userSelect = row.querySelector('select[name="edit_handler_user_id"]');
        const roleSelect = row.querySelector('select[name="edit_handler_role"]');
        if (userSelect && userSelect.value) {
            handlers.push({
                user_id: parseInt(userSelect.value),
                role: roleSelect ? roleSelect.value : 'Membantu'
            });
        }
    });
    return handlers;
}

function submitEditVisitor() {
    const id = document.getElementById('edit_id').value;
    const form = document.getElementById('editVisitorForm');
    const formData = new FormData(form);
    formData.append('_method', 'PUT');
    
    // Collect and append handlers data
    const handlers = collectEditHandlersData();
    if (handlers.length > 0) {
        formData.append('handlers', JSON.stringify(handlers));
    }

    Swal.fire({
        title: 'Simpan Perubahan?',
        text: "Data pengunjung akan diperbarui di sistem.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Simpan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch(`/api/services/${id}/visitor`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                const text = await res.text();
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Response was not JSON:', text);
                    throw new Error('Server returned HTML error. Check Console.');
                }
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    bootstrap.Modal.getInstance(document.getElementById('editVisitorModal')).hide();
                    loadMyServices();
                    loadStatus();
                } else {
                    Swal.fire('Error!', data.message || 'Gagal menyimpan perubahan', 'error');
                }
            })
            .catch(err => {
                Swal.fire('Error!', err.message, 'error');
            });
        }
    });
}

function openLinkMonitorModal(id, currentLink) {
    document.getElementById('linkMonitorBtId').value = id;
    document.getElementById('modalLinkMonitor').value = currentLink;
    new bootstrap.Modal(document.getElementById('linkMonitorModal')).show();
}

function saveLinkMonitor() {
    const id = document.getElementById('linkMonitorBtId').value;
    const link = document.getElementById('modalLinkMonitor').value;
    
    // Validation: simple URL check if not empty
    if (link && !link.startsWith('http')) {
        showToast('Link harus diawali dengan http:// atau https://', 'error');
        return;
    }

    fetch(`/api/services/${id}/monitor-link`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ link_monitor: link })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Link monitoring berhasil disimpan!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('linkMonitorModal')).hide();
            loadMyServices();
        } else {
            showToast('Error: ' + data.message, 'error');
        }
    })
    .catch(err => showToast('Error: ' + err.message, 'error'));
}

function checkSkdStatus(token, manual = false) {
    const gasUrl = `https://script.google.com/macros/s/AKfycbx6NAMQSZTBFuda4tpddVggCK87wr0pCLUxpCarjLJYH7OvbTXJ80j_fPLBAXtXWO0/exec?check_status=${token}`;
    
    if (manual) showToast('Mengecek status SKD...', 'info');

    fetch(gasUrl)
        .then(res => res.json())
        .then(data => {
            if (data.filled) {
                // Update local database
                updateLocalSkdStatus(token);
            } else if (manual) {
                showToast('SKD belum diisi oleh konsumen.', 'warning');
            }
        })
        .catch(err => {
            console.error('GAS Polling Error:', err);
            if (manual) showToast('Gagal terhubung ke server Google.', 'error');
        });
}

function updateLocalSkdStatus(token) {
    fetch('/api/skd/mark-as-filled', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ token: token })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('SKD Berhasil Diverifikasi!', 'success');
            loadMyServices(); // Refresh list to show green badge
        }
    })
    .catch(err => console.error('Local update error:', err));
}

let currentServiceType = '';

function openUpdateModal(id, pengunjung, noSurat, status, jenisLayanan, permintaanDataId) {
    currentServiceId = id;
    currentPermintaanDataId = permintaanDataId;
    currentServiceType = jenisLayanan || '';
    document.getElementById('modalPengunjung').textContent = pengunjung;
    document.getElementById('modalNoSurat').textContent = noSurat;
    document.getElementById('modalStatus').value = status;
    document.getElementById('modalCatatan').value = '';
    
    // Reset report fields
    document.getElementById('modalTopik').value = '';
    document.getElementById('modalRingkasan').value = '';
    document.getElementById('modalTags').value = '';
    document.getElementById('modalFotoBukti').value = '';
    document.getElementById('modalSuratBalasan').value = '';
    document.getElementById('modalFeedback').value = 'Puas';
    
    // Reset handlers and load existing ones
    document.getElementById('handlersListContainer').innerHTML = '';
    loadUpdateHandlers(id);
    
    toggleReportFields();
    
    // Check if reply already exists
    const isDataRequest = currentServiceType.includes('Permintaan Data');
    if (isDataRequest) {
        document.getElementById('replyLetterPreview').style.display = 'none';
        document.getElementById('replyLetterDraft').style.display = 'none';
        document.getElementById('btnShowReplyForm').style.display = 'block';

        if (permintaanDataId && permintaanDataId !== 'null') {
            fetch(`/api/replies/${permintaanDataId}`)
                .then(res => res.json())
            .then(data => {
                if (data.success && data.reply) {
                    document.getElementById('btnShowReplyForm').style.display = 'none';
                    document.getElementById('replyLetterPreview').style.display = 'block';
                    document.getElementById('previewReplyNomor').textContent = data.reply.nomor_surat;
                    document.getElementById('previewReplyTujuan').textContent = 'Penerima: ' + data.reply.tujuan;
                    
                    // Also populate the fields in the draft form (even if hidden)
                    // This is crucial for generateWordDocument to work without re-generating
                    document.getElementById('modalReplyNomor').value = data.reply.nomor_surat;
                    document.getElementById('modalReplyTujuan').value = data.reply.tujuan;
                    document.getElementById('modalReplyPerihal').value = data.reply.perihal || '';
                    document.getElementById('modalReplyUrut').value = data.reply.nomor_urut || '';
                    document.getElementById('modalReplyKode').value = data.reply.kode_surat || '';
                    document.getElementById('modalReplyTanggal').value = data.reply.tanggal_surat || '';
                }
            })
            .catch(err => console.log('No reply found or error:', err));
        }
    }

    var modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
}

function toggleReportFields() {
    const statusField = document.getElementById('modalStatus');
    const status = statusField ? statusField.value : '';
    const reportFields = document.getElementById('reportFields');
    const commonReports = document.getElementById('commonReportFields');
    const permintaanDataFields = document.getElementById('permintaanDataFields');
    
    const statusVal = status.toLowerCase();
    const isSelesai = (statusVal === 'selesai');
    const isDiproses = (statusVal === 'diproses');
    const currentType = (currentServiceType || '').toLowerCase();
    const isConsultationOrComplaint = currentType.includes('konsultasi') || currentType.includes('pengaduan') || currentType.includes('rekomendasi statistik');
    const isDataRequest = currentType.includes('permintaan data');
    
    // Safety check for elements
    if (!reportFields || !permintaanDataFields) return;

    // reportFields MUST be visible if it's Selesai OR if it's a Data Request in Diproses status
    // (because permintaanDataFields is nested inside reportFields)
    const shouldShowReportSection = isSelesai || (isDiproses && isDataRequest);
    reportFields.style.display = shouldShowReportSection ? 'block' : 'none';
    
    // Summary and Photo are only for Selesai
    if (commonReports) commonReports.style.display = (isSelesai && isConsultationOrComplaint) ? 'block' : 'none';
    
    // For Data Requests: show tags and reply letter if Selesai OR Diproses
    permintaanDataFields.style.display = (isDataRequest && (isSelesai || isDiproses)) ? 'block' : 'none';
}

// Add event listener for status change in modal
document.getElementById('modalStatus').addEventListener('change', toggleReportFields);

function submitUpdateStatus(event) {
    if (!currentServiceId) return;
    
    const status = document.getElementById('modalStatus').value;
    const formData = new FormData();
    formData.append('_method', 'PUT'); // For Laravel spoofing because we use FormData
    formData.append('status_layanan', status);
    formData.append('catatan', document.getElementById('modalCatatan').value);
    
    if (status === 'Selesai') {
        const isConsultationOrComplaint = currentServiceType.includes('Konsultasi') || currentServiceType.includes('Pengaduan') || currentServiceType.includes('Rekomendasi Statistik');
        const isDataRequest = currentServiceType.includes('Permintaan Data');

        if (isConsultationOrComplaint) {
            const topik = document.getElementById('modalTopik').value;
            const ringkasan = document.getElementById('modalRingkasan').value;
            
            if (!topik || !ringkasan) {
                showToast('Topik dan Ringkasan wajib diisi!', 'error');
                return;
            }
            
            // Validate at least 1 photo is uploaded
            const photos = document.getElementById('modalFotoBukti').files;
            if (photos.length === 0) {
                showToast('Wajib upload minimal 1 foto bukti pelayanan!', 'error');
                return;
            }
            
            formData.append('topik', topik);
            formData.append('ringkasan', ringkasan);
            formData.append('feedback_final', document.getElementById('modalFeedback').value);

            // photos variable already declared above for validation
            for (let i = 0; i < photos.length; i++) {
                formData.append('foto_bukti[]', photos[i]);
            }
        }
        
        if (isDataRequest) {
            formData.append('tags', document.getElementById('modalTags').value);
            
            const balasan = document.getElementById('modalSuratBalasan').files[0];
            if (balasan) formData.append('surat_balasan', balasan);
        }
    }
    
    // Collect handlers data
    const handlers = collectHandlersData();
    if (handlers.length > 0) {
        formData.append('handlers', JSON.stringify(handlers));
    }
    
    const btn = event.target;
    const oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

    fetch('/api/services/' + currentServiceId, {
        method: 'POST', // Use POST with _method PUT for FormData
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = oldHtml;
        
        if (data.success) {
            // IF there is a reply letter draft, SAVE IT AS WELL
            if (document.getElementById('replyLetterDraft').style.display === 'block') {
                saveReplyLetter();
            }

            showToast('Status berhasil diupdate!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('updateStatusModal')).hide();
            loadMyServices();
            
            // Offer notification for any status update
            showRatingLinkModal(
                null, 
                data.skd_token, 
                data.remote_rating_url || null, 
                null, 
                null, 
                null, 
                data.whatsapp_group_link, 
                data.visitor_name, 
                data.visitor_purpose, 
                data.visitor_instansi, 
                data.visitor_service, 
                data.link_monitor || null,
                data.visitor_phone,
                data.visitor_email,
                data.status
            );
        } else {
            showToast('Error: ' + (data.message || 'Gagal update'), 'error');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = oldHtml;
        showToast('Error: ' + err.message, 'error');
    });
}

function saveReplyLetter() {
    if (!currentPermintaanDataId) {
        showToast('ID Permintaan Data tidak ditemukan', 'error');
        return;
    }
    const data = {
        permintaan_data_id: currentPermintaanDataId,
        nomor_surat: document.getElementById('modalReplyNomor').value,
        nomor_urut: document.getElementById('modalReplyUrut').value,
        tujuan: document.getElementById('modalReplyTujuan').value,
        perihal: document.getElementById('modalReplyPerihal').value,
        tanggal_surat: document.getElementById('modalReplyTanggal').value,
        kode_surat: document.getElementById('modalReplyKode').value,
        catatan: document.getElementById('modalCatatan').value
    };

    const btn = event.target;
    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    btn.disabled = true;

    fetch('/api/replies', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        btn.innerHTML = oldHtml;
        btn.disabled = false;
        
        if (result.success) {
            showToast('Nomor surat berhasil disimpan!', 'success');
            document.getElementById('replyLetterDraft').style.display = 'none';
            document.getElementById('replyLetterPreview').style.display = 'block';
            document.getElementById('previewReplyNomor').textContent = data.nomor_surat;
            document.getElementById('previewReplyTujuan').textContent = 'Penerima: ' + data.tujuan;
        } else {
            showToast('Gagal menyimpan: ' + (result.message || 'Error'), 'error');
        }
    })
    .catch(err => {
        btn.innerHTML = oldHtml;
        btn.disabled = false;
        showToast('Error: ' + err.message, 'error');
    });
}

function loadStatus() {
    fetch('/api/services', {
        headers: { 'Accept': 'application/json' }
    })
        .then(res => res.json())
        .then(services => {
            // Count stats
            const stats = { diterima: 0, diproses: 0, siap: 0, selesai: 0 };
            services.forEach(s => {
                if (s.status_layanan === 'Diterima') stats.diterima++;
                else if (s.status_layanan === 'Diproses') stats.diproses++;
                else if (s.status_layanan === 'Siap Diambil') stats.siap++;
                else if (s.status_layanan === 'Selesai') stats.selesai++;
            });
            
            document.getElementById('statusDiterima').textContent = stats.diterima;
            document.getElementById('statusDiproses').textContent = stats.diproses;
            document.getElementById('statusSiap').textContent = stats.siap;
            document.getElementById('statusSelesai').textContent = stats.selesai;
            
            if (services.length === 0) {
                document.getElementById('statusTableBody').innerHTML = '<tr><td colspan="6" class="text-center text-muted">Tidak ada data permintaan</td></tr>';
                return;
            }
            
            const html = services.map(s => `
                <tr>
                    <td>
                        <strong>${s.nama_pengunjung || '-'}</strong>
                        <div class="small text-primary fw-bold mt-1 d-md-none">
                            <i class="fas fa-calendar-alt"></i> ${s.tanggal_kunjungan || '-'}
                        </div>
                        <div class="small text-muted d-md-none">
                            <span class="badge bg-light text-dark border mt-1">${s.nama_petugas || 'No Petugas'}</span>
                        </div>
                    </td>
                    <td class="d-none d-md-table-cell">${s.instansi || '-'}</td>
                    <td class="d-none d-lg-table-cell"><small>${s.jenis_layanan || '-'}</small></td>
                    <td class="d-none d-xl-table-cell">${s.nomor_surat || '-'}</td>
                    <td class="d-none d-md-table-cell">${s.nama_petugas || '-'}</td>
                    <td><span class="badge bg-${getStatusColor(s.status_layanan)}">${s.status_layanan}</span></td>
                    <td class="d-none d-md-table-cell">
                        <div class="d-flex flex-column gap-1">
                            <span class="badge bg-light text-dark border">${s.tanggal_update || '-'}</span>
                            ${s.rated ? '<span class="badge bg-success-subtle text-success border-success-subtle"><i class="fas fa-star"></i> Rated</span>' : ''}
                            ${s.skd_token ? (
                                s.skd_filled ? 
                                '<span class="badge bg-success-subtle text-success border-success-subtle"><i class="fas fa-check-circle"></i> SKD Diisi</span>' : 
                                '<span class="badge bg-warning-subtle text-warning border-warning-subtle"><i class="fas fa-hourglass-half"></i> Menunggu SKD</span>'
                            ) : ''}
                        </div>
                    </td>
                    <td>
                        <div class="btn-group">
                            ${s.rating_token && !s.rated ? `
                                <button class="btn btn-xs btn-outline-warning" onclick="showRatingLinkModal('${window.location.origin}/rating/${s.rating_token}', '${s.skd_token || ''}', '${s.remote_rating_url || ''}', null, null, null, null, '${(s.nama_pengunjung || "Pengunjung").replace(/'/g, "\\'")}', '${(s.keperluan || "-").replace(/'/g, "\\'").replace(/\n/g, " ")}', '${(s.instansi || "-").replace(/'/g, "\\'")}', '${(s.jenis_layanan || "-").replace(/'/g, "\\'")}', '${s.link_monitor || ''}', '${s.no_hp || ''}', '${s.email || ''}', '${s.status_layanan}')" title="Lihat Link Rating">
                                    <i class="fas fa-star"></i>
                                </button>
                            ` : ''}
                            ${s.skd_token ? `
                                <button class="btn btn-xs btn-outline-success" onclick="showRatingLinkModal(null, '${s.skd_token}', null, null, null, null, null, '${(s.nama_pengunjung || "Pengunjung").replace(/'/g, "\\'")}', '${(s.keperluan || "-").replace(/'/g, "\\'").replace(/\n/g, " ")}', '${(s.instansi || "-").replace(/'/g, "\\'")}', '${(s.jenis_layanan || "-").replace(/'/g, "\\'")}', '${s.link_monitor || ''}', '${s.no_hp || ''}', '${s.email || ''}', '${s.status_layanan}')" title="Lihat Link SKD">
                                    <i class="fas fa-link"></i>
                                </button>
                            ` : ''}
                            ${s.laporan ? `
                                <button class="btn btn-xs btn-outline-primary" onclick="viewReport('${encodeURIComponent(JSON.stringify(s.laporan))}')" title="Lihat Laporan">
                                    <i class="fas fa-file-invoice"></i>
                                </button>
                            ` : ''}
                            ${s.link_monitor ? `
                                <a href="${s.link_monitor}" target="_blank" class="btn btn-xs btn-outline-info" title="Buka Link Monitoring">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `).join('');
            document.getElementById('statusTableBody').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('statusTableBody').innerHTML = `<tr><td colspan="6" class="text-danger">Error: ${err.message}</td></tr>`;
        });
}

function viewReport(jsonStr) {
    const laporan = JSON.parse(decodeURIComponent(jsonStr));
    const content = document.getElementById('reportDetailContent');
    
    let photosHtml = '';
    if (laporan.foto_bukti && laporan.foto_bukti.length > 0) {
        photosHtml = `
            <div class="mb-4">
                <label class="fw-bold text-muted mb-2"><i class="fas fa-images"></i> Foto Bukti Pelayanan:</label>
                <div class="photo-grid">
                    ${laporan.foto_bukti.map(url => `
                        <div class="photo-item">
                            <img src="${url}" alt="Bukti Layanan" onclick="previewImage('${url}')">
                            <a href="javascript:void(0)" class="photo-download-btn" onclick="downloadImage('${url}')" title="Unduh Foto">
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    `).join('')}
                </div>
            </div>`;
    }

    let filesHtml = '';
    if (laporan.surat_balasan) {
        filesHtml = `
            <div class="mb-3">
                <label class="fw-bold">Dokumen Pendukung:</label>
                <div class="mt-2 text-wrap">
                    <button class="btn btn-sm btn-outline-info me-2 mb-2" onclick="previewDocument('${laporan.surat_balasan}')">
                        <i class="fas fa-eye"></i> Preview Surat Balasan
                    </button>
                    <a href="${laporan.surat_balasan}" download class="btn btn-sm btn-outline-dark me-2 mb-2">
                        <i class="fas fa-download"></i> Unduh
                    </a>
                </div>
            </div>`;
    }

    content.innerHTML = `
        ${laporan.topik ? `
        <div class="mb-3">
            <label class="fw-bold">Topik:</label>
            <p class="mb-1">${laporan.topik}</p>
        </div>` : ''}
        ${laporan.ringkasan ? `
        <div class="mb-3">
            <label class="fw-bold">Ringkasan:</label>
            <p class="mb-1 text-justify">${laporan.ringkasan}</p>
        </div>` : ''}
        ${laporan.tags && laporan.tags.length > 0 ? `
            <div class="mb-3">
                <label class="fw-bold">Tags:</label>
                <div class="mt-1">
                    ${laporan.tags.map(t => `<span class="badge bg-light text-dark border me-1">${t}</span>`).join('')}
                </div>
            </div>` : ''}
        <div class="mb-3">
            <label class="fw-bold">Feedback Final Pengunjung:</label>
            <p><span class="badge bg-info">${laporan.feedback || '-'}</span></p>
        </div>
        ${photosHtml}
        ${filesHtml}
    `;
    
    new bootstrap.Modal(document.getElementById('viewReportModal')).show();
}

function previewImage(url) {
    document.getElementById('previewImageSource').src = url;
    new bootstrap.Modal(document.getElementById('imagePreviewModal')).show();
}

function previewDocument(url) {
    const container = document.getElementById('docPreviewContent');
    const isPDF = url.toLowerCase().endsWith('.pdf');
    
    container.innerHTML = '<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
    
    if (isPDF) {
        container.innerHTML = `<iframe src="${url}" width="100%" height="100%" frameborder="0"></iframe>`;
    } else {
        container.innerHTML = `<div class="text-center p-3 h-100 d-flex align-items-center justify-content-center">
            <img src="${url}" class="img-fluid rounded shadow" style="max-height: 100%;">
        </div>`;
    }
    
    new bootstrap.Modal(document.getElementById('docPreviewModal')).show();
}

function downloadImage(url) {
    const link = document.createElement('a');
    link.href = url;
    link.download = url.split('/').pop();
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function getStatusColor(status) {
    switch(status) {
        case 'Diterima': return 'primary';
        case 'Diproses': return 'warning';
        case 'Menunggu Persetujuan': return 'info';
        case 'Siap Diambil': return 'success';
        case 'Selesai': return 'secondary';
        default: return 'info';
    }
}


// Smart Search & Autocomplete Logic
let searchTimer;
function handlePhoneInput(input) {
    formatPhoneNumber(input);
    const query = input.value;
    const suggestionsCont = document.getElementById('phoneSuggestions');
    
    clearTimeout(searchTimer);
    if (query.length < 5) {
        suggestionsCont.classList.add('d-none');
        return;
    }

    searchTimer = setTimeout(() => {
        fetch(`/api/pengunjung/search?q=${query}`)
            .then(res => res.json())
            .then(data => {
                if (data.length > 0) {
                    suggestionsCont.innerHTML = data.map(v => `
                        <button type="button" class="list-group-item list-group-item-action py-2" onclick="selectVisitor('${v.nama_pengunjung}', '${v.no_hp}', '${v.email}', '${v.instansi}')">
                            <div class="d-flex justify-content-between">
                                <strong>${v.nama_pengunjung}</strong>
                                <small class="text-primary">${v.no_hp}</small>
                            </div>
                            <div class="small text-muted">${v.instansi}</div>
                        </button>
                    `).join('');
                    suggestionsCont.classList.remove('d-none');
                } else {
                    suggestionsCont.classList.add('d-none');
                }
            });
    }, 300);
}

function selectVisitor(name, phone, email, instansi) {
    document.querySelector('input[name="nama_pengunjung"]').value = name;
    document.querySelector('input[name="no_hp"]').value = phone;
    document.querySelector('input[name="email"]').value = email;
    document.querySelector('input[name="instansi"]').value = instansi;
    document.getElementById('phoneSuggestions').classList.add('d-none');
    showToast('Data pengunjung otomatis terisi!', 'success');
}

// Close suggestions on outside click
document.addEventListener('click', function(e) {
    if (!document.getElementById('phoneSuggestions').contains(e.target) && e.target.id !== 'no_hp') {
        document.getElementById('phoneSuggestions').classList.add('d-none');
    }
});

// Real-time Updates & Polling
let nextRefresh = 30;
function startRealtimeUpdates() {
    // 1. Clock Update
    setInterval(() => {
        const now = new Date();
        document.getElementById('realtimeClock').textContent = now.toLocaleTimeString('en-GB') + ' WITA';
        
        // Refresh counter
        nextRefresh--;
        if (nextRefresh <= 0) {
            refreshDashboardData();
            nextRefresh = 30;
        }
        document.getElementById('nextRefreshCounter').textContent = `(${nextRefresh}s)`;
    }, 1000);
}

function refreshDashboardData() {
    loadStats();
    
    // Refresh lists based on active tab
    const activeTab = document.querySelector('.tab-content.active');
    if (activeTab) {
        if (activeTab.id === 'tab-layanan') loadMyServices();
        if (activeTab.id === 'tab-status') loadStatus();
    }
}

function manualRefresh() {
    refreshDashboardData();
    nextRefresh = 30;
    document.getElementById('nextRefreshCounter').textContent = `(${nextRefresh}s)`;
    showToast('Data diperbarui (Manual)', 'info');
}

// Reply Letter Functions
function showReplyForm() {
    document.getElementById('btnShowReplyForm').style.display = 'none';
    document.getElementById('replyLetterDraft').style.display = 'block';
    
    // Autocomplete Purpose/Recipient if possible
    const currentName = document.getElementById('modalPengunjung').textContent;
    document.getElementById('modalReplyTujuan').value = currentName;
    document.getElementById('modalReplyPerihal').value = 'Penyampaian Data ' + (currentServiceType.replace('Permintaan Data', '').replace(/^[,\s]+|[,\s]+$/g, '') || '');
    
    generateReplyNumber();
}

function cancelReplyDraft() {
    document.getElementById('replyLetterDraft').style.display = 'none';
    document.getElementById('btnShowReplyForm').style.display = 'block';
}

function generateReplyNumber() {
    const kode = document.getElementById('modalReplyKode').value;
    
    fetch(`/api/replies/generate?kode_surat=${kode}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalReplyNomor').value = data.nomor_surat;
                document.getElementById('modalReplyUrut').value = data.nomor_urut;
                document.getElementById('modalReplyTanggal').value = new Date().toISOString().split('T')[0];
                document.getElementById('modalReplyKode').value = data.kode_surat;
            }
        });
}

function removeReplyLetter() {
    Swal.fire({
        title: 'Hapus Draft Surat?',
        text: "Nomor surat yang sudah tergenerate akan dibatalkan.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus'
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('replyLetterPreview').style.display = 'none';
            document.getElementById('btnShowReplyForm').style.display = 'block';
            // Also need to clear hidden inputs or state if any
        }
    });
}

function generateWordDocument() {
    let nomorSurat = document.getElementById('modalReplyNomor').value;
    let tanggalSurat = document.getElementById('modalReplyTanggal').value;
    let tujuan = document.getElementById('modalReplyTujuan').value;
    let perihal = document.getElementById('modalReplyPerihal').value;
    let kode = document.getElementById('modalReplyKode').value;
    let nomor_urut = document.getElementById('modalReplyUrut').value;
    
    // If fields are empty (re-opening modal), they should have been populated by openUpdateModal.
    // This check is a safety fallback.
    if (!nomorSurat) {
        nomorSurat = document.getElementById('previewReplyNomor').textContent;
    }
    
    if (!nomorSurat) {
        showToast('Nomor surat belum di-generate', 'error');
        return;
    }
    
    const btn = event.target;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    btn.disabled = true;
    
    fetch('/admin/reply-template/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            service_id: currentPermintaanDataId,
            nomor_surat: nomorSurat,
            tanggal_surat: tanggalSurat,
            tujuan: tujuan,
            perihal: perihal,
            kode_surat: kode,
            nomor_urut: nomor_urut,
            catatan: document.getElementById('modalCatatan').value
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        
        if (data.success) {
            showToast('Surat balasan berhasil dibuat!', 'success');
            // Download the file
            window.open(data.download_url, '_blank');
        } else {
            showToast('Gagal: ' + data.message, 'error');
        }
    })
    .catch(err => {
        btn.innerHTML = originalText;
        btn.disabled = false;
        showToast('Error: ' + err.message, 'error');
    });
}
</script>
@endpush
