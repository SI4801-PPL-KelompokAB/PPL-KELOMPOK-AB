@props(['status'])

@php
    // Warna badge status konsultasi, sama di semua halaman
    $kelas = [
        'pending'   => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
        'accepted'  => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
        'ongoing'   => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        'completed' => 'bg-success-subtle text-success-emphasis border border-success-subtle',
        'rejected'  => 'bg-danger-subtle text-danger-emphasis border border-danger-subtle',
    ][$status] ?? 'bg-light text-dark border';

    $label = \App\Models\Consultation::LABEL_STATUS[$status] ?? ucfirst($status);
@endphp

<span {{ $attributes->merge(['class' => "badge rounded-pill fw-semibold {$kelas}"]) }}>{{ $label }}</span>