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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .rating-card {
            background: white;
            border-radius: 24px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .rating-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .rating-header h2 {
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .petugas-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .petugas-info .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid rgba(255,255,255,0.3);
            margin-bottom: 10px;
        }
        
        .petugas-info h4 {
            margin: 0;
            font-size: 1.2rem;
        }
        
        .petugas-info p {
            margin: 5px 0 0;
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .rating-category {
            margin-bottom: 25px;
        }
        
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
        .star-rating .star.active {
            color: #fbbf24;
            transform: scale(1.1);
        }
        
        .star-rating .star:hover ~ .star {
            color: #e2e8f0 !important;
        }
        
        .komentar-box {
            margin-top: 25px;
        }
        
        .komentar-box textarea {
            width: 100%;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px;
            font-size: 0.95rem;
            resize: none;
            transition: border-color 0.3s;
        }
        
        .komentar-box textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Toast */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .toast-message {
            background: #10b981;
            color: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        .success-screen {
            text-align: center;
            display: none;
        }
        
        .success-screen i {
            font-size: 5rem;
            color: #10b981;
            margin-bottom: 20px;
        }
        
        .success-screen h3 {
            color: #1e293b;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="rating-card">
        <div class="rating-form-container">
            <div class="rating-header">
                <h2><i class="fas fa-star"></i> Penilaian Layanan</h2>
                <p class="text-muted">Berikan penilaian untuk pelayanan yang Anda terima</p>
            </div>
            
            <div class="petugas-info">
                @if($officer->foto && Storage::disk('public')->exists($officer->foto))
                    <img src="{{ asset('storage/' . $officer->foto) }}" alt="{{ $officer->name }}" class="avatar">
                @else
                    <div class="avatar" style="background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 600;">
                        {{ strtoupper(substr($officer->name, 0, 1)) }}
                    </div>
                @endif
                <h4>{{ $officer->name }}</h4>
                <p>Petugas Pelayanan</p>
            </div>
            
            <form id="ratingForm">
                <div class="rating-category">
                    <label><i class="fas fa-smile"></i> Keramahan</label>
                    <div class="star-rating" data-category="keramahan">
                        <i class="fas fa-star star" data-value="1"></i>
                        <i class="fas fa-star star" data-value="2"></i>
                        <i class="fas fa-star star" data-value="3"></i>
                        <i class="fas fa-star star" data-value="4"></i>
                        <i class="fas fa-star star" data-value="5"></i>
                    </div>
                    <input type="hidden" name="rating_keramahan" id="rating_keramahan" value="0">
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
                    <input type="hidden" name="rating_kecepatan" id="rating_kecepatan" value="0">
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
                    <input type="hidden" name="rating_pengetahuan" id="rating_pengetahuan" value="0">
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
                    <input type="hidden" name="rating_keseluruhan" id="rating_keseluruhan" value="0">
                </div>
                
                <div class="komentar-box">
                    <label class="form-label fw-semibold"><i class="fas fa-comment"></i> Komentar (opsional)</label>
                    <textarea name="komentar" id="komentar" rows="3" placeholder="Tuliskan komentar atau saran Anda..."></textarea>
                </div>
                
                <button type="submit" class="submit-btn" id="submitBtn">
                    <i class="fas fa-paper-plane"></i> Kirim Penilaian
                </button>
            </form>
        </div>
        
        <div class="success-screen" id="successScreen">
            <i class="fas fa-check-circle"></i>
            <h3>Terima Kasih!</h3>
            <p class="text-muted">Penilaian Anda telah kami terima.<br>Terima kasih telah menggunakan layanan kami.</p>
        </div>
    </div>
    
    <div class="toast-container" id="toastContainer"></div>

    <script>
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
                        if (index < value) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
                
                star.addEventListener('mouseenter', () => {
                    const value = parseInt(star.dataset.value);
                    stars.forEach((s, index) => {
                        if (index < value) {
                            s.style.color = '#fbbf24';
                        } else {
                            s.style.color = '#e2e8f0';
                        }
                    });
                });
            });
            
            container.addEventListener('mouseleave', () => {
                const currentValue = parseInt(input.value);
                stars.forEach((s, index) => {
                    if (s.classList.contains('active')) {
                        s.style.color = '#fbbf24';
                    } else {
                        s.style.color = '#e2e8f0';
                    }
                });
            });
        });
        
        // Form submission
        document.getElementById('ratingForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const data = {
                rating_keramahan: document.getElementById('rating_keramahan').value,
                rating_kecepatan: document.getElementById('rating_kecepatan').value,
                rating_pengetahuan: document.getElementById('rating_pengetahuan').value,
                rating_keseluruhan: document.getElementById('rating_keseluruhan').value,
                komentar: document.getElementById('komentar').value
            };
            
            // Validation
            if (data.rating_keramahan == 0 || data.rating_kecepatan == 0 || 
                data.rating_pengetahuan == 0 || data.rating_keseluruhan == 0) {
                showToast('Mohon isi semua kategori penilaian', 'error');
                return;
            }
            
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
            
            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.querySelector('.rating-form-container').style.display = 'none';
                    document.getElementById('successScreen').style.display = 'block';
                } else {
                    showToast(result.error || 'Terjadi kesalahan', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Penilaian';
                }
            } catch (error) {
                showToast('Terjadi kesalahan jaringan', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Kirim Penilaian';
            }
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
    </script>
</body>
</html>
