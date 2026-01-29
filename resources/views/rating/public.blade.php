<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Penilaian Layanan - BPS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container { max-width: 600px; margin: 0 auto; }
        
        .rating-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .rating-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .rating-header h2 {
            color: #1e293b;
            font-weight: 700;
        }
        
        /* Step indicator */
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
        }
        
        .step.active { color: #667eea; }
        .step.completed { color: #10b981; }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .step.active .step-number { background: #667eea; color: white; }
        .step.completed .step-number { background: #10b981; color: white; }
        
        /* Petugas selection */
        .petugas-option {
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .petugas-option:hover {
            border-color: #667eea;
            background: #f8fafc;
        }
        
        .petugas-option.selected {
            border-color: #667eea;
            background: #eef2ff;
        }
        
        .petugas-option img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .petugas-option .avatar-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        /* Star rating */
        .rating-category { margin-bottom: 25px; }
        .rating-category label {
            display: block;
            font-weight: 600;
            color: #475569;
            margin-bottom: 10px;
        }
        
        .star-rating {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
        
        .star-rating .star {
            font-size: 2rem;
            color: #e2e8f0;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .star-rating .star:hover,
        .star-rating .star.active { color: #fbbf24; transform: scale(1.1); }
        
        /* Buttons */
        .btn-next {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-next:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .btn-next:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        
        .btn-back {
            background: #f1f5f9;
            color: #475569;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
        }
        
        /* Toast */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast-message {
            padding: 15px 25px;
            border-radius: 10px;
            color: white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .success-screen { text-align: center; }
        .success-screen i { font-size: 5rem; color: #10b981; margin-bottom: 20px; }
        
        .step-content { display: none; }
        .step-content.active { display: block; }
        
        .komentar-box textarea {
            width: 100%;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            resize: none;
        }
        
        .komentar-box textarea:focus {
            outline: none;
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="rating-card">
            <div class="rating-header">
                <h2><i class="fas fa-star"></i> Penilaian Layanan</h2>
                <p class="text-muted">Berikan penilaian untuk pelayanan BPS</p>
            </div>
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active" id="step1-indicator">
                    <div class="step-number">1</div>
                    <span>Verifikasi</span>
                </div>
                <div class="step" id="step2-indicator">
                    <div class="step-number">2</div>
                    <span>Pilih Petugas</span>
                </div>
                <div class="step" id="step3-indicator">
                    <div class="step-number">3</div>
                    <span>Beri Rating</span>
                </div>
            </div>
            
            <!-- Step 1: Phone Verification -->
            <div class="step-content active" id="step1">
                <div class="text-center mb-4">
                    <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                    <h5>Masukkan Nomor HP Anda</h5>
                    <p class="text-muted">Nomor HP yang Anda gunakan saat berkunjung hari ini</p>
                </div>
                <div class="mb-4">
                    <input type="text" class="form-control form-control-lg text-center" id="phoneInput" 
                           placeholder="628xxxxxxxxxx" maxlength="20" oninput="formatPhoneNumber(this)">
                    <small class="text-muted d-block text-center mt-2">
                        <i class="fas fa-info-circle"></i> Hanya pengunjung yang terdaftar hari ini bisa memberi rating
                    </small>
                </div>
                <button class="btn-next" onclick="verifyPhone()">
                    <i class="fas fa-arrow-right"></i> Lanjutkan
                </button>
            </div>
            
            <!-- Step 2: Select Officer -->
            <div class="step-content" id="step2">
                <div class="text-center mb-4">
                    <h5>Pilih Petugas yang Melayani Anda</h5>
                </div>
                <div id="petugasList" class="mb-4">
                    <!-- Filled by JS -->
                </div>
                <div class="d-flex gap-2">
                    <button class="btn-back" onclick="goToStep(1)">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button class="btn-next flex-grow-1" onclick="selectPetugas()" id="btnSelectPetugas" disabled>
                        <i class="fas fa-arrow-right"></i> Lanjutkan
                    </button>
                </div>
            </div>
            
            <!-- Step 3: Rating Form -->
            <div class="step-content" id="step3">
                <div class="text-center mb-4">
                    <div id="selectedPetugasInfo"></div>
                </div>
                
                <div class="rating-category">
                    <label><i class="fas fa-smile"></i> Keramahan</label>
                    <div class="star-rating" data-category="keramahan">
                        <i class="fas fa-star star" data-value="1"></i>
                        <i class="fas fa-star star" data-value="2"></i>
                        <i class="fas fa-star star" data-value="3"></i>
                        <i class="fas fa-star star" data-value="4"></i>
                        <i class="fas fa-star star" data-value="5"></i>
                    </div>
                    <input type="hidden" id="rating_keramahan" value="0">
                </div>
                
                <div class="rating-category">
                    <label><i class="fas fa-bolt"></i> Kecepatan</label>
                    <div class="star-rating" data-category="kecepatan">
                        <i class="fas fa-star star" data-value="1"></i>
                        <i class="fas fa-star star" data-value="2"></i>
                        <i class="fas fa-star star" data-value="3"></i>
                        <i class="fas fa-star star" data-value="4"></i>
                        <i class="fas fa-star star" data-value="5"></i>
                    </div>
                    <input type="hidden" id="rating_kecepatan" value="0">
                </div>
                
                <div class="rating-category">
                    <label><i class="fas fa-brain"></i> Pengetahuan</label>
                    <div class="star-rating" data-category="pengetahuan">
                        <i class="fas fa-star star" data-value="1"></i>
                        <i class="fas fa-star star" data-value="2"></i>
                        <i class="fas fa-star star" data-value="3"></i>
                        <i class="fas fa-star star" data-value="4"></i>
                        <i class="fas fa-star star" data-value="5"></i>
                    </div>
                    <input type="hidden" id="rating_pengetahuan" value="0">
                </div>
                
                <div class="rating-category">
                    <label><i class="fas fa-award"></i> Keseluruhan</label>
                    <div class="star-rating" data-category="keseluruhan">
                        <i class="fas fa-star star" data-value="1"></i>
                        <i class="fas fa-star star" data-value="2"></i>
                        <i class="fas fa-star star" data-value="3"></i>
                        <i class="fas fa-star star" data-value="4"></i>
                        <i class="fas fa-star star" data-value="5"></i>
                    </div>
                    <input type="hidden" id="rating_keseluruhan" value="0">
                </div>
                
                <div class="komentar-box mb-4">
                    <label class="form-label fw-semibold"><i class="fas fa-comment"></i> Komentar (opsional)</label>
                    <textarea id="komentar" rows="3" placeholder="Tuliskan komentar atau saran Anda..."></textarea>
                </div>
                
                <div class="d-flex gap-2">
                    <button class="btn-back" onclick="goToStep(2)">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                    <button class="btn-next flex-grow-1" onclick="submitRating()" id="btnSubmit">
                        <i class="fas fa-paper-plane"></i> Kirim Penilaian
                    </button>
                </div>
            </div>
            
            <!-- Success Screen -->
            <div class="step-content" id="successScreen">
                <div class="success-screen">
                    <i class="fas fa-check-circle"></i>
                    <h3>Terima Kasih!</h3>
                    <p class="text-muted">Penilaian Anda telah kami terima.<br>Terima kasih telah menggunakan layanan kami.</p>
                    <a href="/" class="btn btn-primary mt-3">
                        <i class="fas fa-home"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="toast-container" id="toastContainer"></div>

    <script>
        let verifiedBukuTamu = null;
        let selectedPetugasId = null;
        let petugasData = [];
        
        // Star rating functionality
        document.querySelectorAll('.star-rating').forEach(container => {
            const category = container.dataset.category;
            const stars = container.querySelectorAll('.star');
            const input = document.getElementById('rating_' + category);
            
            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const value = parseInt(star.dataset.value);
                    input.value = value;
                    
                    stars.forEach((s, index) => {
                        s.classList.toggle('active', index < value);
                    });
                });
                
                star.addEventListener('mouseenter', () => {
                    const value = parseInt(star.dataset.value);
                    stars.forEach((s, index) => {
                        s.style.color = index < value ? '#fbbf24' : '#e2e8f0';
                    });
                });
            });
            
            container.addEventListener('mouseleave', () => {
                stars.forEach(s => {
                    s.style.color = s.classList.contains('active') ? '#fbbf24' : '#e2e8f0';
                });
            });
        });
        
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast-message';
            toast.style.background = type === 'error' ? '#ef4444' : '#10b981';
            toast.innerHTML = '<i class="fas fa-' + (type === 'error' ? 'exclamation-circle' : 'check-circle') + '"></i> ' + message;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
        
        function goToStep(step) {
            document.querySelectorAll('.step-content').forEach(s => s.classList.remove('active'));
            document.getElementById('step' + step).classList.add('active');
            
            // Update indicators
            for (let i = 1; i <= 3; i++) {
                const indicator = document.getElementById('step' + i + '-indicator');
                indicator.classList.remove('active', 'completed');
                if (i < step) indicator.classList.add('completed');
                else if (i === step) indicator.classList.add('active');
            }
        }
        
        async function verifyPhone() {
            const phone = document.getElementById('phoneInput').value.trim();
            
            if (!phone || phone.length < 10) {
                showToast('Masukkan nomor HP yang valid', 'error');
                return;
            }
            
            try {
                const response = await fetch('/rating/verify-phone', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ no_hp: phone })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    verifiedBukuTamu = data.buku_tamu;
                    petugasData = data.petugas_list;
                    renderPetugasList(petugasData);
                    goToStep(2);
                } else {
                    showToast(data.message || 'Nomor HP tidak ditemukan', 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            }
        }
        
        function renderPetugasList(list) {
            const container = document.getElementById('petugasList');
            
            if (list.length === 0) {
                container.innerHTML = '<p class="text-center text-muted">Tidak ada petugas yang tersedia</p>';
                return;
            }
            
            container.innerHTML = list.map(p => `
                <div class="petugas-option" onclick="togglePetugas(${p.id}, this)" data-id="${p.id}">
                    ${p.foto ? 
                        `<img src="${p.foto}" alt="${p.name}">` : 
                        `<div class="avatar-placeholder">${p.name.charAt(0).toUpperCase()}</div>`
                    }
                    <div>
                        <h6 class="mb-0">${p.name}</h6>
                        <small class="text-muted">${p.jabatan || 'Petugas Pelayanan'}</small>
                    </div>
                </div>
            `).join('');
        }
        
        function togglePetugas(id, element) {
            document.querySelectorAll('.petugas-option').forEach(o => o.classList.remove('selected'));
            element.classList.add('selected');
            selectedPetugasId = id;
            document.getElementById('btnSelectPetugas').disabled = false;
        }
        
        function selectPetugas() {
            if (!selectedPetugasId) {
                showToast('Pilih petugas terlebih dahulu', 'error');
                return;
            }
            
            const petugas = petugasData.find(p => p.id === selectedPetugasId);
            document.getElementById('selectedPetugasInfo').innerHTML = `
                <h5>Menilai: ${petugas.name}</h5>
                <p class="text-muted">${petugas.jabatan || 'Petugas Pelayanan'}</p>
            `;
            
            goToStep(3);
        }
        
        async function submitRating() {
            const data = {
                buku_tamu_id: verifiedBukuTamu.id,
                user_id: selectedPetugasId,
                rating_keramahan: document.getElementById('rating_keramahan').value,
                rating_kecepatan: document.getElementById('rating_kecepatan').value,
                rating_pengetahuan: document.getElementById('rating_pengetahuan').value,
                rating_keseluruhan: document.getElementById('rating_keseluruhan').value,
                komentar: document.getElementById('komentar').value
            };
            
            if (data.rating_keramahan == 0 || data.rating_kecepatan == 0 || 
                data.rating_pengetahuan == 0 || data.rating_keseluruhan == 0) {
                showToast('Mohon isi semua kategori penilaian', 'error');
                return;
            }
            
            const btn = document.getElementById('btnSubmit');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
            
            try {
                const response = await fetch('/rating/submit-public', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.querySelectorAll('.step-content').forEach(s => s.classList.remove('active'));
                    document.getElementById('successScreen').classList.add('active');
                } else {
                    showToast(result.message || 'Terjadi kesalahan', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Penilaian';
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Penilaian';
            }
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
    </script>
</body>
</html>
