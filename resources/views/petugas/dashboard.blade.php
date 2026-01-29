@extends('layouts.dashboard')

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
        <div class="badge bg-success-subtle text-success border border-success-subtle mt-1" style="font-size: 0.65rem;">
            <i class="fas fa-circle-play fa-fade me-1"></i> LIVE UPDATES ACTIVE 
            <span id="nextRefreshCounter" class="ms-1 text-muted fw-normal">(30s)</span>
        </div>
    </div>
    <div class="text-end d-none d-md-block ms-auto me-3">
        <div class="fw-bold small">{{ now()->translatedFormat('d M Y') }}</div>
        <div class="text-muted small" id="realtimeClock" style="font-size: 0.7rem;">{{ now()->format('H:i:s') }} WITA</div>
    </div>
@endsection

@section('content')

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value" id="statVisitorsToday">0</div>
            <div class="stat-label">Pengunjung Hari Ini</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value" id="statVisitorsWeek">0</div>
            <div class="stat-label">Minggu Ini</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-value" id="statVisitorsTotal">0</div>
            <div class="stat-label">Total Dilayani</div>
        </div>
    </div>
    <div class="col-md-3">
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
                                <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Pengaduan" id="pengaduan">
                                <label class="form-check-label" for="pengaduan">Pengaduan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Lainnya" id="lainnya">
                                <label class="form-check-label" for="lainnya">Lainnya</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Surat Fields (shown when Permintaan Data selected) -->
                    <div id="suratFields" style="display:none;" class="col-12">
                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fas fa-file-alt"></i> Detail Surat Permintaan</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nomor_surat" id="nomor_surat">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_surat" id="tanggal_surat">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Upload Surat (PDF/Gambar, max 5MB)</label>
                                        <input type="file" class="form-control" name="file_surat" accept=".pdf,.jpg,.jpeg,.png">
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
            <h5 class="card-title"><i class="fas fa-tasks"></i> Layanan yang Saya Tangani</h5>
            <div id="layananList" class="mt-3">
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-2 text-muted">Memuat data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="tab-status" class="tab-content" style="display:none;">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title"><i class="fas fa-chart-line"></i> Status Semua Layanan</h5>
            
            <!-- Stats Row -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card" style="background:#dbeafe;">
                        <div class="stat-value text-primary" id="statusDiterima">0</div>
                        <div class="stat-label">Diterima</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background:#fef3c7;">
                        <div class="stat-value text-warning" id="statusDiproses">0</div>
                        <div class="stat-label">Diproses</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background:#d1fae5;">
                        <div class="stat-value text-success" id="statusSiap">0</div>
                        <div class="stat-label">Siap Diambil</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card" style="background:#e0e7ff;">
                        <div class="stat-value" style="color:#4f46e5;" id="statusSelesai">0</div>
                        <div class="stat-label">Selesai</div>
                    </div>
                </div>
            </div>
            
            <div id="statusList" class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Pengunjung</th>
                            <th>Instansi</th>
                            <th>Jenis</th>
                            <th>No. Surat</th>
                            <th>Petugas</th>
                            <th>Status</th>
                            <th>Update</th>
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
                            <label class="form-label">Foto Bukti Pelayanan (Bisa pilih banyak)</label>
                            <input type="file" class="form-control" id="modalFotoBukti" multiple accept="image/png, image/jpeg, image/jpg, image/webp">
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
                        <div class="mb-3">
                            <label class="form-label">Surat Balasan (PDF)</label>
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
                                    <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Pengaduan" id="edit_pengaduan">
                                    <label class="form-check-label" for="edit_pengaduan">Pengaduan</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="jenis_layanan[]" value="Lainnya" id="edit_lainnya">
                                    <label class="form-check-label" for="edit_lainnya">Lainnya</label>
                                </div>
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

// Load stats on page load
loadStats();
startRealtimeUpdates();

function toggleSuratFields() {
    const permintaan = document.getElementById('permintaan');
    const suratFields = document.getElementById('suratFields');
    const nomorSurat = document.getElementById('nomor_surat');
    const tanggalSurat = document.getElementById('tanggal_surat');
    
    if (permintaan.checked) {
        suratFields.style.display = 'block';
        nomorSurat.required = true;
        tanggalSurat.required = true;
    } else {
        suratFields.style.display = 'none';
        nomorSurat.required = false;
        tanggalSurat.required = false;
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

function loadStats() {
    fetch('/api/stats/petugas')
        .then(res => res.json())
        .then(data => {
            document.getElementById('statVisitorsToday').textContent = data.visitors?.today || 0;
            document.getElementById('statVisitorsWeek').textContent = data.visitors?.week || 0;
            document.getElementById('statVisitorsTotal').textContent = data.visitors?.total || 0;
            document.getElementById('statRating').textContent = data.rating?.average || '0.0';
        })
        .catch(err => console.error('Stats error:', err));
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
                showRatingLinkModal(localRatingUrl, data.skd_token, data.remote_rating_url);
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

function showRatingLinkModal(ratingUrl, skdToken, remoteRatingUrl = null) {
    const skdUrl = `https://script.google.com/macros/s/AKfycbx6NAMQSZTBFuda4tpddVggCK87wr0pCLUxpCarjLJYH7OvbTXJ80j_fPLBAXtXWO0/exec?token=${skdToken}`;
    
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
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control form-control-sm bg-light" id="remoteRatingInput" readonly>
                                        <button class="btn btn-sm btn-primary" onclick="copyToClipboard('remoteRatingInput')"><i class="fas fa-copy"></i></button>
                                    </div>
                                    <div class="d-grid">
                                        <a id="waRemoteRating" href="#" target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="fab fa-whatsapp"></i> WA
                                        </a>
                                    </div>
                                </div>
                                
                                <!-- SKD Column -->
                                <div class="col-md-4" id="skdCol">
                                    <h6 class="fw-bold mb-3"><i class="fas fa-poll-h text-primary"></i> 3. Survei SKD</h6>
                                    <p class="small text-muted mb-2">Hanya Layanan Data</p>
                                    <div id="qrSkdContainer" class="mb-3 p-2 bg-white d-inline-block rounded shadow-sm border"></div>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control form-control-sm bg-light" id="skdInput" readonly>
                                        <button class="btn btn-sm btn-primary" onclick="copyToClipboard('skdInput')"><i class="fas fa-copy"></i></button>
                                    </div>
                                    <div class="d-grid">
                                        <a id="waSkd" href="#" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fab fa-whatsapp"></i> WA
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-primary mt-4 mb-0 py-2 border-0 bg-primary-subtle" style="font-size: 0.8rem;" id="modalInstruction">
                                <i class="fas fa-info-circle me-1"></i> Mohon informasikan link/QR di atas kepada pengunjung untuk evaluasi layanan.
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
        new QRCode(qrRating, { text: ratingUrl, width: 120, height: 120 });
        document.getElementById('ratingInput').value = ratingUrl;
        document.getElementById('waRating').href = 'https://wa.me/?text=' + encodeURIComponent('Halo, mohon bantu kami meningkatkan layanan dengan memberikan penilaian petugas PST melalui link: ' + ratingUrl);
        ratingCol.style.display = 'block';
    } else {
        ratingCol.style.display = 'none';
    }

    // Handle Remote Rating
    if (remoteRatingUrl) {
        const qrRemote = document.getElementById('qrRemoteRatingContainer');
        qrRemote.innerHTML = '';
        new QRCode(qrRemote, { text: remoteRatingUrl, width: 120, height: 120 });
        document.getElementById('remoteRatingInput').value = remoteRatingUrl;
        document.getElementById('waRemoteRating').href = 'https://wa.me/?text=' + encodeURIComponent('Halo, terima kasih telah berkunjung ke BPS. Anda dapat mengisi penilaian layanan kami dari rumah melalui link ini: ' + remoteRatingUrl);
        remoteRatingCol.style.display = 'block';
    } else {
        remoteRatingCol.style.display = 'none';
    }

    // Handle SKD Content
    if (skdToken) {
        const fullSkdUrl = `https://script.google.com/macros/s/AKfycbx6NAMQSZTBFuda4tpddVggCK87wr0pCLUxpCarjLJYH7OvbTXJ80j_fPLBAXtXWO0/exec?token=${skdToken}`;
        const qrSkd = document.getElementById('qrSkdContainer');
        qrSkd.innerHTML = '';
        new QRCode(qrSkd, { text: fullSkdUrl, width: 120, height: 120 });
        document.getElementById('skdInput').value = fullSkdUrl;
        document.getElementById('waSkd').href = 'https://wa.me/?text=' + encodeURIComponent('Halo, mohon bantu kami meningkatkan kualitas data dengan mengisi Survei SKD melalui link: ' + fullSkdUrl);
        skdCol.style.display = 'block';
    } else {
        skdCol.style.display = 'none';
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
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1"><i class="fas fa-user"></i> ${s.nama_pengunjung}</h6>
                            <div class="small fw-bold text-primary mb-1">
                                <i class="fas fa-calendar-alt"></i> Kunjungan: ${s.tanggal_kunjungan || '-'}
                            </div>
                            <p class="mb-1 text-muted" style="font-size:0.85rem;"><i class="fas fa-tag"></i> ${s.jenis_layanan}</p>
                            <p class="mb-1 text-muted"><i class="fas fa-building"></i> ${s.instansi}</p>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <small class="text-muted"><i class="fas fa-file-alt"></i> No. Surat: ${s.nomor_surat || '-'}</small>
                                ${s.sarana_kunjungan === 'Online' ? `
                                    <span class="badge bg-light text-dark border"><i class="fas fa-globe"></i> ${s.online_channel}${s.nama_petugas_online ? ' ('+s.nama_petugas_online+')' : ''}</span>
                                ` : ''}
                                <!-- Only show SKD badge for Permintaan Data (which has skd_token) -->
                                ${s.status_layanan === 'Selesai' && s.skd_token ? (
                                    s.skd_filled ? 
                                    '<span class="badge bg-success"><i class="fas fa-check-circle"></i> SKD Diisi</span>' : 
                                    `<span class="badge bg-warning text-dark" id="skd-badge-${s.skd_token}"><i class="fas fa-hourglass-half"></i> Menunggu SKD</span>`
                                ) : ''}
                                ${s.rated ? '<span class="badge bg-success"><i class="fas fa-star"></i> Rated</span>' : ''}
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-${getStatusColor(s.status_layanan)} mb-2">${s.status_layanan}</span>
                            <br>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-dark" onclick="openEditVisitorModal(${s.id})" title="Edit Data Pengunjung">
                                    <i class="fas fa-user-edit"></i> Edit Data
                                </button>
                                ${s.status_layanan !== 'Selesai' ? `
                                    <button class="btn btn-sm btn-outline-primary" onclick="openUpdateModal(${s.id}, '${s.nama_pengunjung}', '${s.nomor_surat || '-'}', '${s.status_layanan}', '${s.jenis_layanan}')">
                                        <i class="fas fa-edit"></i> Update Status
                                    </button>
                                ` : ''}
                                ${s.file_surat ? `
                                    <a href="${s.file_surat}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-file-pdf"></i> Lihat Surat
                                    </a>
                                ` : ''}
                                ${s.rating_token && !s.rated ? `
                                    <button class="btn btn-sm btn-outline-warning" onclick="showRatingLinkModal('${window.location.origin}/rating/${s.rating_token}', '${s.skd_token || ''}')">
                                        <i class="fas fa-star"></i> Rating
                                    </button>
                                ` : ''}
                                ${s.skd_token ? `
                                    <button class="btn btn-sm btn-outline-success" onclick="showRatingLinkModal(null, '${s.skd_token}')">
                                        <i class="fas fa-link"></i> SKD
                                    </button>
                                ` : ''}
                                ${s.status_layanan === 'Selesai' && s.skd_token && !s.skd_filled ? `
                                    <button class="btn btn-sm btn-outline-dark" onclick="checkSkdStatus('${s.skd_token}', true)">
                                        <i class="fas fa-sync-alt"></i> Cek SKD
                                    </button>
                                ` : ''}
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
    document.getElementById('edit_pengaduan').checked = jenis.includes('Pengaduan');
    document.getElementById('edit_lainnya').checked = jenis.includes('Lainnya');

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

function submitEditVisitor() {
    const id = document.getElementById('edit_id').value;
    const form = document.getElementById('editVisitorForm');
    const formData = new FormData(form);
    formData.append('_method', 'PUT');

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

function openUpdateModal(id, pengunjung, noSurat, status, jenisLayanan) {
    currentServiceId = id;
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
    
    toggleReportFields();
    
    var modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
}

function toggleReportFields() {
    const status = document.getElementById('modalStatus').value;
    const reportFields = document.getElementById('reportFields');
    const commonReports = document.getElementById('commonReportFields');
    const permintaanDataFields = document.getElementById('permintaanDataFields');
    
    const isSelesai = (status === 'Selesai');
    const isConsultationOrComplaint = currentServiceType.includes('Konsultasi') || currentServiceType.includes('Pengaduan');
    const isDataRequest = currentServiceType.includes('Permintaan Data');
    
    if (isSelesai) {
        reportFields.style.display = 'block';
        
        // Show common fields only for Consultation or Complaints
        commonReports.style.display = isConsultationOrComplaint ? 'block' : 'none';
        
        // Show data fields only for Data Requests
        permintaanDataFields.style.display = isDataRequest ? 'block' : 'none';
    } else {
        reportFields.style.display = 'none';
        commonReports.style.display = 'none';
        permintaanDataFields.style.display = 'none';
    }
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
        const isConsultationOrComplaint = currentServiceType.includes('Konsultasi') || currentServiceType.includes('Pengaduan');
        const isDataRequest = currentServiceType.includes('Permintaan Data');

        if (isConsultationOrComplaint) {
            const topik = document.getElementById('modalTopik').value;
            const ringkasan = document.getElementById('modalRingkasan').value;
            
            if (!topik || !ringkasan) {
                showToast('Topik dan Ringkasan wajib diisi untuk Konsultasi/Pengaduan!', 'error');
                return;
            }
            
            formData.append('topik', topik);
            formData.append('ringkasan', ringkasan);
            formData.append('feedback_final', document.getElementById('modalFeedback').value);

            const photos = document.getElementById('modalFotoBukti').files;
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
        if (data.success) {
            showToast('Status berhasil diupdate!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('updateStatusModal')).hide();
            loadMyServices();
            
            // Trigger SKD modal if status changed to Selesai (Rating only for initial guest book)
            if (status === 'Selesai' && data.skd_token) {
                showRatingLinkModal(null, data.skd_token);
            }
        } else {
            showToast('Error: ' + (data.message || 'Gagal update'), 'error');
        }
    })
    .catch(err => {
        showToast('Error: ' + err.message, 'error');
    });
}

function loadStatus() {
    fetch('/api/services')
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
                        <div class="small text-primary fw-bold mt-1">
                            <i class="fas fa-calendar-alt"></i> ${s.tanggal_kunjungan || '-'}
                        </div>
                        ${s.sarana_kunjungan === 'Online' ? `
                            <small class="text-muted d-block mt-1"><i class="fas fa-globe"></i> ${s.online_channel}${s.nama_petugas_online ? ' ('+s.nama_petugas_online+')' : ''}</small>
                        ` : ''}
                    </td>
                    <td>${s.instansi || '-'}</td>
                    <td><small>${s.jenis_layanan || '-'}</small></td>
                    <td>${s.nomor_surat || '-'}</td>
                    <td>${s.nama_petugas || '-'}</td>
                    <td><span class="badge bg-${getStatusColor(s.status_layanan)}">${s.status_layanan}</span></td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <span class="badge bg-light text-dark border">${s.tanggal_update || '-'}</span>
                            ${s.rated ? '<span class="badge bg-success-subtle text-success border-success-subtle"><i class="fas fa-star"></i> Rated</span>' : ''}
                        </div>
                    </td>
                    <td>
                        <div class="btn-group">
                            ${s.rating_token && !s.rated ? `
                                <button class="btn btn-xs btn-outline-warning" onclick="showRatingLinkModal('${window.location.origin}/rating/${s.rating_token}', '${s.skd_token || ''}')" title="Lihat Link Rating">
                                    <i class="fas fa-star"></i>
                                </button>
                            ` : ''}
                            ${s.skd_token ? `
                                <button class="btn btn-xs btn-outline-success" onclick="showRatingLinkModal(null, '${s.skd_token}')" title="Lihat Link SKD">
                                    <i class="fas fa-link"></i>
                                </button>
                            ` : ''}
                            ${s.laporan ? `
                                <button class="btn btn-xs btn-outline-primary" onclick="viewReport('${encodeURIComponent(JSON.stringify(s.laporan))}')" title="Lihat Laporan">
                                    <i class="fas fa-file-invoice"></i>
                                </button>
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
                <div class="mt-2">
                    <a href="${laporan.surat_balasan}" target="_blank" class="btn btn-sm btn-outline-info me-2 mb-2">
                        <i class="fas fa-file-pdf"></i> Lihat Surat Balasan
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
                        <button type="button" class="list-group-item list-group-item-action py-2" onclick="selectVisitor('${v.nama_konsumen}', '${v.no_hp}', '${v.email}', '${v.instansi}')">
                            <div class="d-flex justify-content-between">
                                <strong>${v.nama_konsumen}</strong>
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
</script>
@endpush
