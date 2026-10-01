<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoodleCourseIdToSubjectsTable extends Migration
{
    public function up()
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'moodle_course_id')) {
                $table->unsignedInteger('moodle_course_id')->nullable()->after('teacher_id');
            }
        });
    }

    public function down()
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (Schema::hasColumn('subjects', 'moodle_course_id')) {
                $table->dropColumn('moodle_course_id');
            }
        });
    }
}

