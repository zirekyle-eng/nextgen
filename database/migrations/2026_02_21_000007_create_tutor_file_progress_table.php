<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTutorFileProgressTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('tutor_file_progress')) {
            Schema::create('tutor_file_progress', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->unsignedBigInteger('file_id');
                $table->string('subject');
                $table->string('status')->default('not_started');
                $table->timestamp('started_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('last_activity_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'file_id']);
                $table->index(['user_id', 'subject']);
                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('tutor_file_progress');
    }
}

