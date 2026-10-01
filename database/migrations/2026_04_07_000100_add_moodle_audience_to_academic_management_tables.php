<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subject_general_files') && !Schema::hasColumn('subject_general_files', 'moodle_audience')) {
            Schema::table('subject_general_files', function (Blueprint $table) {
                $table->string('moodle_audience', 20)->default('both')->after('moodle_cmid');
            });
        }

        if (Schema::hasTable('unit_general_files') && !Schema::hasColumn('unit_general_files', 'moodle_audience')) {
            Schema::table('unit_general_files', function (Blueprint $table) {
                $table->string('moodle_audience', 20)->default('both')->after('moodle_cmid');
            });
        }

        if (Schema::hasTable('lesson_files') && !Schema::hasColumn('lesson_files', 'moodle_audience')) {
            Schema::table('lesson_files', function (Blueprint $table) {
                $table->string('moodle_audience', 20)->default('both')->after('moodle_cmid');
            });
        }

        if (Schema::hasTable('academic_quizzes') && !Schema::hasColumn('academic_quizzes', 'moodle_audience')) {
            Schema::table('academic_quizzes', function (Blueprint $table) {
                $table->string('moodle_audience', 20)->default('both')->after('moodle_view_url');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('academic_quizzes') && Schema::hasColumn('academic_quizzes', 'moodle_audience')) {
            Schema::table('academic_quizzes', function (Blueprint $table) {
                $table->dropColumn('moodle_audience');
            });
        }

        if (Schema::hasTable('lesson_files') && Schema::hasColumn('lesson_files', 'moodle_audience')) {
            Schema::table('lesson_files', function (Blueprint $table) {
                $table->dropColumn('moodle_audience');
            });
        }

        if (Schema::hasTable('unit_general_files') && Schema::hasColumn('unit_general_files', 'moodle_audience')) {
            Schema::table('unit_general_files', function (Blueprint $table) {
                $table->dropColumn('moodle_audience');
            });
        }

        if (Schema::hasTable('subject_general_files') && Schema::hasColumn('subject_general_files', 'moodle_audience')) {
            Schema::table('subject_general_files', function (Blueprint $table) {
                $table->dropColumn('moodle_audience');
            });
        }
    }
};
