<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateClubSchedulesForRecurring extends Migration
{
    public function up()
    {
        Schema::table('club_schedules', function (Blueprint $table) {
            // إضافة حقول للجداول المتكررة
            $table->boolean('is_recurring')->default(false)->after('end_time');
            $table->enum('recurrence_type', ['daily', 'weekly', 'monthly'])->nullable()->after('is_recurring');
            $table->enum('day_of_week', ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])->nullable()->after('recurrence_type');
            $table->time('schedule_time')->nullable()->after('day_of_week');
            $table->date('recurrence_end_date')->nullable()->after('schedule_time');
        });
    }

    public function down()
    {
        Schema::table('club_schedules', function (Blueprint $table) {
            $table->dropColumn(['is_recurring', 'recurrence_type', 'day_of_week', 'schedule_time', 'recurrence_end_date']);
        });
    }
}
