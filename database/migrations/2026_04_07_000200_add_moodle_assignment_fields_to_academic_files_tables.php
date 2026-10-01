<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['subject_general_files', 'unit_general_files', 'lesson_files'];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'moodle_activity_type')) {
                    $table->string('moodle_activity_type', 20)->default('resource')->after('moodle_cmid');
                }
                if (!Schema::hasColumn($tableName, 'activity_intro')) {
                    $table->text('activity_intro')->nullable()->after('moodle_activity_type');
                }
                if (!Schema::hasColumn($tableName, 'available_from')) {
                    $table->timestamp('available_from')->nullable()->after('activity_intro');
                }
                if (!Schema::hasColumn($tableName, 'due_at')) {
                    $table->timestamp('due_at')->nullable()->after('available_from');
                }
                if (!Schema::hasColumn($tableName, 'cutoff_at')) {
                    $table->timestamp('cutoff_at')->nullable()->after('due_at');
                }
                if (!Schema::hasColumn($tableName, 'moodle_view_url')) {
                    $table->string('moodle_view_url')->nullable()->after('cutoff_at');
                }
            });
        }
    }

    public function down(): void
    {
        $tables = ['lesson_files', 'unit_general_files', 'subject_general_files'];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $columns = ['moodle_view_url', 'cutoff_at', 'due_at', 'available_from', 'activity_intro', 'moodle_activity_type'];
                $existingColumns = array_values(array_filter($columns, static function ($column) use ($tableName) {
                    return Schema::hasColumn($tableName, $column);
                }));

                if (!empty($existingColumns)) {
                    $table->dropColumn($existingColumns);
                }
            });
        }
    }
};
