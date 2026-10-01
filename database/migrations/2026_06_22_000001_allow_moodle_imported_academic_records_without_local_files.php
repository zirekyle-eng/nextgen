<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('unit_general_files')) {
            DB::statement('ALTER TABLE unit_general_files MODIFY file_path VARCHAR(255) NULL');
        }

        if (Schema::hasTable('lesson_files')) {
            DB::statement('ALTER TABLE lesson_files MODIFY file_path VARCHAR(255) NULL');
        }

        if (Schema::hasTable('academic_quizzes')) {
            DB::statement('ALTER TABLE academic_quizzes MODIFY xml_file_path VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('unit_general_files')) {
            DB::statement("UPDATE unit_general_files SET file_path = '' WHERE file_path IS NULL");
            DB::statement('ALTER TABLE unit_general_files MODIFY file_path VARCHAR(255) NOT NULL');
        }

        if (Schema::hasTable('lesson_files')) {
            DB::statement("UPDATE lesson_files SET file_path = '' WHERE file_path IS NULL");
            DB::statement('ALTER TABLE lesson_files MODIFY file_path VARCHAR(255) NOT NULL');
        }

        if (Schema::hasTable('academic_quizzes')) {
            DB::statement("UPDATE academic_quizzes SET xml_file_path = '' WHERE xml_file_path IS NULL");
            DB::statement('ALTER TABLE academic_quizzes MODIFY xml_file_path VARCHAR(255) NOT NULL');
        }
    }
};
