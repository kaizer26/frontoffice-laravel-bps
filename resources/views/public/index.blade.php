<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petugas Bertugas - BPS</title>
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
        }
        
        .container {
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 40px;
        }
        
        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .date-display {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            border-radius: 30px;
            display: inline-block;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }
        
        .shift-section {
            margin-bottom: 40px;
        }
        
        .shift-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .shift-badge {
            background: rgba(255,255,255,0.3);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .petugas-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        
        .petugas-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            text-align: center;
            width: 320px;
            max-width: 100%;
        }
        
        .petugas-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        
        .avatar-wrapper {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        
        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 4px solid #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }
        
        .avatar-wrapper:hover .avatar {
            transform: scale(1.15);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
            border-color: #764ba2;
        }
        
        .avatar-wrapper:hover::after {
            content: '\f00e';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            bottom: 18px;
            right: -5px;
            background: #667eea;
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        .avatar-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: white;
            font-size: 2.5rem;
            font-weight: 600;
        }
        
        /* Photo Modal */
        .photo-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.85);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .photo-modal.active {
            display: flex;
        }
        
        .photo-modal-content {
            text-align: center;
            animation: zoomIn 0.3s ease;
        }
        
        @keyframes zoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        
        .photo-modal-img {
            max-width: 90vw;
            max-height: 70vh;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
            border: 5px solid white;
        }
        
        .photo-modal-name {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            margin-top: 20px;
        }
        
        .photo-modal-jabatan {
            color: rgba(255,255,255,0.7);
            font-size: 1rem;
            margin-top: 5px;
        }
        
        .photo-modal-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.3s;
        }
        
        .photo-modal-close:hover {
            opacity: 1;
        }
        
        .petugas-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }
        
        .petugas-nip {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .petugas-jabatan {
            background: #f1f5f9;
            color: #475569;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.8rem;
            display: inline-block;
        }
        
        .empty-state {
            text-align: center;
            color: white;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.7;
        }
        
        .login-link {
            text-align: center;
            margin-top: 40px;
        }
        
        .login-link a {
            color: white;
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 15px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .login-link a:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8rem;
            }
            .petugas-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> Petugas Bertugas Hari Ini</h1>
            <div class="date-display">
                <i class="fas fa-calendar-day"></i>
                {{ $today->translatedFormat('l, d F Y') }}
            </div>
        </div>

        @if($jadwal->isEmpty())
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>Tidak ada petugas bertugas hari ini</h3>
                <p>Silakan hubungi admin untuk informasi lebih lanjut</p>
            </div>
        @else
            @foreach($jadwal as $shift => $officers)
                @php
                    $isActive = \App\Models\SystemSetting::get('shift_' . strtolower($shift) . '_active', 'true') === 'true';
                @endphp
                @if($isActive)
                <div class="shift-section">
                    <h3 class="shift-title">
                        <i class="fas fa-clock"></i> 
                        {{ $shift }} 
                        @php
                            $isFriday = $today->isFriday();
                            $prefix = $isFriday ? 'shift_friday_' : 'shift_';
                            $lowShift = strtolower($shift);
                            
                            $defaultStart = '00:00';
                            $defaultEnd = '00:00';
                            if ($lowShift == 'pagi') { 
                                $defaultStart = '07:30'; 
                                $defaultEnd = $isFriday ? '11:30' : '12:00'; 
                            }
                            elseif ($lowShift == 'siang') { 
                                $defaultStart = $isFriday ? '13:30' : '12:00'; 
                                $defaultEnd = '14:30'; 
                            }
                            elseif ($lowShift == 'sore') { 
                                $defaultStart = '14:30'; 
                                $defaultEnd = $isFriday ? '16:30' : '16:00'; 
                            }
                        @endphp
                        <span class="small opacity-75">
                            ({{ \App\Models\SystemSetting::get($prefix . $lowShift . '_start', $defaultStart) }} - {{ \App\Models\SystemSetting::get($prefix . $lowShift . '_end', $defaultEnd) }})
                        </span>
                        <span class="shift-badge">{{ count($officers) }} Petugas</span>
                    </h3>
                    <div class="petugas-grid">
                        @foreach($officers as $schedule)
                            <div class="petugas-card">
                                @if($schedule->user->foto && Storage::disk('public')->exists($schedule->user->foto))
                                    <div class="avatar-wrapper" onclick="showPhoto('{{ asset('storage/' . $schedule->user->foto) }}', '{{ $schedule->user->name }}', '{{ $schedule->user->pegawai->jabatan ?? '' }}')">
                                        <img src="{{ asset('storage/' . $schedule->user->foto) }}" alt="{{ $schedule->user->name }}" class="avatar">
                                    </div>
                                @else
                                    <div class="avatar-placeholder">
                                        {{ strtoupper(substr($schedule->user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <h5 class="petugas-name">{{ $schedule->user->name }}</h5>
                                <p class="petugas-nip">
                                    <i class="fas fa-id-badge"></i> 
                                    {{ $schedule->user->nip_bps ?? 'NIP: -' }}
                                </p>
                                @if($schedule->user->pegawai && $schedule->user->pegawai->jabatan)
                                    <span class="petugas-jabatan">{{ $schedule->user->pegawai->jabatan }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        @endif

        <div class="login-link">
            <a href="{{ route('rating.public') }}" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);">
                <i class="fas fa-star"></i> Berikan Penilaian
            </a>
            <a href="{{ route('login') }}">
                <i class="fas fa-sign-in-alt"></i> Login Petugas
            </a>
        </div>
    </div>

    <!-- Photo Modal -->
    <div class="photo-modal" id="photoModal" onclick="closePhoto()">
        <span class="photo-modal-close">&times;</span>
        <div class="photo-modal-content" onclick="event.stopPropagation()">
            <img src="" alt="" class="photo-modal-img" id="modalImg">
            <div class="photo-modal-name" id="modalName"></div>
            <div class="photo-modal-jabatan" id="modalJabatan"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showPhoto(src, name, jabatan) {
            document.getElementById('modalImg').src = src;
            document.getElementById('modalName').textContent = name;
            document.getElementById('modalJabatan').textContent = jabatan;
            document.getElementById('photoModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closePhoto() {
            document.getElementById('photoModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        // Close on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePhoto();
        });
    </script>
</body>
</html>
