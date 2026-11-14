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
        Schema::create('queues', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patient_profile_id')
                ->constrained('patient_profiles')
                ->cascadeOnDelete();

            $table->foreignId('doctor_schedule_id')
                ->constrained('doctor_schedules')
                ->restrictOnDelete();

            $table->date('tanggal_antrean');
            $table->integer('nomor_antrean');

            $table->enum('status', [
                'waiting',
                'called',
                'serving',
                'completed',
                'skipped',
            ])->default('waiting');

            $table->timestamps();

            $table->unique([
                'doctor_schedule_id',
                'tanggal_antrean',
                'nomor_antrean',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
