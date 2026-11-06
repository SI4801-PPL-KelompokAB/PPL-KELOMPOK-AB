@csrf
<div class="form-group">
    <label>Nama Dokter</label>
    <input class="input" type="text" name="nama" placeholder="dr. Andi Pratama" value="{{ old('nama', $dokter->nama ?? '') }}">
</div>
<div class="form-group">
    <label>Spesialisasi</label>
    <input class="input" type="text" name="spesialisasi" placeholder="Dokter Umum" value="{{ old('spesialisasi', $dokter->spesialisasi ?? '') }}">
</div>
<div class="form-group">
    <label>Nomor Telepon</label>
    <input class="input" type="text" name="no_telp" placeholder="08xxxxxxxxxx" value="{{ old('no_telp', $dokter->no_telp ?? '') }}">
</div>
<div class="form-group">
    <label>Email</label>
    <input class="input" type="email" name="email" placeholder="dokter@puskesmas.test" value="{{ old('email', $dokter->email ?? '') }}">
</div>
<button type="submit" class="btn">Simpan</button>
@isset($dokter) <a href="{{ route('dokter.index') }}" class="btn btn-outline">Batal</a> @endisset