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
        Schema::table('weights', function (Blueprint $table) {
            $table->unique(['user_id', 'date'], 'weights_user_date_unique');
        });

        Schema::table('blood_pressures', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'blood_pressures_user_date_index');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'files_user_created_at_index');
        });

        Schema::table('diets', function (Blueprint $table) {
            $table->index(['user_id', 'deleted_at', 'created_at'], 'diets_user_deleted_created_index');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->index(['user_id', 'date'], 'notes_user_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weights', function (Blueprint $table) {
            $table->dropUnique('weights_user_date_unique');
        });

        Schema::table('blood_pressures', function (Blueprint $table) {
            $table->dropIndex('blood_pressures_user_date_index');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex('files_user_created_at_index');
        });

        Schema::table('diets', function (Blueprint $table) {
            $table->dropIndex('diets_user_deleted_created_index');
        });

        Schema::table('notes', function (Blueprint $table) {
            $table->dropIndex('notes_user_date_index');
        });
    }
};
