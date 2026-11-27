<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Bonamu</title>

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
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            border: 1px solid #e8ecf1;
            width: 100%;
            max-width: 440px;
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

<div class="login-card">
    <div class="p-4 p-md-5">
        {{-- Logo Brand --}}
        <div class="text-center mb-4">
            <div class="brand-logo-icon mb-2">
                <i class="bi bi-hospital"></i>
            </div>
            <h4 class="fw-bold text-dark mb-0">Bonamu</h4>
            <small class="text-muted">Manajemen antrean &amp; informasi layanan</small>
        </div>

        <h5 class="fw-bold text-dark text-center mb-1">Masuk ke Akun Anda</h5>
        <p class="text-muted small text-center mb-4">Gunakan nomor telepon dan password terdaftar</p>

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm rounded-3 py-2 small mb-3">
                <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 py-2 small mb-3">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Nomor Telepon</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-telephone"></i></span>
                    <input type="text" name="no_hp" id="inputNoHp" value="{{ old('no_hp') }}" class="form-control" placeholder="Contoh: 081234567890" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                    <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Masukkan password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-app-primary w-100 mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>

        {{-- 1-Click Auto Fill untuk Pengujian --}}
        <div class="border-top pt-3 mt-3">
            <small class="text-muted d-block text-center mb-2" style="font-size: 0.75rem;">Uji Coba Cepat (Klik untuk isi otomatis):</small>
            <div class="d-flex justify-content-center gap-1 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0" style="font-size: 0.75rem;" onclick="isiAkun('081234567890', 'password')">Admin</button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0" style="font-size: 0.75rem;" onclick="isiAkun('081200000001', 'password')">dr. Andi</button>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0" style="font-size: 0.75rem;" onclick="isiAkun('081299999999', 'password')">Pasien</button>
            </div>
        </div>

        <div class="text-center mt-4 pt-2">
            <small class="text-muted">
                Belum punya akun pasien?
                <a href="{{ url('/register') }}" class="fw-semibold text-decoration-none" style="color: #005954;">Daftar sekarang</a>
            </small>
            <div class="mt-2">
                <a href="{{ url('/') }}" class="small text-muted text-decoration-underline">&larr; Kembali ke Portal</a>
            </div>
        </div>
    </div>
</div>

<script>
    function isiAkun(noHp, pass) {
        document.getElementById('inputNoHp').value = noHp;
        document.getElementById('inputPassword').value = pass;
    }
</script>

</body>
</html>
