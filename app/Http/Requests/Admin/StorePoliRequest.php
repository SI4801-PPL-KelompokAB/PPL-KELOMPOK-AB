<?php

namespace App\Http\Requests\Admin;

use App\Models\KlinikCabang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePoliRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi diurus oleh middleware role:admin
    }

    public function rules(): array
    {
        $cabangId = $this->user()?->klinik_cabang_id ?? KlinikCabang::first()?->id ?? 1;

        return [
            'kode_poli' => [
                'required',
                'string',
                'max:20',
                Rule::unique('polis', 'kode_poli')->where('klinik_cabang_id', $cabangId),
            ],
            'nama_poli' => [
                'required',
                'string',
                'max:100',
                Rule::unique('polis', 'nama_poli')->where('klinik_cabang_id', $cabangId),
            ],
            'deskripsi' => ['nullable', 'string'],
            'status' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_poli.required' => 'Kode poli wajib diisi.',
            'kode_poli.max' => 'Kode poli maksimal :max karakter.',
            'kode_poli.unique' => 'Kode poli sudah digunakan di cabang ini.',
            'nama_poli.required' => 'Nama poli wajib diisi.',
            'nama_poli.max' => 'Nama poli maksimal :max karakter.',
            'nama_poli.unique' => 'Nama poli sudah digunakan di cabang ini.',
        ];
    }
}
