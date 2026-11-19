<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Bonamu</title>
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "surface-variant": "#d3e4fe", "tertiary-fixed": "#c2ebe1", "surface-bright": "#f8f9ff",
                        "on-surface": "#0b1c30", "on-error-container": "#93000a", "outline-variant": "#bec8c7",
                        "primary-container": "#0f5c5a", "error-container": "#ffdad6", "surface-container-highest": "#d3e4fe",
                        "secondary-container": "#6df5e1", "primary": "#004342", "surface-container-lowest": "#ffffff",
                        "surface": "#f8f9ff", "background": "#f8f9ff", "on-primary": "#ffffff",
                        "surface-container": "#e5eeff", "surface-container-low": "#eff4ff",
                        "on-surface-variant": "#3f4948", "secondary-fixed": "#71f8e4", "outline": "#6f7978",
                        "error": "#ba1a1a", "secondary": "#006b5f"
                    },
                    borderRadius: { DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px" },
                    spacing: { "space-md": "1rem", "margin-desktop": "2.5rem", "space-sm": "0.5rem", "space-xs": "0.25rem" },
                    fontFamily: {
                        "headline-lg": ["Inter"], "headline-md": ["Inter"], "headline-sm": ["Inter"],
                        "label-uppercase": ["Inter"], "body-default": ["Inter"], "body-semibold": ["Inter"],
                        "body-sm": ["Inter"], "badge-status": ["Inter"]
                    },
                    fontSize: {
                        "headline-lg": ["32px", { lineHeight: "40px", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "headline-md": ["22px", { lineHeight: "28px", fontWeight: "600" }],
                        "headline-sm": ["18px", { lineHeight: "24px", fontWeight: "600" }],
                        "label-uppercase": ["11px", { lineHeight: "14px", letterSpacing: "0.08em", fontWeight: "700" }],
                        "body-default": ["14px", { lineHeight: "20px", fontWeight: "400" }],
                        "body-semibold": ["14px", { lineHeight: "20px", fontWeight: "600" }],
                        "body-sm": ["12px", { lineHeight: "18px", fontWeight: "400" }],
                        "badge-status": ["11px", { lineHeight: "14px", fontWeight: "600" }]
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background: linear-gradient(180deg, #EEF5F6 0%, #FFFFFF 650px, #EEF5F6 100%); min-height: 100vh; color: #0b1c30; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; vertical-align: middle; line-height: 1; }
        .font-mono-code { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="text-on-surface antialiased flex flex-col min-h-screen">

<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-outline-variant/30 shadow-sm">
    <div class="w-full px-space-md md:px-margin-desktop py-space-sm flex items-center justify-between mx-auto max-w-[1280px]">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-primary-container flex items-center justify-center text-on-primary shadow-sm">
                <span class="material-symbols-outlined text-[24px]" style="font-variation-settings: 'FILL' 1;">add</span>
            </div>
            <div class="flex flex-col">
                <span class="font-headline-sm text-headline-sm text-primary leading-tight tracking-tight">Bonamu</span>
                <span class="font-body-sm text-body-sm text-outline">Antrean &amp; layanan</span>
            </div>
        </div>

        <nav class="hidden md:flex items-center gap-1">
            @if (auth()->user()->role === 'dokter')
                <a href="{{ route('dokter.konsultasi.index') }}" class="bg-secondary-container text-primary rounded-full px-space-md py-space-xs font-body-semibold text-body-default">Konsultasi Masuk</a>
            @else
                <a href="#" class="text-on-surface-variant hover:text-primary px-space-md py-space-xs text-body-default rounded-full hover:bg-surface-container-low transition-colors">Ambil Antrean</a>
                <a href="{{ route('konsultasi.create') }}" class="bg-secondary-container text-primary rounded-full px-space-md py-space-xs font-body-semibold text-body-default">Konsultasi Online</a>
                <a href="#" class="text-on-surface-variant hover:text-primary px-space-md py-space-xs text-body-default rounded-full hover:bg-surface-container-low transition-colors">Status Antrean</a>
                <a href="#" class="text-on-surface-variant hover:text-primary px-space-md py-space-xs text-body-default rounded-full hover:bg-surface-container-low transition-colors">Riwayat</a>
                <a href="#" class="text-on-surface-variant hover:text-primary px-space-md py-space-xs text-body-default rounded-full hover:bg-surface-container-low transition-colors">Notifikasi</a>
            @endif
        </nav>

        <div class="flex items-center gap-3">
            <button type="button" class="w-9 h-9 rounded-full flex items-center justify-center text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-all">
                <span class="material-symbols-outlined text-[20px]">dark_mode</span>
            </button>
            <div class="flex items-center gap-2 pl-2 pr-3 py-1 rounded-full bg-surface-container-low border border-outline-variant/40">
                <div class="w-7 h-7 rounded-full bg-primary-container text-on-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[16px]">person</span>
                </div>

                @if (app()->environment('local') && \Illuminate\Support\Facades\Route::has('dev.ganti-akun'))
                    {{-- SEMENTARA: dropdown ganti akun, khusus uji lokal. HAPUS sebelum commit/push. --}}
                    @php
                        // Hanya akun dengan role yang sama dengan yang sedang login
                        $akunUji = \App\Models\User::where('role', auth()->user()->role)
                            ->when(auth()->user()->role === 'dokter', fn ($q) => $q->where('email', '!=', 'dokter.budi@test.com'))
                            ->orderBy('name')
                            ->get();
                    @endphp
                    <select onchange="window.location = '{{ route('dev.ganti-akun') }}?email=' + encodeURIComponent(this.value)"
                        class="font-body-semibold text-body-default text-on-surface bg-transparent border-0 py-0 pl-0 pr-7 focus:ring-0 cursor-pointer">
                        @foreach ($akunUji as $akun)
                            <option value="{{ $akun->email }}" @selected((int) $akun->id === (int) auth()->id())>{{ $akun->name }}</option>
                        @endforeach
                    </select>
                @else
                    <span class="font-body-semibold text-body-default text-on-surface">{{ auth()->user()->name }}</span>
                @endif

                <span class="font-badge-status text-badge-status bg-secondary-fixed/50 text-primary px-2 py-0.5 rounded-full uppercase tracking-wider">{{ auth()->user()->role }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="border border-outline-variant/60 text-on-surface-variant hover:text-primary hover:bg-surface-container-low rounded-lg px-3 py-1.5 font-body-semibold text-body-sm transition-all">Keluar</button>
            </form>
        </div>
    </div>
</header>

<main class="w-full max-w-[1100px] mx-auto px-4 py-8 flex-grow">
    @yield('content')
</main>

<footer class="w-full max-w-[1100px] mx-auto px-4 py-8 text-center text-outline font-body-sm text-body-sm">
    <div class="flex items-center justify-center gap-6 mb-2">
        <a class="hover:text-primary transition-colors" href="#">Bantuan Darurat 119</a>
        <span>•</span>
        <a class="hover:text-primary transition-colors" href="#">Syarat &amp; Ketentuan</a>
        <span>•</span>
        <a class="hover:text-primary transition-colors" href="#">Kebijakan Privasi</a>
    </div>
    <p>© 2026 Bonamu</p>
</footer>

@stack('scripts')
</body>
</html>