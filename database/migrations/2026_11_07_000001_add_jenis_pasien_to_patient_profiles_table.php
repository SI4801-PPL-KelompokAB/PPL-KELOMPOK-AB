<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_profiles', function (Blueprint $table) {
            $table->string('jenis_pasien', 10)->nullable()->after('alamat');
            $table->string('no_bpjs', 20)->nullable()->after('jenis_pasien');
        });
    }

    public function down(): void
    {
        Schema::table('patient_profiles', function (Blueprint $table) {
            $table->dropColumn(['jenis_pasien', 'no_bpjs']);
        });
    }
};
