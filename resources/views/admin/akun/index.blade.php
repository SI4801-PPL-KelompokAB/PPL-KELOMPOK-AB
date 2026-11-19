@extends('layouts.app')

@section('title', 'Manajemen Akun Staf')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h4 class="fw-bold text-dark mb-1">Daftar Akun Dokter &amp; Admin</h4>
        <p class="text-muted small mb-0">Kelola akun staf internal yang dibuat oleh administrator (PBI-03)</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <a href="{{ route('admin.akun.create') }}" dusk="tombol-buat-akun" class="btn btn-app-primary shadow-sm rounded-pill px-3">
            <i class="bi bi-person-plus-fill me-1"></i> Buat Akun Baru
        </a>
    </div>
</div>

<div class="card-mockup">
    <div class="card-mockup-header d-flex justify-content-between align-items-center border-bottom pb-3">
        <h5 class="fw-bold text-dark mb-0">Daftar Akun Terdaftar</h5>
        <span class="badge bg-light text-secondary border px-3 py-1">Total: {{ $akuns->count() }} Akun</span>
    </div>
    <div class="card-mockup-body p-0">
        <div class="table-responsive">
            <table class="table table-mockup align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Nama Lengkap</th>
                        <th>Role Akses</th>
                        <th>Email</th>
                        <th>No. Telepon</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($akuns as $akun)
                        <tr dusk="akun-{{ $akun->id }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle p-2 d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px; background-color: {{ $akun->role === 'admin' ? '#fef3c7' : '#d1f4ed' }}; color: {{ $akun->role === 'admin' ? '#92400e' : '#005954' }};">
                                        <i class="bi {{ $akun->role === 'admin' ? 'bi-shield-lock' : 'bi-person-badge' }}"></i>
                                    </div>
                                    <span class="fw-semibold text-dark">{{ $akun->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($akun->role === 'admin')
                                    <span class="admin-pill">ADMIN</span>
                                @else
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-1">DOKTER</span>
                                @endif
                            </td>
                            <td>
                                <span class="small text-muted">{{ $akun->email }}</span>
                            </td>
                            <td>
                                <span class="badge-kode">{{ $akun->no_hp ?? '-' }}</span>
                            </td>
                            <td>
                                @if($akun->is_active)
                                    <span class="status-dot-active">&bull; Aktif</span>
                                @else
                                    <span class="status-dot-inactive">&bull; Nonaktif</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-people d-block fs-3 mb-2"></i>
                                Belum ada akun dokter atau admin yang terdaftar.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
