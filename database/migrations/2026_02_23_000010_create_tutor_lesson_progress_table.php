<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTutorLessonProgressTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tutor_lesson_progress')) {
            Schema::create('tutor_lesson_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->unsignedBigInteger('lesson_id');
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('subject');
                $table->string('status')->default('not_started');
                $table->string('last_mode')->default('normal');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('last_activity_at')->nullable();
                $table->longText('conversation_messages')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'lesson_id']);
                $table->index(['user_id', 'subject']);
                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('tutor_lesson_progress');
    }
}

