@extends('layouts.app')

@section('title', 'Dashboard Petugas')
@section('page-title', 'Dashboard Petugas')

@section('content')
@php
    $statusLabels = [
        'waiting'   => ['Menunggu', 'bg-yellow-100 text-yellow-800'],
        'called'    => ['Dipanggil', 'bg-blue-100 text-blue-800'],
        'serving'   => ['Dilayani', 'bg-purple-100 text-purple-800'],
        'completed' => ['Selesai', 'bg-green-100 text-green-800'],
        'skipped'   => ['Dilewati', 'bg-red-100 text-red-800'],
    ];
@endphp

@if (session('success'))
    <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800">{{ session('error') }}</div>
@endif

{{-- Ringkasan hari ini --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    @foreach ($statusLabels as $key => [$label, $class])
        <div class="rounded-xl p-4 text-center shadow {{ $class }}">
            <p class="text-3xl font-bold">{{ $statusCounts[$key] ?? 0 }}</p>
            <p class="text-sm">{{ $label }}</p>
        </div>
    @endforeach
</div>

<p class="text-sm text-gray-500 mb-4">
    Antrean tanggal {{ now()->format('d/m/Y') }} (halaman refresh otomatis tiap 30 detik)
</p>

{{-- Antrean per jadwal dokter --}}
@forelse ($schedules as $schedule)
    <div class="bg-white rounded-xl shadow p-5 mb-6">
        <div class="flex flex-wrap justify-between items-start mb-4">
            <div>
                <h3 class="font-semibold text-lg">{{ $schedule->doctor?->user?->name ?? 'Dokter' }}</h3>
                <p class="text-sm text-gray-500">
                    {{ $schedule->poli?->nama_poli }} &middot; {{ $schedule->poli?->klinikCabang?->nama_cabang }}
                </p>
            </div>
            <div class="text-sm text-gray-600 text-right">
                <p>{{ ucfirst($schedule->hari) }}, {{ substr($schedule->jam_mulai, 0, 5) }} - {{ substr($schedule->jam_selesai, 0, 5) }}</p>
                <p>Kuota terisi: {{ $schedule->queues->count() }} / {{ $schedule->kuota }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="p-3">No</th>
                        <th class="p-3">Pasien</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($schedule->queues as $q)
                        @php [$label, $class] = $statusLabels[$q->status] ?? [$q->status, 'bg-gray-100 text-gray-700']; @endphp
                        <tr class="border-t">
                            <td class="p-3 font-bold text-lg">{{ $q->nomor_antrean }}</td>
                            <td class="p-3">{{ $q->patientProfile?->user?->name ?? '-' }}</td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded-full text-xs {{ $class }}">{{ $label }}</span>
                            </td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-2">
                                    @php
                                        $actions = match ($q->status) {
                                            'waiting' => [['call', 'Panggil', 'bg-blue-600'], ['skip', 'Lewati', 'bg-red-500']],
                                            'called'  => [['serve', 'Layani', 'bg-purple-600'], ['skip', 'Lewati', 'bg-red-500']],
                                            'serving' => [['complete', 'Selesai', 'bg-green-600']],
                                            default   => [],
                                        };
                                    @endphp
                                    @foreach ($actions as [$action, $text, $color])
                                        <form action="{{ route('petugas.antrean.status', $q) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="action" value="{{ $action }}">
                                            <button type="submit" class="{{ $color }} text-white px-3 py-1 rounded-lg text-xs">
                                                {{ $text }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="bg-white rounded-xl shadow p-8 text-center text-gray-500">
        Belum ada antrean hari ini.
    </div>
@endforelse

<script>
    setTimeout(() => location.reload(), 30000);
</script>
@endsection