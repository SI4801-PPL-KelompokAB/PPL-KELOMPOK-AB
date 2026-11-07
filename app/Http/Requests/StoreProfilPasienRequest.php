<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfilPasienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()?->role === 'pasien';
    }

    public function rules(): array
    {
        return [
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'alamat' => ['required', 'string', 'max:1000'],
            'jenis_pasien' => ['required', 'in:BPJS,UMUM'],
            'no_bpjs' => ['nullable', 'string', 'max:20', 'required_if:jenis_pasien,BPJS', 'prohibited_if:jenis_pasien,UMUM'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'tanggal_lahir.before' => 'Tanggal lahir harus sebelum hari ini.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin hanya boleh L atau P.',
            'alamat.required' => 'Alamat wajib diisi.',
            'jenis_pasien.required' => 'Jenis pasien wajib dipilih.',
            'jenis_pasien.in' => 'Jenis pasien hanya boleh BPJS atau UMUM.',
            'no_bpjs.required_if' => 'Nomor BPJS wajib diisi untuk pasien BPJS.',
            'no_bpjs.prohibited_if' => 'Nomor BPJS harus kosong untuk pasien UMUM.',
            'no_bpjs.max' => 'Nomor BPJS maksimal 20 karakter.',
        ];
    }
}
