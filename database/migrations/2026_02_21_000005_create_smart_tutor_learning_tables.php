<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmartTutorLearningTables extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tutor_learning_progress')) {
            Schema::create('tutor_learning_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('subject');
                $table->unsignedBigInteger('last_file_id')->nullable();
                $table->string('last_file_name')->nullable();
                $table->string('last_mode')->default('normal');
                $table->decimal('last_quiz_score', 5, 2)->nullable();
                $table->unsignedTinyInteger('last_quiz_total')->default(10);
                $table->unsignedInteger('points')->default(0);
                $table->unsignedInteger('completed_quizzes')->default(0);
                $table->timestamp('last_activity_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'subject']);
                $table->index('last_activity_at');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('last_file_id')->references('id')->on('curriculum_files')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('tutor_quiz_results')) {
            Schema::create('tutor_quiz_results', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('subject');
                $table->unsignedBigInteger('file_id')->nullable();
                $table->string('file_name')->nullable();
                $table->decimal('score', 5, 2);
                $table->unsignedTinyInteger('total')->default(10);
                $table->unsignedTinyInteger('percentage')->default(0);
                $table->unsignedInteger('points_awarded')->default(0);
                $table->text('recommendation')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'subject']);
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('file_id')->references('id')->on('curriculum_files')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('tutor_quiz_results');
        Schema::dropIfExists('tutor_learning_progress');
    }
}
