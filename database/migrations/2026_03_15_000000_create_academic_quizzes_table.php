<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_quizzes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('week_unit_id')->nullable();
            $table->unsignedBigInteger('unit_lesson_id')->nullable();
            $table->string('title', 255);
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->string('question_file_path');
            $table->string('question_original_name')->nullable();
            $table->string('question_mime_type')->nullable();
            $table->unsignedBigInteger('question_size')->nullable();
            $table->string('xml_file_path');
            $table->unsignedInteger('moodle_cmid')->nullable();
            $table->unsignedInteger('moodle_instance_id')->nullable();
            $table->string('moodle_view_url')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index('subject_id');
            $table->index('week_unit_id');
            $table->index('unit_lesson_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_quizzes');
    }
};
