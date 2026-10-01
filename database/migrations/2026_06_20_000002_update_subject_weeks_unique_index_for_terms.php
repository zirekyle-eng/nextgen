<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subject_weeks')) {
            return;
        }

        Schema::table('subject_weeks', function (Blueprint $table) {
            try {
                $table->index('subject_id', 'subject_weeks_subject_id_index');
            } catch (\Throwable $e) {
                // The index may already exist.
            }
        });

        Schema::table('subject_weeks', function (Blueprint $table) {
            try {
                $table->dropUnique('subject_weeks_subject_id_week_number_unique');
            } catch (\Throwable $e) {
                // The legacy index may not exist on fresh databases.
            }
        });

        if (
            Schema::hasColumn('subject_weeks', 'academic_session_id') &&
            Schema::hasColumn('subject_weeks', 'subject_id') &&
            Schema::hasColumn('subject_weeks', 'week_number')
        ) {
            Schema::table('subject_weeks', function (Blueprint $table) {
                $table->unique(['academic_session_id', 'subject_id', 'week_number'], 'subject_weeks_session_subject_week_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('subject_weeks')) {
            return;
        }

        Schema::table('subject_weeks', function (Blueprint $table) {
            try {
                $table->dropUnique('subject_weeks_session_subject_week_unique');
            } catch (\Throwable $e) {
                // Index may not exist if the migration was partially applied.
            }
        });

        Schema::table('subject_weeks', function (Blueprint $table) {
            $table->unique(['subject_id', 'week_number'], 'subject_weeks_subject_id_week_number_unique');
        });
    }
};
