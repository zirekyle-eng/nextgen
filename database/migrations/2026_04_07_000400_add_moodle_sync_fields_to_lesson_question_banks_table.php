<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lesson_question_banks')) {
            return;
        }

        Schema::table('lesson_question_banks', function (Blueprint $table) {
            if (!Schema::hasColumn('lesson_question_banks', 'moodle_context_id')) {
                $table->unsignedBigInteger('moodle_context_id')->nullable()->after('xml_file_path');
            }
            if (!Schema::hasColumn('lesson_question_banks', 'moodle_category_id')) {
                $table->unsignedBigInteger('moodle_category_id')->nullable()->after('moodle_context_id');
            }
            if (!Schema::hasColumn('lesson_question_banks', 'moodle_category_name')) {
                $table->string('moodle_category_name', 255)->nullable()->after('moodle_category_id');
            }
            if (!Schema::hasColumn('lesson_question_banks', 'moodle_category_url')) {
                $table->string('moodle_category_url')->nullable()->after('moodle_category_name');
            }
            if (!Schema::hasColumn('lesson_question_banks', 'moodle_imported_count')) {
                $table->unsignedInteger('moodle_imported_count')->nullable()->after('question_count');
            }
            if (!Schema::hasColumn('lesson_question_banks', 'moodle_sync_status')) {
                $table->string('moodle_sync_status', 20)->default('pending')->after('moodle_imported_count');
            }
            if (!Schema::hasColumn('lesson_question_banks', 'moodle_sync_error')) {
                $table->text('moodle_sync_error')->nullable()->after('moodle_sync_status');
            }
            if (!Schema::hasColumn('lesson_question_banks', 'moodle_last_synced_at')) {
                $table->timestamp('moodle_last_synced_at')->nullable()->after('moodle_sync_error');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lesson_question_banks')) {
            return;
        }

        Schema::table('lesson_question_banks', function (Blueprint $table) {
            $columns = [
                'moodle_last_synced_at',
                'moodle_sync_error',
                'moodle_sync_status',
                'moodle_imported_count',
                'moodle_category_url',
                'moodle_category_name',
                'moodle_category_id',
                'moodle_context_id',
            ];

            $existing = array_values(array_filter($columns, function (string $column): bool {
                return Schema::hasColumn('lesson_question_banks', $column);
            }));

            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
