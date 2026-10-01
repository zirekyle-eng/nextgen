<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSessionFieldsToTutorFileProgressTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tutor_file_progress')) {
            Schema::table('tutor_file_progress', function (Blueprint $table) {
                if (!Schema::hasColumn('tutor_file_progress', 'last_mode')) {
                    $table->string('last_mode')->default('normal')->after('status');
                }
                if (!Schema::hasColumn('tutor_file_progress', 'conversation_messages')) {
                    $table->longText('conversation_messages')->nullable()->after('last_activity_at');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('tutor_file_progress')) {
            Schema::table('tutor_file_progress', function (Blueprint $table) {
                if (Schema::hasColumn('tutor_file_progress', 'conversation_messages')) {
                    $table->dropColumn('conversation_messages');
                }
                if (Schema::hasColumn('tutor_file_progress', 'last_mode')) {
                    $table->dropColumn('last_mode');
                }
            });
        }
    }
}

