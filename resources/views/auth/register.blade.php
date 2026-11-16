<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pasien Baru — Bonamu</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --app-primary: #005954;
            --app-primary-hover: #004743;
            --app-bg: #f5f8fa;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--app-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .register-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            border: 1px solid #e8ecf1;
            width: 100%;
            max-width: 480px;
            overflow: hidden;
        }
        .brand-logo-icon {
            width: 42px;
            height: 42px;
            background-color: var(--app-primary);
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
        }
        .btn-app-primary {
            background-color: var(--app-primary);
            color: white;
            border-radius: 10px;
            padding: 0.75rem;
            font-weight: 600;
            border: none;
            transition: all 0.2s;
        }
        .btn-app-primary:hover {
            background-color: var(--app-primary-hover);
            color: white;
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="p-4 p-md-5">
        {{-- Logo Brand --}}
        <div class="text-center mb-4">
            <div class="brand-logo-icon mb-2">
                <i class="bi bi-hospital"></i>
            </div>
            <h4 class="fw-bold text-dark mb-0">Bonamu</h4>
            <small class="text-muted">Manajemen antrean &amp; informasi layanan</small>
        </div>

        <h5 class="fw-bold text-dark text-center mb-1">Registrasi Pasien Baru</h5>
        <p class="text-muted small text-center mb-4">Daftarkan akun pasien untuk mulai mengambil antrean online</p>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 py-2 small mb-3">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/register') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Masukkan nama lengkap sesuai KTP" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Alamat Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Contoh: pasien@gmail.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Nomor Telepon / WhatsApp</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                    <input type="text" name="no_hp" value="{{ old('no_hp') }}" class="form-control" placeholder="Contoh: 081234567890" required>
                </div>
            </div>

            <div class="row g-2 mb-4">
                <div class="col-sm-6">
                    <label class="form-label small fw-semibold text-secondary">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter" required>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="form-label small fw-semibold text-secondary">Konfirmasi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-check2-circle"></i></span>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-app-primary w-100 mb-3">
                <i class="bi bi-person-check me-1"></i> Daftar Sekarang
            </button>
        </form>

        <div class="text-center mt-3">
            <small class="text-muted">
                Sudah memiliki akun?
                <a href="{{ url('/login') }}" class="fw-semibold text-decoration-none" style="color: #005954;">Masuk di sini</a>
            </small>
            <div class="mt-2">
                <a href="{{ url('/') }}" class="small text-muted text-decoration-underline">&larr; Kembali ke Portal</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
