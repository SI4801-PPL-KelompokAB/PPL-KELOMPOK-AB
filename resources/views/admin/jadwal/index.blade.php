@extends('layouts.app')

@section('title', 'Kelola Jadwal Praktik Dokter')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="h4 mb-1 text-dark fw-bold">Jadwal Praktik Dokter</h2>
        <p class="text-muted small mb-0">Kelola jadwal hari, jam kerja, dan kuota antrean pasien per dokter</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.jadwal.create') }}" class="btn btn-primary shadow-sm" dusk="btn-tambah-jadwal">
            <i class="bi bi-plus-circle me-1"></i> Tambah Jadwal Praktik
        </a>
    </div>
</div>

<!-- Card Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.jadwal.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">Poli</label>
                <select name="poli_id" class="form-select form-select-sm">
                    <option value="">Semua Poli</option>
                    @foreach($polis as $poli)
                        <option value="{{ $poli->id }}" {{ request('poli_id') == $poli->id ? 'selected' : '' }}>
                            {{ $poli->nama_poli }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">Dokter</label>
                <select name="doctor_id" class="form-select form-select-sm">
                    <option value="">Semua Dokter</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>
                            {{ $doc->user->name ?? 'Dokter #'.$doc->id }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">Hari</label>
                <select name="hari" class="form-select form-select-sm">
                    <option value="">Semua Hari</option>
                    @foreach($daftarHari as $h)
                        <option value="{{ $h }}" {{ request('hari') == $h ? 'selected' : '' }}>
                            {{ ucfirst($h) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <a href="{{ route('admin.jadwal.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Jadwal -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-3" style="width: 50px;">No</th>
                        <th>Dokter</th>
                        <th>Poli</th>
                        <th>Hari</th>
                        <th>Jam Praktik</th>
                        <th class="text-center">Kuota Antrean</th>
                        <th class="text-center">Status</th>
                        <th class="pe-3 text-end" style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $index => $item)
                        <tr>
                            <td class="ps-3 text-muted small">{{ $schedules->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $item->doctor->user->name ?? 'Dokter #'.$item->doctor_id }}</div>
                                @if($item->doctor->nomor_sip)
                                    <div class="text-muted small">SIP: {{ $item->doctor->nomor_sip }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $item->poli->nama_poli ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ ucfirst($item->hari) }}</span>
                            </td>
                            <td>
                                <i class="bi bi-clock me-1 text-muted"></i>
                                {{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }} WIB
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info-emphasis px-3 py-2 fw-semibold">
                                    {{ $item->kuota }} pasien
                                </span>
                            </td>
                            <td class="text-center">
                                @if($item->status)
                                    <span class="badge bg-success-subtle text-success px-2 py-1 rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1 rounded-pill">
                                        <i class="bi bi-x-circle me-1"></i> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.jadwal.edit', $item) }}" class="btn btn-outline-secondary" title="Edit Jadwal" dusk="edit-jadwal-{{ $item->id }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.jadwal.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin {{ $item->status ? 'menonaktifkan' : 'mengaktifkan kembali' }} jadwal ini?')">
                                        @csrf
                                        @method('DELETE')
                                        @if($item->status)
                                            <button type="submit" class="btn btn-outline-danger" title="Nonaktifkan Jadwal" dusk="toggle-jadwal-{{ $item->id }}">
                                                <i class="bi bi-pause-circle"></i>
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-outline-success" title="Aktifkan Jadwal" dusk="toggle-jadwal-{{ $item->id }}">
                                                <i class="bi bi-play-circle"></i>
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary"></i>
                                Belum ada data jadwal praktik yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($schedules->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $schedules->links() }}
        </div>
    @endif
</div>
@endsection

