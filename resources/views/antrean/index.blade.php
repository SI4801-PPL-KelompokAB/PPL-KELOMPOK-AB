@extends('layouts.app')

@section('title', 'Daftar Antrean Saya')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h2 class="h4 mb-1 text-dark fw-bold">Daftar Antrean Saya</h2>
        <p class="text-muted small mb-0">Pantau tiket nomor antrean aktif dan riwayat antrean klinik Anda</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('antrean.create') }}" class="btn btn-primary shadow-sm" dusk="btn-ambil-antrean-baru">
            <i class="bi bi-plus-circle me-1"></i> Ambil Antrean Baru
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 py-3">No. Antrean</th>
                        <th>Poli</th>
                        <th>Dokter</th>
                        <th>Tanggal Kunjungan</th>
                        <th>Jam Praktik</th>
                        <th class="text-center">Status</th>
                        <th class="pe-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queues as $item)
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-primary fs-6 px-3 py-2">
                                    {{ $item->formatted_nomor }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $item->doctorSchedule->poli->nama_poli ?? '-' }}</span>
                            </td>
                            <td>
                                <div>{{ $item->doctorSchedule->doctor->user->name ?? 'Dokter' }}</div>
                                @if($item->doctorSchedule->doctor->nomor_sip)
                                    <small class="text-muted">SIP: {{ $item->doctorSchedule->doctor->nomor_sip }}</small>
                                @endif
                            </td>
                            <td>
                                <i class="bi bi-calendar3 me-1 text-muted"></i>
                                {{ \Carbon\Carbon::parse($item->tanggal_antrean)->translatedFormat('d F Y') }}
                            </td>
                            <td>
                                <i class="bi bi-clock me-1 text-muted"></i>
                                {{ \Carbon\Carbon::parse($item->doctorSchedule->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->doctorSchedule->jam_selesai)->format('H:i') }}
                            </td>
                            <td class="text-center">
                                @if($item->status === 'waiting')
                                    <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-1 rounded-pill">
                                        <i class="bi bi-hourglass-split me-1"></i> Menunggu
                                    </span>
                                @elseif($item->status === 'called')
                                    <span class="badge bg-info-subtle text-info-emphasis px-3 py-1 rounded-pill">
                                        <i class="bi bi-megaphone me-1"></i> Dipanggil
                                    </span>
                                @elseif($item->status === 'serving')
                                    <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill">
                                        <i class="bi bi-activity me-1"></i> Sedang Dilayani
                                    </span>
                                @elseif($item->status === 'completed')
                                    <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i> Selesai
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-3 py-1 rounded-pill">
                                        Dilewati
                                    </span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('antrean.show', $item) }}" class="btn btn-outline-primary btn-sm" dusk="lihat-tiket-{{ $item->id }}">
                                    <i class="bi bi-ticket-perforated me-1"></i> Lihat Tiket
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-ticket-detailed fs-1 text-secondary d-block mb-2"></i>
                                Belum ada antrean yang terdaftar.
                                <div class="mt-2">
                                    <a href="{{ route('antrean.create') }}" class="btn btn-sm btn-primary">
                                        Ambil Nomor Antrean Sekarang
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($queues, 'hasPages') && $queues->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $queues->links() }}
        </div>
    @endif
</div>
@endsection

