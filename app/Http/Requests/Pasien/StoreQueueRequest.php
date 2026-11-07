<?php

namespace App\Http\Requests\Pasien;

use Illuminate\Foundation\Http\FormRequest;

class StoreQueueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_schedule_id' => 'required|exists:doctor_schedules,id',
            'tanggal_antrean' => 'required|date|date_format:Y-m-d',
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_schedule_id.required' => 'Jadwal dokter wajib dipilih.',
            'doctor_schedule_id.exists' => 'Jadwal dokter yang dipilih tidak terdaftar.',
            'tanggal_antrean.required' => 'Tanggal rencana kunjungan wajib dipilih.',
            'tanggal_antrean.date' => 'Format tanggal kunjungan tidak valid.',
            'tanggal_antrean.date_format' => 'Format tanggal harus tahun-bulan-hari (YYYY-MM-DD).',
        ];
    }
}

