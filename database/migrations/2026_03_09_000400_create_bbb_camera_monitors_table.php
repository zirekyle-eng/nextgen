<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBbbCameraMonitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bbb_camera_monitors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('meeting_id');
            $table->unsignedBigInteger('student_user_id');
            $table->timestamp('camera_off_since')->nullable();
            $table->timestamp('alert_sent_at')->nullable();
            $table->string('last_event_name')->nullable();
            $table->timestamps();

            $table->unique(['meeting_id', 'student_user_id'], 'bbb_cam_meeting_student_unique');
            $table->index(['camera_off_since', 'alert_sent_at'], 'bbb_cam_alert_scan_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bbb_camera_monitors');
    }
}
