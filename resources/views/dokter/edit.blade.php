@extends('layouts.app')
@section('title', 'Ubah Dokter')

@section('content')
<div class="page-head">
    <h1 class="title title-sm">Ubah Dokter</h1>
</div>
<div class="card" style="max-width:420px">
    <form action="{{ route('dokter.update', $dokter) }}" method="POST">
        @method('PUT')
        @include('dokter._form')
    </form>
</div>
@endsection