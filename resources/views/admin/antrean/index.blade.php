@extends('layouts.app')

@section('title', 'Kelola Antrean Pasien')

@section('content')
<div class="py-2">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Kelola Antrean Pasien</h4>
            <p class="text-muted small mb-0">Pantau antrean online pasien dan perbarui status pelayanan klinik.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    {{-- Alert Notifikasi --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Ringkasan Status Hari Terpilih --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-mockup p-3">
                <span class="text-muted small fw-semibold text-uppercase">Menunggu (Waiting)</span>
                <h3 class="fw-bold text-warning mb-0 mt-1">{{ $stats['waiting'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-mockup p-3">
                <span class="text-muted small fw-semibold text-uppercase">Dipanggil (Called)</span>
                <h3 class="fw-bold text-info mb-0 mt-1">{{ $stats['called'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-mockup p-3">
                <span class="text-muted small fw-semibold text-uppercase">Dilayani (Serving)</span>
                <h3 class="fw-bold text-primary mb-0 mt-1">{{ $stats['serving'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-mockup p-3">
                <span class="text-muted small fw-semibold text-uppercase">Selesai (Done)</span>
                <h3 class="fw-bold text-success mb-0 mt-1">{{ $stats['completed'] }}</h3>
            </div>
        </div>
    </div>

    {{-- Filter Antrean --}}
    <div class="card-mockup p-3 mb-4">
        <form method="GET" action="{{ route('admin.antrean.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Tanggal Antrean</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Poli</label>
                <select name="poli" class="form-select form-select-sm">
                    <option value="">Semua Poli</option>
                    @foreach($polis as $p)
                        <option value="{{ $p->id }}" @selected($poliId == $p->id)>{{ $p->nama_poli }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="waiting" @selected($status === 'waiting')>Menunggu (Waiting)</option>
                    <option value="called" @selected($status === 'called')>Dipanggil (Called)</option>
                    <option value="serving" @selected($status === 'serving')>Sedang Dilayani (Serving)</option>
                    <option value="completed" @selected($status === 'completed')>Selesai (Completed)</option>
                    <option value="skipped" @selected($status === 'skipped')>Dilewati (Skipped)</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-app-primary px-3">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('admin.antrean.index') }}" class="btn btn-sm btn-outline-secondary px-3">
                    Hari Ini
                </a>
            </div>
        </form>
    </div>

    {{-- Tabel Daftar Antrean --}}
    <div class="card-mockup">
        <div class="card-mockup-body p-0">
            <div class="table-responsive">
                <table class="table table-mockup align-middle mb-0">
                    <thead>
                        <tr>
                            <th>No. Antrean</th>
                            <th>Pasien</th>
                            <th>Poli &amp; Dokter</th>
                            <th>Tanggal &amp; Jam</th>
                            <th>Status</th>
                            <th class="text-end">Aksi Alur Antrean</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($queues as $queue)
                            <tr>
                                <td>
                                    <span class="badge bg-dark fs-6 px-3 py-2 rounded-3">
                                        {{ $queue->formatted_nomor }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $queue->patientProfile->user->name ?? '-' }}</div>
                                    <small class="text-muted">{{ $queue->patientProfile->user->email ?? '' }}</small>
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <span class="badge-kode me-1">{{ $queue->doctorSchedule->poli->kode_poli ?? '-' }}</span>
                                        <span class="fw-semibold text-dark">{{ $queue->doctorSchedule->poli->nama_poli ?? '-' }}</span>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-person-badge me-1"></i>
                                        {{ $queue->doctorSchedule->doctor->user->name ?? '-' }}
                                    </small>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-dark">{{ $queue->tanggal_antrean->translatedFormat('d M Y') }}</div>
                                    <small class="text-muted">
                                        {{ substr($queue->doctorSchedule->jam_mulai ?? '00:00', 0, 5) }} - {{ substr($queue->doctorSchedule->jam_selesai ?? '00:00', 0, 5) }}
                                    </small>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($queue->status) {
                                            'waiting'   => 'bg-warning text-dark',
                                            'called'    => 'bg-info text-dark',
                                            'serving'   => 'bg-primary text-white',
                                            'completed' => 'bg-success text-white',
                                            'skipped'   => 'bg-secondary text-white',
                                            default     => 'bg-light text-dark',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2">
                                        {{ $queue->status_label }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-inline-flex flex-wrap justify-content-end gap-1">
                                        {{-- Tombol Cepat Berurutan: Waiting -> Serving -> Completed --}}
                                        @if($queue->status === 'waiting')
                                            <form action="{{ route('admin.antrean.update-status', $queue) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="serving">
                                                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                                                    <i class="bi bi-play-fill me-1"></i> Layani (Serve)
                                                </button>
                                            </form>
                                        @elseif(in_array($queue->status, ['called', 'serving']))
                                            <form action="{{ route('admin.antrean.update-status', $queue) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                                                    <i class="bi bi-check-lg me-1"></i> Selesai (Done)
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Dropdown Ubah Status Manual (jika butuh Panggil / Lewati / Reset) --}}
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Ubah
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                @foreach([
                                                    'waiting'   => 'Menunggu (Waiting)',
                                                    'called'    => 'Dipanggil (Called)',
                                                    'serving'   => 'Sedang Dilayani (Serving)',
                                                    'completed' => 'Selesai (Completed)',
                                                    'skipped'   => 'Dilewati (Skipped)',
                                                ] as $val => $label)
                                                    @if($queue->status !== $val)
                                                        <li>
                                                            <form action="{{ route('admin.antrean.update-status', $queue) }}" method="POST">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="{{ $val }}">
                                                                <button type="submit" class="dropdown-item small">{{ $label }}</button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-ticket-perforated d-block fs-3 mb-2"></i>
                                    Belum ada data antrean pasien untuk filter ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $queues->links() }}
    </div>
</div>
@endsection