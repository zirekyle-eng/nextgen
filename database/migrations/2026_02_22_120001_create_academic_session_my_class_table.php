<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_session_my_class', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_session_id');
            $table->unsignedInteger('my_class_id');
            $table->timestamps();

            $table->unique(['academic_session_id', 'my_class_id'], 'session_class_unique');
            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->onDelete('cascade');
            $table->foreign('my_class_id')->references('id')->on('my_classes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_session_my_class');
    }
};
