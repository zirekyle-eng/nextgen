<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddConversationMessagesToTutorLearningProgressTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tutor_learning_progress') && !Schema::hasColumn('tutor_learning_progress', 'conversation_messages')) {
            Schema::table('tutor_learning_progress', function (Blueprint $table) {
                $table->longText('conversation_messages')->nullable()->after('completed_quizzes');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('tutor_learning_progress') && Schema::hasColumn('tutor_learning_progress', 'conversation_messages')) {
            Schema::table('tutor_learning_progress', function (Blueprint $table) {
                $table->dropColumn('conversation_messages');
            });
        }
    }
}

