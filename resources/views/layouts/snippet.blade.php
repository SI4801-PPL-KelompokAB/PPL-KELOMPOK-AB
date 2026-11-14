{{-- Ganti isi <nav class="space-y-3"> ... </nav> di layouts/app.blade.php dengan ini --}}
@php
    $role = auth()->user()->role;
    $menuBase = 'block p-3 rounded-lg hover:bg-blue-800';
    $menuActive = 'bg-blue-800';
@endphp

<nav class="space-y-3">
    <a href="{{ route('dashboard') }}"
       class="{{ $menuBase }} {{ request()->routeIs('dashboard') ? $menuActive : '' }}">
        Dashboard
    </a>

    @if ($role === 'admin')
        {{-- Tambahkan menu admin di sini, contoh: --}}
        {{-- <a href="{{ route('admin.poli.index') }}" class="{{ $menuBase }}">Kelola Poli</a> --}}
    @endif

    @if ($role === 'petugas')
        {{-- Tambahkan menu petugas di sini --}}
    @endif

    @if ($role === 'dokter')
        <a href="{{ route('dokter.konsultasi.index') }}"
           class="{{ $menuBase }} {{ request()->routeIs('dokter.konsultasi.*') ? $menuActive : '' }}">
            Konsultasi
        </a>
    @endif
</nav>