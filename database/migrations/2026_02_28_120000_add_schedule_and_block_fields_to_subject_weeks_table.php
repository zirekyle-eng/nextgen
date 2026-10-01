<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduleAndBlockFieldsToSubjectWeeksTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('subject_weeks')) {
            return;
        }

        Schema::table('subject_weeks', function (Blueprint $table) {
            if (!Schema::hasColumn('subject_weeks', 'start_date')) {
                $table->date('start_date')->nullable()->after('title');
            }
            if (!Schema::hasColumn('subject_weeks', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('subject_weeks', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false)->after('end_date');
            }
            if (!Schema::hasColumn('subject_weeks', 'block_note')) {
                $table->string('block_note', 255)->nullable()->after('is_blocked');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('subject_weeks')) {
            return;
        }

        Schema::table('subject_weeks', function (Blueprint $table) {
            if (Schema::hasColumn('subject_weeks', 'block_note')) {
                $table->dropColumn('block_note');
            }
            if (Schema::hasColumn('subject_weeks', 'is_blocked')) {
                $table->dropColumn('is_blocked');
            }
            if (Schema::hasColumn('subject_weeks', 'end_date')) {
                $table->dropColumn('end_date');
            }
            if (Schema::hasColumn('subject_weeks', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });
    }
}

