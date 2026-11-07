<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Klinik Bonamu') - Sistem Layanan Terintegrasi</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --bs-primary: #006b5f;
            --bs-primary-rgb: 0, 107, 95;
        }
        body {
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        .navbar-brand {
            font-weight: 700;
            color: #006b5f !important;
            letter-spacing: -0.5px;
        }
        .btn-primary {
            background-color: #006b5f;
            border-color: #006b5f;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #005249;
            border-color: #005249;
        }
        .badge-status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }
        footer {
            margin-top: auto;
        }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-white bg-white border-bottom shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex items-center gap-2" href="{{ url('/') }}">
                <i class="bi bi-hospital fs-4"></i>
                <span>Bonamu</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.jadwal.*') ? 'active fw-semibold text-primary' : '' }}" href="{{ route('admin.jadwal.index') }}">
                                    <i class="bi bi-calendar-week me-1"></i> Jadwal Dokter
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->isPasien())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('antrean.create') ? 'active fw-semibold text-primary' : '' }}" href="{{ route('antrean.create') }}">
                                    <i class="bi bi-ticket-perforated me-1"></i> Ambil Antrean
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('antrean.index') ? 'active fw-semibold text-primary' : '' }}" href="{{ route('antrean.index') }}">
                                    <i class="bi bi-clock-history me-1"></i> Antrean Saya
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('konsultasi.create') }}">
                                    <i class="bi bi-chat-dots me-1"></i> Konsultasi Online
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->isDokter())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('dokter.konsultasi.index') }}">
                                    <i class="bi bi-chat-square-text me-1"></i> Konsultasi Pasien
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
                <ul class="navbar-nav ms-auto align-items-center">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle fs-5"></i>
                                <span>{{ auth()->user()->name }}</span>
                                <span class="badge bg-secondary-subtle text-secondary text-uppercase ms-1">{{ auth()->user()->role }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                            <i class="bi bi-box-arrow-right"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Masuk</a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-primary btn-sm px-3" href="{{ route('register') }}">Daftar Pasien</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
            {{-- Flash Alert Sukses --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Flash Alert Error --}}
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <div class="d-flex align-items-center mb-1">
                        <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                        <strong>Terdapat beberapa kesalahan:</strong>
                    </div>
                    <ul class="mb-0 ps-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-top py-3 text-center text-muted small">
        <div class="container">
            &copy; {{ date('Y') }} Klinik Bona Mitra Keluarga (Bonamu) — PPL Kelompok AB
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>

