<!DOCTYPE html>
<html>
<head><title>Tambah Dokter</title></head>
<body>
    <h1>Tambah Dokter</h1>
    <form action="{{ route('dokter.store') }}" method="POST">
        @include('dokter._form')
    </form>
</body>
</html>