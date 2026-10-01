<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subject_weeks') || Schema::hasColumn('subject_weeks', 'academic_session_id')) {
            return;
        }

        Schema::table('subject_weeks', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_session_id')->nullable()->after('subject_id');
            $table->index(['academic_session_id', 'subject_id', 'week_number'], 'subject_weeks_session_subject_week_idx');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('subject_weeks') || !Schema::hasColumn('subject_weeks', 'academic_session_id')) {
            return;
        }

        Schema::table('subject_weeks', function (Blueprint $table) {
            $table->dropIndex('subject_weeks_session_subject_week_idx');
            $table->dropColumn('academic_session_id');
        });
    }
};
