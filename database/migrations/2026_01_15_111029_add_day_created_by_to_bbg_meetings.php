<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDayCreatedByToBbgMeetings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bbg_meetings', function (Blueprint $table) {
            if (!Schema::hasColumn('bbg_meetings', 'day')) {
                $table->string('day')->nullable()->after('tt_id');
            }
            if (!Schema::hasColumn('bbg_meetings', 'created_by')) {
                $table->unsignedInteger('created_by')->nullable()->after('attendee_password');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
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
            if (Schema::hasColumn('bbg_meetings', 'created_by')) {
                $table->dropForeignKeyIfExists('bbg_meetings_created_by_foreign');
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('bbg_meetings', 'day')) {
                $table->dropColumn('day');
            }
        });
    }
}
