<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_holidays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('academic_session_id')->nullable();
            $table->string('name', 160);
            $table->string('holiday_type', 40)->default('term');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('note', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['academic_session_id', 'starts_on', 'ends_on'], 'academic_holidays_session_dates_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_holidays');
    }
};
