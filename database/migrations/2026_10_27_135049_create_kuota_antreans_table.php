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
        Schema::create('kuota_antreans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('poli_id')
                ->constrained('polis')
                ->restrictOnDelete();

            $table->date('tanggal');
            $table->unsignedInteger('kuota_maksimal');
            $table->timestamps();

            $table->unique(['poli_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuota_antreans');
    }
};
