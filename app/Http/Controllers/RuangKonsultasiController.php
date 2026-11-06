<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RuangKonsultasiController extends Controller
{
    public function show(Consultation $consultation)
    {
        $this->izinAkses($consultation);

        $consultation->load('schedule.poli', 'doctor.user', 'patientProfile.user', 'messages.sender');

        return view('konsultasi.ruang', [
            'konsultasi' => $consultation,
        ]);
    }

    public function kirim(Request $request, Consultation $consultation)
    {
        $this->izinAkses($consultation);

        if ($consultation->status !== 'ongoing') {
            throw ValidationException::withMessages([
                'pesan' => 'Pesan hanya dapat dikirim saat sesi berlangsung.',
            ]);
        }

        $data = $request->validate([
            'pesan' => ['required', 'string', 'max:1000'],
        ], [
            'pesan.required' => 'Pesan wajib diisi.',
            'pesan.max' => 'Pesan maksimal 1000 karakter.',
        ]);

        $consultation->messages()->create([
            'sender_user_id' => auth()->id(),
            'message' => $data['pesan'],
        ]);

        return redirect()->route('konsultasi.ruang', $consultation);
    }

    private function izinAkses(Consultation $consultation): void
    {
        $user = auth()->user();

        $boleh = ($user?->role === 'pasien' && (int) $consultation->patient_profile_id === (int) $user->patientProfile?->id)
            || ($user?->role === 'dokter' && (int) $consultation->doctor_id === (int) $user->doctor?->id);

        abort_unless($boleh, 403);
    }
}