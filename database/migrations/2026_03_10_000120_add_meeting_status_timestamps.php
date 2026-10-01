<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMeetingStatusTimestamps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bbg_meetings', function (Blueprint $table) {
            if (!Schema::hasColumn('bbg_meetings', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('bbg_meetings', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('bbg_meetings', 'attendance_processed_at')) {
                $table->timestamp('attendance_processed_at')->nullable()->after('ended_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bbg_meetings', function (Blueprint $table) {
            if (Schema::hasColumn('bbg_meetings', 'attendance_processed_at')) {
                $table->dropColumn('attendance_processed_at');
            }
            if (Schema::hasColumn('bbg_meetings', 'ended_at')) {
                $table->dropColumn('ended_at');
            }
            if (Schema::hasColumn('bbg_meetings', 'started_at')) {
                $table->dropColumn('started_at');
            }
        });
    }
}
