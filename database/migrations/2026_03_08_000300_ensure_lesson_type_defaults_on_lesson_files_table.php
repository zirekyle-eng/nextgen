<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lesson_files')) {
            return;
        }

        if (!Schema::hasColumn('lesson_files', 'lesson_type')) {
            Schema::table('lesson_files', function (Blueprint $table) {
                $table->string('lesson_type', 60)->default('digital_resources')->after('title');
            });
            return;
        }

        DB::table('lesson_files')
            ->whereNull('lesson_type')
            ->orWhere('lesson_type', '')
            ->update(['lesson_type' => 'digital_resources']);

        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE lesson_files MODIFY lesson_type VARCHAR(60) NOT NULL DEFAULT 'digital_resources'");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('lesson_files') || !Schema::hasColumn('lesson_files', 'lesson_type')) {
            return;
        }

        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE lesson_files MODIFY lesson_type VARCHAR(60) NULL DEFAULT NULL");
        }
    }
};

