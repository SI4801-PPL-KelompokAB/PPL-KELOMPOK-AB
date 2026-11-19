<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKonsultasiOnlineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_schedule_id' => ['required', Rule::exists('doctor_schedules', 'id')->where('status', true)],
            'tanggal_konsultasi' => ['required', 'date', 'after_or_equal:today'],
            'keluhan'            => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_schedule_id.required'       => 'Jadwal dokter wajib dipilih',
            'doctor_schedule_id.exists'         => 'Jadwal dokter tidak ditemukan',
            'tanggal_konsultasi.required'       => 'Tanggal konsultasi wajib diisi',
            'tanggal_konsultasi.after_or_equal' => 'Tanggal konsultasi tidak valid',
            'keluhan.required'                  => 'Keluhan wajib diisi',
            'keluhan.min'                       => 'Keluhan minimal 10 karakter',
            'keluhan.max'                       => 'Keluhan maksimal 1000 karakter',
        ];
    }
}