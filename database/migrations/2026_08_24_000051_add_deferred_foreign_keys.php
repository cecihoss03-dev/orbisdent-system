<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('odontograms', function (Blueprint $table) {
            $table->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
        });

        Schema::table('tooth_treatment_events', function (Blueprint $table) {
            $table->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
        });

        Schema::table('periodontal_charts', function (Blueprint $table) {
            $table->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
        });

        Schema::table('clinical_evolution_notes', function (Blueprint $table) {
            $table->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('treatment_id')->references('id')->on('treatment_catalog');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['treatment_id']);
        });

        Schema::table('clinical_evolution_notes', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
        });

        Schema::table('periodontal_charts', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
        });

        Schema::table('tooth_treatment_events', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
        });

        Schema::table('odontograms', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
        });
    }
};
