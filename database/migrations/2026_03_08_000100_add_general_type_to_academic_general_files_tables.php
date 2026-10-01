<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subject_general_files') && !Schema::hasColumn('subject_general_files', 'general_type')) {
            Schema::table('subject_general_files', function (Blueprint $table) {
                $table->string('general_type', 60)->nullable()->after('title');
            });
        }

        if (Schema::hasTable('unit_general_files') && !Schema::hasColumn('unit_general_files', 'general_type')) {
            Schema::table('unit_general_files', function (Blueprint $table) {
                $table->string('general_type', 60)->nullable()->after('title');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('unit_general_files') && Schema::hasColumn('unit_general_files', 'general_type')) {
            Schema::table('unit_general_files', function (Blueprint $table) {
                $table->dropColumn('general_type');
            });
        }

        if (Schema::hasTable('subject_general_files') && Schema::hasColumn('subject_general_files', 'general_type')) {
            Schema::table('subject_general_files', function (Blueprint $table) {
                $table->dropColumn('general_type');
            });
        }
    }
};
