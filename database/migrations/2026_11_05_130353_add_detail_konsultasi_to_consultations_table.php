<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PBI10: kolom tambahan untuk konsultasi online.
 * PERLU KONFIRMASI GALIH sebelum di-merge ke develop.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->foreignId('doctor_schedule_id')
                ->nullable()
                ->after('doctor_id')
                ->constrained('doctor_schedules')
                ->nullOnDelete();

            $table->date('tanggal_konsultasi')->nullable()->after('doctor_schedule_id');
            $table->text('keluhan')->nullable()->after('subject');
            $table->string('nomor_konsultasi', 30)->nullable()->unique()->after('id');

            $table->index(['doctor_schedule_id', 'tanggal_konsultasi']);
            $table->index(['doctor_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            // Hapus foreign key doctor_schedule_id dulu, baru index-nya
            $table->dropForeign(['doctor_schedule_id']);
            $table->dropIndex(['doctor_schedule_id', 'tanggal_konsultasi']);

            // Foreign key doctor_id butuh index; buat penggantinya sebelum index gabungan dihapus
            $table->index('doctor_id');
            $table->dropIndex(['doctor_id', 'status']);

            $table->dropUnique(['nomor_konsultasi']);
            $table->dropColumn(['doctor_schedule_id', 'tanggal_konsultasi', 'keluhan', 'nomor_konsultasi']);
        });
    }
};