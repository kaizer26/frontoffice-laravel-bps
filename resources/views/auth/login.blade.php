<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Front Office BPS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --secondary: #fbbf24;
            --bg-dark: #0f172a;
            --success: #10b981;
            --danger: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--bg-dark) 0%, #1e3a5f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 450px;
            width: 100%;
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .login-header i {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .login-header h1 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #1e293b;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .input-group {
            display: flex;
            align-items: stretch;
        }

        .input-group .form-control {
            border-radius: 10px 0 0 10px;
        }

        .input-group-text {
            background: #f1f5f9;
            border: 2px solid #e2e8f0;
            border-left: none;
            padding: 12px 16px;
            border-radius: 0 10px 10px 0;
            color: #64748b;
            font-size: 0.9rem;
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(30, 64, 175, 0.3);
        }

        .btn-secondary {
            background: #f8fafc;
            color: #64748b;
            border: 2px solid #e2e8f0;
            margin-top: 15px;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .hint {
            color: #64748b;
            font-size: 0.8rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-building-columns"></i>
            <h1>Front Office BPS</h1>
            <p>Sistem Manajemen Pelayanan Terpadu</p>
        </div>
        
        <div class="login-body">
            @if ($errors->any())
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email / Username</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="email" placeholder="username" required autofocus>
                        <span class="input-group-text">@bps.go.id</span>
                    </div>
                    <p class="hint">Masukkan username tanpa @bps.go.id</p>
                </div>
                
                @php
                    $passwordRequired = \App\Models\SystemSetting::get('login_password_required', 'true') === 'true';
                @endphp

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" 
                        placeholder="{{ $passwordRequired ? 'Masukkan password' : 'Password (Opsional - Mode Cepat Aktif)' }}" 
                        {{ $passwordRequired ? 'required' : '' }}>
                    @if(!$passwordRequired)
                        <p class="hint text-success"><i class="fas fa-bolt"></i> Login Cepat Aktif: Password tidak wajib diisi.</p>
                    @endif
                </div>
                
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" name="remember">
                        <span style="color:#64748b;font-size:0.9rem;">Ingat saya</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
                
                <a href="{{ route('public') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Halaman Publik
                </a>
            </form>
        </div>
    </div>
</body>
</html>
