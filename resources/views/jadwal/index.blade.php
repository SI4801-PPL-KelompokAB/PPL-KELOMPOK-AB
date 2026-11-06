@extends('layouts.app')
@section('title', 'Jadwal Dokter')

@section('content')
@php
    $hariList = ['Senin'=>'SEN','Selasa'=>'SEL','Rabu'=>'RAB','Kamis'=>'KAM','Jumat'=>'JUM','Sabtu'=>'SAB'];
@endphp

<span class="eyebrow">DOKTER</span>
<h1 class="title">Jadwal Dokter</h1>
<p class="subtitle">Cari dan filter jadwal dokter berdasarkan nama atau poli.</p>

<div class="search">
    <input class="input" type="text" id="cari" placeholder="Cari nama dokter...">
</div>

<div class="chips" id="chips">
    <button type="button" class="chip active" data-poli="">Semua</button>
    @foreach ($poliList as $p)
        <button type="button" class="chip" data-poli="{{ $p }}">{{ $p }}</button>
    @endforeach
</div>

<div class="jadwal-wrap">
    <table class="jadwal-table">
        <thead>
            <tr>
                <th>DOKTER</th><th>POLI</th>
                @foreach ($hariList as $singkat) <th>{{ $singkat }}</th> @endforeach
            </tr>
        </thead>
        <tbody id="tbody">
            @forelse ($dokters as $d)
            <tr data-nama="{{ strtolower($d->nama) }}" data-poli="{{ $d->spesialisasi }}">
                <td class="nama">{{ $d->nama }}</td>
                <td><span class="badge">{{ $d->spesialisasi }}</span></td>
                @foreach ($hariList as $hari => $singkat)
                    <td>
                        @php $slots = $d->jadwal->where('hari', $hari)->sortBy('jam_mulai'); @endphp
                        @forelse ($slots as $s)
                            <span class="slot">
                                {{ \Carbon\Carbon::parse($s->jam_mulai)->format('H.i') }} s.d
                                {{ \Carbon\Carbon::parse($s->jam_selesai)->format('H.i') }} WIB
                            </span>
                        @empty
                            <span class="dash">-</span>
                        @endforelse
                    </td>
                @endforeach
            </tr>
            @empty
            <tr><td colspan="8" class="empty">Belum ada data dokter</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Form tambah jadwal (untuk admin) --}}
<div class="card" style="margin-top:28px">
    <h3>Tambah Jadwal</h3>
    <form action="{{ route('jadwal.store') }}" method="POST">
        @csrf
        <div class="form-row">
            <div class="form-group">
                <label>Dokter</label>
                <select class="input" name="dokter_id">
                    @foreach ($dokters as $d)
                        <option value="{{ $d->id }}">{{ $d->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Hari</label>
                <select class="input" name="hari">
                    @foreach (array_keys($hariList) as $h) <option>{{ $h }}</option> @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Jam Mulai</label>
                <input class="input" type="time" name="jam_mulai">
            </div>
            <div class="form-group">
                <label>Jam Selesai</label>
                <input class="input" type="time" name="jam_selesai">
            </div>
            <button type="submit" class="btn">Simpan</button>
        </div>
    </form>
</div>

<script>
    const cari = document.getElementById('cari');
    const chips = document.querySelectorAll('.chip');
    const rows = document.querySelectorAll('#tbody tr[data-nama]');
    let poliAktif = '';

    function filter() {
        const q = cari.value.toLowerCase();
        rows.forEach(r => {
            const cocokNama = r.dataset.nama.includes(q);
            const cocokPoli = !poliAktif || r.dataset.poli === poliAktif;
            r.classList.toggle('hidden', !(cocokNama && cocokPoli));
        });
    }
    cari.addEventListener('input', filter);
    chips.forEach(c => c.addEventListener('click', () => {
        chips.forEach(x => x.classList.remove('active'));
        c.classList.add('active');
        poliAktif = c.dataset.poli;
        filter();
    }));
</script>
@endsection