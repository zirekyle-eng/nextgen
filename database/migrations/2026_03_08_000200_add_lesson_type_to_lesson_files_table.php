<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lesson_files') || Schema::hasColumn('lesson_files', 'lesson_type')) {
            return;
        }

        try {
            Schema::table('lesson_files', function (Blueprint $table) {
                $table->string('lesson_type', 60)->nullable()->after('title');
            });
        } catch (QueryException $e) {
            if (stripos((string) $e->getMessage(), 'Duplicate column name') === false) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lesson_files') && Schema::hasColumn('lesson_files', 'lesson_type')) {
            Schema::table('lesson_files', function (Blueprint $table) {
                $table->dropColumn('lesson_type');
            });
        }
    }
};
