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
        Schema::create('jadwal_operasionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poli_id')
                ->constrained('polis')
                ->restrictOnDelete();

            // 1 = Senin, 7 = Minggu
            $table->unsignedTinyInteger('hari');

            $table->time('jam_buka');
            $table->time('jam_tutup');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['poli_id', 'hari']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_operasionals');
    }
};
