@extends('layouts.konsultasi')

@section('title', 'Daftar Akun Admin')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-headline-md font-headline-md text-on-surface">Daftar akun dokter &amp; admin</h1>
            <p class="text-body-sm text-outline">Akun yang dibuat admin via PBI03.</p>
        </div>
        <a href="{{ route('admin.akun.create') }}" dusk="tombol-buat-akun" class="bg-primary-container hover:bg-primary text-on-primary px-4 py-2 rounded-xl font-body-semibold text-body-default">Buat akun</a>
    </div>

    @if (session('success'))
        <div class="bg-[#DCFCE7] text-[#15803D] text-body-default rounded-xl px-4 py-3">{{ session('success') }}</div>
    @endif

    <section class="bg-surface-container-lowest rounded-2xl border border-outline-variant/40 shadow-sm p-6" style="border-top: 4px solid #0F5C5A;">
        <div class="space-y-3">
            @forelse ($akuns as $akun)
                <div dusk="akun-{{ $akun->id }}" class="p-4 rounded-xl border border-outline-variant/60 flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <p class="font-body-semibold text-on-surface">{{ $akun->name }} <span class="font-badge-status text-badge-status bg-secondary-fixed/50 text-primary px-2 py-0.5 rounded-full uppercase tracking-wider">{{ $akun->role }}</span></p>
                        <p class="text-body-sm text-outline">{{ $akun->email }} · {{ $akun->no_hp }} · {{ $akun->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-body-default text-outline">Belum ada akun dokter/admin.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
