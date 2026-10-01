<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_general_files', function (Blueprint $table) {
            $table->unsignedInteger('moodle_cmid')->nullable()->after('uploaded_by');
        });

        Schema::table('unit_general_files', function (Blueprint $table) {
            $table->unsignedInteger('moodle_cmid')->nullable()->after('uploaded_by');
        });

        Schema::table('lesson_files', function (Blueprint $table) {
            $table->unsignedInteger('moodle_cmid')->nullable()->after('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_files', function (Blueprint $table) {
            $table->dropColumn('moodle_cmid');
        });

        Schema::table('unit_general_files', function (Blueprint $table) {
            $table->dropColumn('moodle_cmid');
        });

        Schema::table('subject_general_files', function (Blueprint $table) {
            $table->dropColumn('moodle_cmid');
        });
    }
};
