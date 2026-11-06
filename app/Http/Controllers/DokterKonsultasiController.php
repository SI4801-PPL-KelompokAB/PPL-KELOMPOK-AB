<?php

namespace App\Http\Controllers;

use App\Models\Consultation;

class DokterKonsultasiController extends Controller
{
    public function index()
    {
        $dokter = $this->profilDokter();

        $konsultasis = Consultation::with('patientProfile.user', 'schedule.poli')
            ->where('doctor_id', $dokter->id)
            ->where('status', '!=', 'rejected')
            ->orderByRaw("CASE status WHEN 'pending' THEN 1 WHEN 'accepted' THEN 2 WHEN 'ongoing' THEN 3 ELSE 4 END")
            ->orderBy('tanggal_konsultasi')
            ->orderBy('id')
            ->get();

        return view('dokter.konsultasi.index', [
            'konsultasis' => $konsultasis,
            'hitung' => $konsultasis->groupBy('status')->map->count(),
        ]);
    }

    /** pending -> ongoing */
    public function mulai(Consultation $consultation)
    {
        $this->izin($consultation);

        if ($consultation->status !== 'pending') {
            return back()->withErrors(['status' => 'Sesi hanya dapat dimulai dari status Menunggu.']);
        }

        $consultation->update(['status' => 'ongoing']);

        return redirect()
            ->route('konsultasi.ruang', $consultation)
            ->with('success', 'Sesi dimulai.');
    }

    /** ongoing -> completed */
    public function selesai(Consultation $consultation)
    {
        $this->izin($consultation);

        if ($consultation->status !== 'ongoing') {
            return back()->withErrors(['status' => 'Sesi hanya dapat diakhiri dari status Berlangsung.']);
        }

        $consultation->update(['status' => 'completed']);

        return redirect()
            ->route('dokter.konsultasi.index')
            ->with('success', 'Sesi diakhiri.');
    }

    private function profilDokter()
    {
        $user = auth()->user();

        abort_unless($user?->role === 'dokter' && $user->doctor, 403);

        return $user->doctor;
    }

    private function izin(Consultation $consultation): void
    {
        $dokter = $this->profilDokter();

        abort_unless((int) $consultation->doctor_id === (int) $dokter->id, 403);
    }
}