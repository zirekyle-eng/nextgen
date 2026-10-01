<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBbbTermReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bbb_term_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('student_user_id');
            $table->string('term_key', 20);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['student_user_id', 'term_key'], 'bbb_term_report_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bbb_term_reports');
    }
}
