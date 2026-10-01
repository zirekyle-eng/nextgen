<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBbbDailyAbsencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bbb_daily_absences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_user_id');
            $table->date('absence_date');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['student_user_id', 'absence_date'], 'bbb_daily_absence_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bbb_daily_absences');
    }
}
