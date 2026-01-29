<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Front Office BPS') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    
    <style>
        :root {
            /* Common */
            --primary: #1e40af;
            --secondary: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --transition: all 0.3s ease;

            /* Light Theme */
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --sidebar-grad: linear-gradient(135deg, #1e40af, #3b82f6);
            --stat-icon-bg: #dbeafe;
        }

        [data-theme='dark'] {
            --bg-body: #0f172a;
            --bg-card: #1e293b;
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --sidebar-grad: linear-gradient(135deg, #0f172a, #1e40af);
            --stat-icon-bg: #334155;
        }
        
        body {
            font-family: 'Figtree', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            transition: var(--transition);
        }
        
        .sidebar {
            width: 250px;
            height: 100vh;
            background: var(--sidebar-grad);
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px 0;
            color: white;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-header h3 {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .nav-item {
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
        }
        
        .nav-item:hover, .nav-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
            min-height: 100vh;
            width: calc(100% - 250px);
            transition: var(--transition);
        }
        
        .stat-card {
            background: var(--bg-card);
            border-radius: 15px;
            padding: 25px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-main);
        }
        
        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .card {
            background-color: var(--bg-card);
            border-radius: 15px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            color: var(--text-main);
        }

        .card-title {
            color: var(--text-main);
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        .table {
            color: var(--text-main);
        }

        .table-striped tbody tr:nth-of-type(odd) {
            --bs-table-bg-type: rgba(var(--text-main), 0.02);
        }

        .form-control, .form-select {
            background-color: var(--bg-card);
            border-color: var(--border-color);
            color: var(--text-main);
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--bg-card);
            color: var(--text-main);
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(30, 64, 175, 0.25);
        }

        .modal-content {
            background-color: var(--bg-card);
            color: var(--text-main);
        }
        
        .btn-primary {
            background: var(--primary);
            border: none;
        }
        
        .btn-primary:hover {
            background: #1e3a8a;
        }

        /* Dark Mode Toggle */
        .theme-toggle-btn {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-main);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .theme-toggle-btn:hover {
            transform: rotate(15deg) scale(1.1);
            background: var(--border-color);
        }

        #toast .alert {
            min-width: 300px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border-radius: 10px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        @yield('sidebar')
        
        <div class="main-content">
            <!-- Global Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center flex-grow-1">
                    @yield('title_section')
                </div>
                <div class="header-actions d-flex align-items-center gap-3">
                    <!-- Theme Toggle Button -->
                    <div id="theme-toggle" class="theme-toggle-btn" onclick="toggleTheme()">
                        <i class="fas fa-moon" id="theme-icon"></i>
                    </div>

                    <!-- User Profile Pill -->
                    <div class="user-profile-pill d-flex align-items-center gap-2 p-1 pe-3 border rounded-pill bg-white shadow-sm" style="cursor: pointer;" onclick="openProfileModal()">
                        <div class="profile-thumb" style="width: 35px; height: 35px; border-radius: 50%; overflow: hidden; background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                            @if(auth()->user()->foto)
                                <img id="header-avatar" src="{{ asset('storage/'.auth()->user()->foto) }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i id="header-icon" class="fas fa-user text-muted" style="font-size: 1rem;"></i>
                                <img id="header-avatar" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                            @endif
                        </div>
                        <div class="d-none d-md-block">
                            <div class="fw-bold text-dark" style="font-size: 0.85rem; line-height: 1.2;">{{ auth()->user()->name }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">{{ ucfirst(auth()->user()->role) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @yield('content')
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" style="position:fixed;top:20px;right:20px;z-index:9999;display:none;">
        <div class="alert" role="alert" id="toastContent">
            <span id="toastMessage"></span>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        function showToast(message, type = 'success') {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            Swal.fire({
                icon: type === 'error' ? 'error' : (type === 'success' ? 'success' : 'info'),
                title: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: isDark ? '#1e293b' : '#fff',
                color: isDark ? '#f1f5f9' : '#1e293b',
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });
        }

        function formatPhoneNumber(input) {
            let value = input.value.replace(/\D/g, ''); // Only digits
            if (value.startsWith('0')) {
                value = '62' + value.substring(1);
            } else if (value.startsWith('8')) {
                value = '62' + value;
            }
            input.value = value;
        }

        // Dark Mode Logic
        function toggleTheme() {
            const html = document.documentElement;
            const icon = document.getElementById('theme-icon');
            const currentTheme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', currentTheme);
            localStorage.setItem('theme', currentTheme);
            updateThemeIcon(currentTheme);
        }

        function updateThemeIcon(theme) {
            const icon = document.getElementById('theme-icon');
            if (theme === 'dark') {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        }

        // Initialize Theme
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon(savedTheme);
        })();

        // Global SweetAlert Listener for Sessions
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    timer: 3000,
                    showConfirmButton: false,
                    background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                    color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b',
                    toast: true,
                    position: 'top-end',
                    showClass: { popup: 'animate__animated animate__fadeInRight' },
                    hideClass: { popup: 'animate__animated animate__fadeOutRight' }
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Waduh!',
                    text: "{{ session('error') }}",
                    background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#fff',
                    color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b',
                    confirmButtonColor: '#1e40af'
                });
            @endif
        });

        // PROFILE MANAGEMENT LOGIC
        function openProfileModal() {
            new bootstrap.Modal(document.getElementById('editProfileModal')).show();
        }

        function previewProfilePhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profile-preview');
                    const icon = document.getElementById('profile-preview-icon');
                    const container = document.getElementById('profile-preview-container');
                    
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (icon) icon.style.display = 'none';
                    container.classList.remove('bg-light', 'd-flex', 'align-items-center', 'justify-content-center');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function resetProfilePhotoLocal() {
            const preview = document.getElementById('profile-preview');
            const icon = document.getElementById('profile-preview-icon');
            const container = document.getElementById('profile-preview-container');
            const originalFoto = "{{ auth()->user()->foto ? asset('storage/'.auth()->user()->foto) : '' }}";
            
            if (originalFoto) {
                preview.src = originalFoto;
                preview.style.display = 'block';
                if (icon) icon.style.display = 'none';
                container.classList.remove('bg-light', 'd-flex', 'align-items-center', 'justify-content-center');
            } else {
                preview.style.display = 'none';
                if (icon) icon.style.display = 'block';
                container.classList.add('bg-light', 'd-flex', 'align-items-center', 'justify-content-center');
            }
            document.getElementById('profile-photo-input').value = '';
        }

        function resetToOfficialPhoto() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            Swal.fire({
                title: 'Konfirmasi Reset',
                text: "Foto profil Anda akan diperbarui dengan mengunduh ulang data resmi dari portal BPS. Lanjutkan?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0dcaf0',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Sinkronkan!',
                cancelButtonText: 'Batal',
                background: isDark ? '#1e293b' : '#fff',
                color: isDark ? '#f1f5f9' : '#1e293b'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeResetToOfficialPhoto();
                }
            });
        }

        function executeResetToOfficialPhoto() {
            const btn = document.getElementById('btnResetOfficial');
            const originalHtml = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resetting...';
            
            fetch('/api/profile/reset-photo', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    if (data.user.foto) {
                        const timestamp = new Date().getTime();
                        const photoUrl = data.user.foto + '?t=' + timestamp;

                        // Update header avatar
                        const headerAvatar = document.getElementById('header-avatar');
                        if (headerAvatar) {
                            const headerIcon = document.getElementById('header-icon');
                            const headerContainer = headerAvatar.parentElement;
                            
                            headerAvatar.src = photoUrl;
                            headerAvatar.style.display = 'block';
                            if (headerIcon) headerIcon.style.display = 'none';
                            headerContainer.classList.remove('bg-light', 'd-flex', 'align-items-center', 'justify-content-center');
                        }
                        
                        // Update preview in modal
                        const preview = document.getElementById('profile-preview');
                        const icon = document.getElementById('profile-preview-icon');
                        const container = document.getElementById('profile-preview-container');
                        
                        preview.src = photoUrl;
                        preview.style.display = 'block';
                        if (icon) icon.style.display = 'none';
                        container.classList.remove('bg-light', 'd-flex', 'align-items-center', 'justify-content-center');
                        
                        // Clear file input
                        document.getElementById('profile-photo-input').value = '';
                    }
                } else {
                    showToast(data.message || 'Gagal mereset foto', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Terjadi kesalahan', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt"></i> Reset ke Foto Official BPS';
            });
        }

        function submitProfileUpdate() {
            const btn = document.getElementById('btnUpdateProfile');
            const noHp = document.getElementById('profile_no_hp').value;
            const photoInput = document.getElementById('profile-photo-input');
            
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            
            const formData = new FormData();
            formData.append('no_hp', noHp);
            
            const password = document.getElementById('profile_password').value;
            const confirm = document.getElementById('profile_password_confirmation').value;
            
            if (password) {
                if (password.length < 6) {
                    showToast('Password minimal 6 karakter', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
                    return;
                }
                if (password !== confirm) {
                    showToast('Konfirmasi password tidak cocok', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
                    return;
                }
                formData.append('password', password);
                formData.append('password_confirmation', confirm);
            }

            if (photoInput.files.length > 0) {
                formData.append('foto', photoInput.files[0]);
            }
            
            fetch('/api/profile/update', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    if (data.user.foto) {
                        const timestamp = new Date().getTime();
                        const photoUrl = data.user.foto + '?t=' + timestamp;
                        
                        // Update header avatar
                        const headerAvatar = document.getElementById('header-avatar');
                        if (headerAvatar) {
                            headerAvatar.src = photoUrl;
                        }
                    }
                    bootstrap.Modal.getInstance(document.getElementById('editProfileModal')).hide();
                } else {
                    showToast(data.message || 'Gagal memperbarui profil', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Terjadi kesalahan', 'error');
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
            });
        }
    </script>

    <!-- Shared Modals -->
    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-circle"></i> Edit Profil Petugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editProfileForm">
                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                <div id="profile-preview-container" class="rounded-circle border @if(!auth()->user()->foto) bg-light d-flex align-items-center justify-content-center @endif" style="width: 120px; height: 120px; overflow: hidden;">
                                    @if(auth()->user()->foto)
                                        <img id="profile-preview" src="{{ asset('storage/'.auth()->user()->foto) }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    @else
                                        <i id="profile-preview-icon" class="fas fa-user text-muted" style="font-size: 4rem;"></i>
                                        <img id="profile-preview" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                                    @endif
                                </div>
                                <label for="profile-photo-input" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="profile-photo-input" class="d-none" accept="image/*" onchange="previewProfilePhoto(this)">
                            </div>
                            <div class="mt-2 text-center">
                                <button type="button" class="btn btn-xs btn-outline-secondary border-0" onclick="resetProfilePhotoLocal()">
                                    <i class="fas fa-undo"></i> Batal / Reset
                                </button>
                                <button type="button" id="btnResetOfficial" class="btn btn-xs btn-outline-info border-0 ms-2" onclick="resetToOfficialPhoto()">
                                    <i class="fas fa-sync-alt"></i> Reset ke Foto Official BPS
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" value="{{ auth()->user()->name }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">No. HP (WhatsApp)</label>
                            <input type="text" class="form-control" id="profile_no_hp" value="{{ auth()->user()->no_hp }}" placeholder="628xxxxxxxxxx" oninput="formatPhoneNumber(this)">
                            <small class="text-muted">Gunakan format internasional (628...)</small>
                        </div>

                        @if(\App\Models\SystemSetting::get('allow_user_password_change', 'false') === 'true')
                        <hr>
                        <div class="mb-3">
                            <label class="form-label text-primary fw-bold small"><i class="fas fa-key"></i> Ganti Password (Opsional)</label>
                            <input type="password" class="form-control mb-2" id="profile_password" placeholder="Password Baru">
                            <input type="password" class="form-control" id="profile_password_confirmation" placeholder="Konfirmasi Password Baru">
                            <small class="text-muted">Kosongkan jika tidak ingin mengganti password</small>
                        </div>
                        @else
                            <input type="hidden" id="profile_password" value="">
                            <input type="hidden" id="profile_password_confirmation" value="">
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnUpdateProfile" onclick="submitProfileUpdate()">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
