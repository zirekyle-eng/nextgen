<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subject_general_files') || Schema::hasColumn('subject_general_files', 'academic_session_id')) {
            return;
        }

        Schema::table('subject_general_files', function (Blueprint $table) {
            $table->unsignedBigInteger('academic_session_id')->nullable()->after('subject_id');
            $table->index(['academic_session_id', 'subject_id'], 'subject_general_files_session_subject_idx');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('subject_general_files') || !Schema::hasColumn('subject_general_files', 'academic_session_id')) {
            return;
        }

        Schema::table('subject_general_files', function (Blueprint $table) {
            $table->dropIndex('subject_general_files_session_subject_idx');
            $table->dropColumn('academic_session_id');
        });
    }
};
