@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page-title', 'Dashboard Admin')

@section('content')
@php
$statusLabels = [
'waiting' => ['Menunggu', 'bg-yellow-100 text-yellow-800'],
'called' => ['Dipanggil', 'bg-blue-100 text-blue-800'],
'serving' => ['Dilayani', 'bg-purple-100 text-purple-800'],
'completed' => ['Selesai', 'bg-green-100 text-green-800'],
'skipped' => ['Dilewati', 'bg-red-100 text-red-800'],
];
$maxChart = max(1, collect($chart)->max('total'));
@endphp

{{-- Kartu statistik --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Total Pasien</p>
        <p class="text-3xl font-bold text-blue-900">{{ $stats['pasien'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Total Dokter</p>
        <p class="text-3xl font-bold text-blue-900">{{ $stats['dokter'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Total Petugas</p>
        <p class="text-3xl font-bold text-blue-900">{{ $stats['petugas'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow p-5">
        <p class="text-sm text-gray-500">Antrean Hari Ini</p>
        <p class="text-3xl font-bold text-blue-900">{{ $stats['antrean_hari_ini'] }}</p>
    </div>
</div>

{{-- Status antrean hari ini --}}
<div class="bg-white rounded-xl shadow p-5 mb-6">
    <h3 class="font-semibold mb-4">Status Antrean Hari Ini</h3>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        @foreach ($statusLabels as $key => [$label, $class])
        <div class="rounded-lg p-4 text-center {{ $class }}">
            <p class="text-2xl font-bold">{{ $statusCounts[$key] ?? 0 }}</p>
            <p class="text-sm">{{ $label }}</p>
        </div>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
    {{-- Grafik 7 hari --}}
    <div class="bg-white rounded-xl shadow p-5">
        <h3 class="font-semibold mb-4">Kunjungan 7 Hari Terakhir</h3>
        <div class="flex items-end gap-3 h-48">
            @foreach ($chart as $c)
            <div class="flex-1 flex flex-col items-center justify-end h-full">
                <span class="text-xs mb-1">{{ $c['total'] }}</span>
                <div class="w-full bg-blue-600 rounded-t"
                    @style([ 'height: ' . ($c['total'] / $maxChart * 100) . '%' , 'min-height: 2px' ,
                    ])></div>
                <span class="text-xs text-gray-500 mt-2">{{ $c['label'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Antrean per poli --}}
    <div class="bg-white rounded-xl shadow p-5">
        <h3 class="font-semibold mb-4">Antrean Hari Ini per Poli</h3>
        @forelse ($perPoli as $row)
        <div class="flex justify-between items-center border-b py-2 last:border-0">
            <div>
                <p class="font-medium">{{ $row->nama_poli }}</p>
                <p class="text-xs text-gray-500">{{ $row->nama_cabang }}</p>
            </div>
            <span class="font-bold text-blue-900">{{ $row->total }}</span>
        </div>
        @empty
        <p class="text-gray-500 text-sm">Belum ada antrean hari ini.</p>
        @endforelse
    </div>
</div>

{{-- Antrean terbaru --}}
<div class="bg-white rounded-xl shadow p-5">
    <h3 class="font-semibold mb-4">Antrean Terbaru</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="p-3">Tanggal</th>
                    <th class="p-3">No</th>
                    <th class="p-3">Pasien</th>
                    <th class="p-3">Dokter</th>
                    <th class="p-3">Poli</th>
                    <th class="p-3">Cabang</th>
                    <th class="p-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($latestQueues as $q)
                @php
                $schedule = $q->doctorSchedule;
                [$label, $class] = $statusLabels[$q->status] ?? [$q->status, 'bg-gray-100 text-gray-700'];
                @endphp
                <tr class="border-t">
                    <td class="p-3">{{ $q->tanggal_antrean->format('d/m/Y') }}</td>
                    <td class="p-3 font-semibold">{{ $q->nomor_antrean }}</td>
                    <td class="p-3">{{ $q->patientProfile?->user?->name ?? '-' }}</td>
                    <td class="p-3">{{ $schedule?->doctor?->user?->name ?? '-' }}</td>
                    <td class="p-3">{{ $schedule?->poli?->nama_poli ?? '-' }}</td>
                    <td class="p-3">{{ $schedule?->poli?->klinikCabang?->nama_cabang ?? '-' }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 rounded-full text-xs {{ $class }}">{{ $label }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-4 text-center text-gray-500">Belum ada data antrean.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection