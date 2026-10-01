<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_question_banks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_lesson_id');
            $table->string('title', 255);
            $table->string('source_file_path')->nullable();
            $table->string('source_original_name')->nullable();
            $table->string('source_mime_type')->nullable();
            $table->unsignedBigInteger('source_size')->nullable();
            $table->string('xml_file_path');
            $table->unsignedInteger('question_count')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index('unit_lesson_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_question_banks');
    }
};
