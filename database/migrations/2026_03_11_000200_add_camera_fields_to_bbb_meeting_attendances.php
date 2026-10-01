<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCameraFieldsToBbbMeetingAttendances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bbb_meeting_attendances', function (Blueprint $table) {
            if (!Schema::hasColumn('bbb_meeting_attendances', 'camera_on_count')) {
                $table->unsignedInteger('camera_on_count')->default(0)->after('left_at');
            }
            if (!Schema::hasColumn('bbb_meeting_attendances', 'first_camera_on_at')) {
                $table->timestamp('first_camera_on_at')->nullable()->after('camera_on_count');
            }
            if (!Schema::hasColumn('bbb_meeting_attendances', 'last_camera_on_at')) {
                $table->timestamp('last_camera_on_at')->nullable()->after('first_camera_on_at');
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
        Schema::table('bbb_meeting_attendances', function (Blueprint $table) {
            if (Schema::hasColumn('bbb_meeting_attendances', 'last_camera_on_at')) {
                $table->dropColumn('last_camera_on_at');
            }
            if (Schema::hasColumn('bbb_meeting_attendances', 'first_camera_on_at')) {
                $table->dropColumn('first_camera_on_at');
            }
            if (Schema::hasColumn('bbb_meeting_attendances', 'camera_on_count')) {
                $table->dropColumn('camera_on_count');
            }
        });
    }
}
