<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_quizzes', function (Blueprint $table) {
            $table->timestamp('available_from')->nullable()->after('time_limit_minutes');
            $table->timestamp('available_until')->nullable()->after('available_from');
        });
    }

    public function down(): void
    {
        Schema::table('academic_quizzes', function (Blueprint $table) {
            $table->dropColumn(['available_from', 'available_until']);
        });
    }
};
