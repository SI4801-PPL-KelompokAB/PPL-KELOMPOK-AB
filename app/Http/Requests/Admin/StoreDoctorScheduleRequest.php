<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => 'required|exists:doctors,id',
            'poli_id' => 'required|exists:polis,id',
            'hari' => 'required|in:senin,selasa,rabu,kamis,jumat,sabtu,minggu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'kuota' => 'required|integer|min:1',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'doctor_id.required' => 'Dokter wajib dipilih.',
            'doctor_id.exists' => 'Dokter yang dipilih tidak terdaftar.',
            'poli_id.required' => 'Poli wajib dipilih.',
            'poli_id.exists' => 'Poli yang dipilih tidak terdaftar.',
            'hari.required' => 'Hari praktik wajib dipilih.',
            'hari.in' => 'Pilihan hari tidak valid.',
            'jam_mulai.required' => 'Jam mulai praktik wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid (contoh: 08:00).',
            'jam_selesai.required' => 'Jam selesai praktik wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid (contoh: 12:00).',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'kuota.required' => 'Kuota antrean wajib diisi.',
            'kuota.integer' => 'Kuota antrean harus berupa angka.',
            'kuota.min' => 'Kuota antrean minimal 1.',
        ];
    }
}

