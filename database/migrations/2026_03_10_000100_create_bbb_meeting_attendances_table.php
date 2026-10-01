<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBbbMeetingAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bbb_meeting_attendances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('meeting_id');
            $table->unsignedBigInteger('student_user_id');
            $table->timestamp('join_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->timestamp('scheduled_start_at')->nullable();
            $table->timestamp('scheduled_end_at')->nullable();
            $table->unsignedInteger('late_minutes')->default(0);
            $table->string('status')->nullable(); // present | late | absent
            $table->timestamp('notified_late_realtime_at')->nullable();
            $table->timestamp('notified_present_after_class_at')->nullable();
            $table->timestamp('notified_late_after_class_at')->nullable();
            $table->timestamp('notified_absent_after_class_at')->nullable();
            $table->timestamps();

            $table->unique(['meeting_id', 'student_user_id'], 'bbb_meeting_student_unique');
            $table->index(['meeting_id', 'student_user_id'], 'bbb_meeting_student_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bbb_meeting_attendances');
    }
}
