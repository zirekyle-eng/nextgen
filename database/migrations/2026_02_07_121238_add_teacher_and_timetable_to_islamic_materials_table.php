<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTeacherAndTimetableToIslamicMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('islamic_materials', function (Blueprint $table) {
            $table->string('teacher')->nullable()->comment('Teacher name or ID');
            $table->text('time_table')->nullable()->comment('Time table or schedule');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('islamic_materials', function (Blueprint $table) {
            $table->dropColumn('teacher');
            $table->dropColumn('time_table');
        });
    }
}
