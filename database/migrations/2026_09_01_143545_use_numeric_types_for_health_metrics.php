<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LAB_RESULT_COLUMNS = [
        'wbc',
        'rbc',
        'hgb',
        'hct',
        'mcv',
        'mch',
        'mchc',
        'plt',
        'rdw_sd',
        'rdw_cv',
        'pdw',
        'mpv',
        'p_lcr',
        'pct',
        'neu',
        'lym',
        'mono',
        'eos',
        'baso',
        'tsh',
        'ast',
        'alt',
        'bilirubin',
        'alp',
        'ggtp',
        'total_cholesterol',
        'hdl_cholesterol',
        'non_hdl_cholesterol',
        'ldl_cholesterol',
        'triglycerides',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('height', 5, 2)->nullable()->change();
            $table->decimal('weight', 5, 2)->nullable()->change();
            $table->unsignedTinyInteger('age')->nullable()->change();

            foreach (self::LAB_RESULT_COLUMNS as $column) {
                $table->decimal($column, 10, 3)->nullable()->change();
            }
        });

        Schema::table('weights', function (Blueprint $table) {
            $table->decimal('weight', 5, 2)->change();
        });

        Schema::table('blood_pressures', function (Blueprint $table) {
            $table->unsignedSmallInteger('systolic')->change();
            $table->unsignedSmallInteger('diastolic')->change();
        });

        Schema::table('files', function (Blueprint $table) {
            $table->decimal('size', 8, 2)->change();
        });

        Schema::table('diets', function (Blueprint $table) {
            $table->unsignedSmallInteger('calories')->change();
            $table->unsignedTinyInteger('meals')->change();
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->unsignedTinyInteger('energy_level')->change();
            $table->unsignedTinyInteger('stress_level')->change();
            $table->decimal('sleep_hours', 4, 2)->change();
            $table->decimal('water_intake', 4, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('height')->nullable()->change();
            $table->string('weight')->nullable()->change();
            $table->string('age')->nullable()->change();

            foreach (self::LAB_RESULT_COLUMNS as $column) {
                $table->string($column)->nullable()->change();
            }
        });

        Schema::table('weights', function (Blueprint $table) {
            $table->string('weight')->change();
        });

        Schema::table('blood_pressures', function (Blueprint $table) {
            $table->string('systolic')->change();
            $table->string('diastolic')->change();
        });

        Schema::table('files', function (Blueprint $table) {
            $table->string('size')->change();
        });

        Schema::table('diets', function (Blueprint $table) {
            $table->string('calories')->change();
            $table->string('meals')->change();
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->string('energy_level')->change();
            $table->string('stress_level')->change();
            $table->string('sleep_hours')->change();
            $table->string('water_intake')->change();
        });
    }
};
