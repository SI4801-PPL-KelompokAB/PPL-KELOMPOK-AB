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
        Schema::create('polis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('klinik_cabang_id')
                ->constrained('klinik_cabang')
                ->cascadeOnDelete();

            $table->string('kode_poli', 20)->nullable();
            $table->string('nama_poli', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->unique(['klinik_cabang_id', 'nama_poli']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('polis');
    }
};
