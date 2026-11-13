<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - Mengelola Data Poli') — Bonamu</title>

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --app-primary: #005954;
            --app-primary-hover: #004743;
            --app-primary-light: #d1f4ed;
            --app-bg: #f5f8fa;
            --card-bg: #ffffff;
            --text-color: #2d3748;
        }

        body.dark-mode {
            --app-bg: #121820;
            --card-bg: #1a222d;
            --text-color: #e2e8f0;
            background-color: var(--app-bg) !important;
            color: var(--text-color) !important;
        }

        body.dark-mode .app-header,
        body.dark-mode .app-footer,
        body.dark-mode .card-mockup {
            background-color: var(--card-bg) !important;
            border-color: #2d3748 !important;
            color: var(--text-color) !important;
        }

        body.dark-mode .text-dark {
            color: #f1f5f9 !important;
        }

        body.dark-mode .table-mockup th,
        body.dark-mode .table-mockup td {
            background-color: var(--card-bg) !important;
            border-color: #2d3748 !important;
            color: var(--text-color) !important;
        }

        body.dark-mode .form-control {
            background-color: #242e3d;
            border-color: #374151;
            color: #f1f5f9;
        }

        body.dark-mode .badge-kode {
            background-color: #242e3d;
            border-color: #374151;
            color: #cbd5e1;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--app-bg);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* Top Header Mockup Style */
        .app-header {
            background: #ffffff;
            border-bottom: 1px solid #e9ecef;
            padding: 0.75rem 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .brand-logo-icon {
            width: 36px;
            height: 36px;
            background-color: var(--app-primary);
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
        }

        .nav-pill-item {
            color: #4a5568;
            font-size: 0.875rem;
            font-weight: 500;
            padding: 0.45rem 1rem;
            border-radius: 50rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .nav-pill-item:hover {
            color: var(--app-primary);
            background-color: #f1f5f9;
        }

        .nav-pill-item.active {
            background-color: var(--app-primary-light);
            color: var(--app-primary);
            font-weight: 600;
        }

        .user-badge {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 50rem;
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-pill {
            background-color: #fef3c7;
            color: #92400e;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 50rem;
            letter-spacing: 0.05em;
        }

        .btn-keluar {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 50rem;
            padding: 0.3rem 1rem;
            font-size: 0.85rem;
            color: #4a5568;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-keluar:hover {
            background: #f8fafc;
            color: #e53e3e;
            border-color: #fecaca;
        }

        /* Card & Table Mockup Style */
        .card-mockup {
            background: #ffffff;
            border: 1px solid #e8ecf1;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            overflow: hidden;
        }

        .card-mockup-header {
            padding: 1.25rem 1.5rem 0.5rem 1.5rem;
            background: transparent;
            border-bottom: none;
        }

        .card-mockup-body {
            padding: 1.25rem 1.5rem 1.5rem 1.5rem;
        }

        .btn-app-primary {
            background-color: var(--app-primary);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 0.65rem 1.25rem;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-app-primary:hover {
            background-color: var(--app-primary-hover);
            color: #ffffff;
        }

        .table-mockup {
            margin-bottom: 0;
        }

        .table-mockup th {
            font-size: 0.75rem;
            font-weight: 700;
            color: #718096;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 0.85rem 1rem;
            border-bottom: 1px solid #edf2f7;
            background: transparent;
        }

        .table-mockup td {
            font-size: 0.875rem;
            padding: 0.9rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .badge-kode {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            padding: 0.25rem 0.55rem;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-dot-active {
            color: #10b981;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status-dot-inactive {
            color: #94a3b8;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .btn-action-outline {
            border: 1px solid #e2e8f0;
            background: white;
            color: #4a5568;
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.15s ease;
        }

        .btn-action-outline:hover {
            background: #f8fafc;
            color: #1a202c;
            border-color: #cbd5e1;
        }

        .btn-action-delete {
            border: 1px solid #fecaca;
            background: white;
            color: #dc2626;
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            transition: all 0.15s ease;
        }

        .btn-action-delete:hover {
            background: #fee2e2;
            color: #b91c1c;
        }

        .btn-action-activate {
            border: 1px solid #bbf7d0;
            background: white;
            color: #16a34a;
            font-size: 0.8rem;
            padding: 0.25rem 0.75rem;
            border-radius: 8px;
            transition: all 0.15s ease;
        }

        .btn-action-activate:hover {
            background: #dcfce7;
            color: #15803d;
        }

        .app-footer {
            margin-top: auto;
            padding: 1.5rem 0;
            font-size: 0.8rem;
            color: #94a3b8;
            border-top: 1px solid #e9ecef;
            background: #ffffff;
        }
    </style>
    @stack('styles')
</head>
<body>

{{-- Header sesuai mockup --}}
<header class="app-header">
    <div class="container-xl d-flex align-items-center justify-content-between">
        {{-- Logo Brand --}}
        <a href="{{ route('admin.poli.index') }}" class="d-flex align-items-center gap-3 text-decoration-none">
            <div class="brand-logo-icon">
                <i class="bi bi-hospital"></i>
            </div>
            <div>
                <div class="fw-bold text-dark lh-sm" style="font-size: 1.15rem; letter-spacing: -0.02em;">Bonamu</div>
                <div class="text-muted" style="font-size: 0.75rem;">Manajemen antrean &amp; informasi layanan</div>
            </div>
        </a>

        {{-- Navigasi Menu --}}
        <nav class="d-none d-md-flex align-items-center gap-2">
            <a href="javascript:void(0)" onclick="tampilkanInfoModul('Daftar Antrean', 'PBI-14')" class="nav-pill-item">Daftar Antrean</a>
            <a href="javascript:void(0)" onclick="tampilkanInfoModul('Input Manual', 'PBI-13')" class="nav-pill-item">Input Manual</a>
            <a href="{{ route('admin.poli.index') }}" class="nav-pill-item active">Poli</a>
            <a href="javascript:void(0)" onclick="tampilkanInfoModul('Jam Operasional Dokter', 'PBI-07')" class="nav-pill-item">Jam Operasional</a>
            <a href="javascript:void(0)" onclick="tampilkanInfoModul('Kuota Antrean Dokter', 'PBI-07')" class="nav-pill-item">Kuota</a>
        </nav>

        {{-- Profil & Action --}}
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btnDarkMode" class="btn btn-sm btn-light rounded-circle text-muted" title="Mode gelap/terang">
                <i class="bi bi-moon"></i>
            </button>

            @auth
                <div class="user-badge">
                    <span>{{ auth()->user()->name ?? 'Admin Bonamu' }}</span>
                    <span class="admin-pill">{{ strtoupper(auth()->user()->role ?? 'ADMIN') }}</span>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="d-inline m-0">
                    @csrf
                    <button type="submit" class="btn-keluar">Keluar</button>
                </form>
            @endauth
        </div>
    </div>
</header>

{{-- Sub-bar kecil --}}
<div class="container-xl py-2 d-flex justify-content-between align-items-center">
    <small class="text-muted" style="font-size: 0.8rem;">
        Master Data &rsaquo; <strong>Mengelola Data Poli (PBI-05)</strong>
    </small>
    <small class="badge bg-light text-secondary border px-2 py-1">
        Cabang: {{ auth()->user()->cabang->nama_cabang ?? 'Klinik Bonamu Pusat' }}
    </small>
</div>

{{-- Main Content --}}
<main class="container-xl mb-5 flex-grow-1">
    {{-- Pesan Flash Sukses --}}
    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert" dusk="alert-sukses">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                <div>{{ session('sukses') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Pesan Error Validasi --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-3" role="alert" dusk="alert-error">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                <div>
                    <strong>Terdapat kesalahan pada input:</strong>
                    <ul class="mb-0 mt-1 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</main>

{{-- Footer sesuai mockup --}}
<footer class="app-footer">
    <div class="container-xl d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <div>
            Sistem Manajemen Antrean Bonamu — pengelolaan antrean &amp; informasi layanan, bukan layanan medis.
        </div>
        <div>
            Papan antrean publik: <a href="javascript:void(0)" onclick="tampilkanInfoModul('Papan Antrean Publik', 'PBI-17')" class="text-decoration-underline text-muted">lihat papan</a>
        </div>
    </div>
</footer>

{{-- Bootstrap 5 JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Fitur Dark Mode Toggle
    const btnDarkMode = document.getElementById('btnDarkMode');
    if (btnDarkMode) {
        btnDarkMode.addEventListener('click', function () {
            document.body.classList.toggle('dark-mode');
            const icon = this.querySelector('i');
            if (document.body.classList.contains('dark-mode')) {
                icon.classList.remove('bi-moon');
                icon.classList.add('bi-sun');
            } else {
                icon.classList.remove('bi-sun');
                icon.classList.add('bi-moon');
            }
        });
    }

    // Modal/Toast interaktif jika menu modul tim lain diklik
    function tampilkanInfoModul(namaModul, kodePbi) {
        alert('Fitur ' + namaModul + ' (' + kodePbi + ') adalah tanggung jawab modul lain dan saat ini sedang dalam pengerjaan oleh anggota tim terkait.');
    }
</script>

@stack('scripts')
</body>
</html>
