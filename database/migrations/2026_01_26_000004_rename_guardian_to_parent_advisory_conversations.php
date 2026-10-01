<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameGuardianToParentAdvisoryConversations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('advisory_conversations', function (Blueprint $table) {
            // Rename guardian_id to parent_id
            $table->renameColumn('guardian_id', 'parent_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('advisory_conversations', function (Blueprint $table) {
            // Revert back to guardian_id
            $table->renameColumn('parent_id', 'guardian_id');
        });
    }
}
