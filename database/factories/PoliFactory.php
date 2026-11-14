<?php

namespace Database\Factories;

use App\Models\KlinikCabang;
use App\Models\Poli;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Poli>
 */
class PoliFactory extends Factory
{
    protected $model = Poli::class;

    public function definition(): array
    {
        return [
            'klinik_cabang_id' => KlinikCabang::factory(),
            'kode_poli' => strtoupper(fake()->unique()->lexify('???')),
            'nama_poli' => fake()->unique()->words(2, true),
            'deskripsi' => fake()->sentence(),
            'status' => true,
        ];
    }
}
