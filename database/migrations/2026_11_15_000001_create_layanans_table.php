<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('layanans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('klinik_cabang_id')
                ->constrained('klinik_cabang')
                ->cascadeOnDelete();

            $table->foreignId('poli_id')
                ->nullable()
                ->constrained('polis')
                ->nullOnDelete();

            $table->string('kode_layanan', 30)->nullable();
            $table->string('nama_layanan', 150);
            $table->decimal('tarif_umum', 12, 2)->default(0);
            $table->decimal('tarif_bpjs', 12, 2)->default(0);
            $table->text('deskripsi')->nullable();
            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->unique(['klinik_cabang_id', 'nama_layanan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanans');
    }
};
