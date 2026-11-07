<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProfilPasienRequest;

class PatientProfileController extends Controller
{
    public function edit()
    {
        $profil = $this->profilPasien();

        return view('pasien.profil.edit', compact('profil'));
    }

    public function update(StoreProfilPasienRequest $request)
    {
        $profil = $this->profilPasien();

        $profil->update($request->validated());

        return redirect()->route('pasien.profil.edit')
            ->with('success', 'Profil berhasil disimpan.');
    }

    private function profilPasien()
    {
        $user = auth()->user();

        abort_unless($user?->role === 'pasien', 403);

        return $user->patientProfile;
    }
}
