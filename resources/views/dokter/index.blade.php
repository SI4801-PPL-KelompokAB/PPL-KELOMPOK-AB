@extends('layouts.app')
@section('title', 'Data Dokter')

@section('content')
<div class="page-head">
    <div>
        <h1 class="title title-sm">Data Dokter</h1>
        <p class="subtitle">Kelola data dokter untuk jadwal praktik.</p>
    </div>
    <a href="{{ route('jadwal.index') }}" class="btn btn-outline">Atur Jadwal Dokter →</a>
</div>

<div class="grid-2">
    <div class="card">
        <h3>Tambah Dokter</h3>
        <form action="{{ route('dokter.store') }}" method="POST">
            @include('dokter._form')
        </form>
    </div>

    <div class="card">
        <h3>Daftar Dokter</h3>
        <table class="table">
            <thead>
                <tr><th>Nama</th><th>Spesialisasi</th><th>Jadwal</th><th>Telepon</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse ($dokters as $d)
                <tr>
                    <td><strong>{{ $d->nama }}</strong></td>
                    <td>{{ $d->spesialisasi }}</td>
                    <td>{{ $d->jadwal_count }}</td>
                    <td>{{ $d->no_telp ?? '—' }}</td>
                    <td class="actions">
                        <a href="{{ route('dokter.edit', $d) }}" class="btn btn-outline btn-sm">Ubah</a>
                        <form action="{{ route('dokter.destroy', $d) }}" method="POST"
                              onsubmit="return confirm('Hapus dokter ini beserta jadwalnya?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="empty">Belum ada data dokter</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection