<?php

namespace Database\Seeders;

use App\Models\KlinikCabang;
use App\Models\Layanan;
use App\Models\Poli;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(KonsultasiSeeder::class);

        // Akun Admin untuk pengujian PBI-05 (Admin Poli)
        User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Admin Bonamu',
                'no_hp' => '081234567890',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // Seeder Master Data Layanan & Tarif (PBI-12)
        $cabang = KlinikCabang::first();
        if ($cabang) {
            $poliUmum = Poli::firstOrCreate(
                ['klinik_cabang_id' => $cabang->id, 'nama_poli' => 'Poli Umum'],
                ['kode_poli' => 'PU', 'deskripsi' => 'Layanan pemeriksaan kesehatan umum', 'status' => true]
            );
            $poliGigi = Poli::firstOrCreate(
                ['klinik_cabang_id' => $cabang->id, 'nama_poli' => 'Poli Gigi dan Mulut'],
                ['kode_poli' => 'PGT', 'deskripsi' => 'Layanan perawatan dan kesehatan gigi', 'status' => true]
            );
            $poliAnak = Poli::firstOrCreate(
                ['klinik_cabang_id' => $cabang->id, 'nama_poli' => 'Poli Anak'],
                ['kode_poli' => 'ANK', 'deskripsi' => 'Layanan imunisasi dan kesehatan anak', 'status' => true]
            );

            $layanans = [
                [
                    'nama_layanan' => 'Pemeriksaan Umum',
                    'kode_layanan' => 'LAY-UMM-01',
                    'poli_id' => $poliUmum->id,
                    'tarif_umum' => 50000,
                    'tarif_bpjs' => 0,
                    'deskripsi' => 'Konsultasi dokter umum, pemeriksaan fisik dasar, dan tensi darah.',
                    'status' => true,
                ],
                [
                    'nama_layanan' => 'Pemeriksaan Gigi',
                    'kode_layanan' => 'LAY-GGI-01',
                    'poli_id' => $poliGigi->id,
                    'tarif_umum' => 150000,
                    'tarif_bpjs' => 0,
                    'deskripsi' => 'Pembersihan karang gigi (scaling) dan konsultasi dokter gigi.',
                    'status' => true,
                ],
                [
                    'nama_layanan' => 'Imunisasi Anak',
                    'kode_layanan' => 'LAY-ANK-01',
                    'poli_id' => $poliAnak->id,
                    'tarif_umum' => 75000,
                    'tarif_bpjs' => 0,
                    'deskripsi' => 'Vaksinasi berkala untuk bayi dan anak balita.',
                    'status' => true,
                ],
                [
                    'nama_layanan' => 'Konsultasi Online',
                    'kode_layanan' => 'LAY-UMM-02',
                    'poli_id' => $poliUmum->id,
                    'tarif_umum' => 35000,
                    'tarif_bpjs' => 0,
                    'deskripsi' => 'Layanan konsultasi telemedisin daring dengan dokter.',
                    'status' => true,
                ],
                [
                    'nama_layanan' => 'Pembersihan Karang Gigi (Scaling)',
                    'kode_layanan' => 'LAY-GGI-02',
                    'poli_id' => $poliGigi->id,
                    'tarif_umum' => 150000,
                    'tarif_bpjs' => 0,
                    'deskripsi' => 'Scaling gigi rahang atas dan bawah dengan ultrasonic scaler.',
                    'status' => true,
                ],
            ];

            foreach ($layanans as $layanan) {
                Layanan::firstOrCreate(
                    [
                        'klinik_cabang_id' => $cabang->id,
                        'nama_layanan' => $layanan['nama_layanan']
                    ],
                    $layanan
                );
            }
        }
    }
}
