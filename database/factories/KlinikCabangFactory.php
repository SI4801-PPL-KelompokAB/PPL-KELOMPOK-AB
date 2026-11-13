<?php

namespace Database\Factories;

use App\Models\KlinikCabang;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KlinikCabang>
 */
class KlinikCabangFactory extends Factory
{
    protected $model = KlinikCabang::class;

    public function definition(): array
    {
        return [
            'nama_cabang' => 'Klinik Bona Mitra Keluarga '.fake()->city(),
            'alamat' => fake()->address(),
            'nomor_telepon' => fake()->phoneNumber(),
            'status' => true,
        ];
    }
}
