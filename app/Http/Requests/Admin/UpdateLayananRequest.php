<?php

namespace App\Http\Requests\Admin;

use App\Models\KlinikCabang;
use App\Models\Layanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLayananRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Otorisasi diatur oleh middleware role:admin
    }

    public function rules(): array
    {
        $layanan = $this->route('layanan');
        $layananId = $layanan instanceof Layanan ? $layanan->id : $layanan;
        $cabangId = $layanan instanceof Layanan 
            ? $layanan->klinik_cabang_id 
            : ($this->user()?->klinik_cabang_id ?? KlinikCabang::first()?->id ?? 1);

        return [
            'nama_layanan' => [
                'required',
                'string',
                'max:150',
                Rule::unique('layanans', 'nama_layanan')
                    ->where('klinik_cabang_id', $cabangId)
                    ->ignore($layananId),
            ],
            'kode_layanan' => [
                'nullable',
                'string',
                'max:30',
            ],
            'poli_id' => [
                'nullable',
                'exists:polis,id',
            ],
            'tarif_umum' => [
                'required',
                'numeric',
                'min:0',
            ],
            'tarif_bpjs' => [
                'required',
                'numeric',
                'min:0',
            ],
            'deskripsi' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'status' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nama_layanan.required' => 'Nama layanan wajib diisi.',
            'nama_layanan.max' => 'Nama layanan maksimal :max karakter.',
            'nama_layanan.unique' => 'Nama layanan sudah digunakan di cabang ini.',
            'kode_layanan.max' => 'Kode layanan maksimal :max karakter.',
            'poli_id.exists' => 'Poli yang dipilih tidak valid.',
            'tarif_umum.required' => 'Tarif umum wajib diisi.',
            'tarif_umum.numeric' => 'Tarif umum harus berupa angka.',
            'tarif_umum.min' => 'Tarif umum tidak boleh negatif.',
            'tarif_bpjs.required' => 'Tarif BPJS wajib diisi (isi 0 jika ditanggung BPJS).',
            'tarif_bpjs.numeric' => 'Tarif BPJS harus berupa angka.',
            'tarif_bpjs.min' => 'Tarif BPJS tidak boleh negatif.',
        ];
    }
}
