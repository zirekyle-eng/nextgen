<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvisoryNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advisory_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id'); // يشير إلى advisory_conversations
            $table->unsignedBigInteger('advisor_id'); // يشير إلى users
            $table->enum('note_type', ['observation', 'recommendation', 'warning', 'praise', 'general'])->default('general');
            $table->string('title', 255)->nullable();
            $table->longText('content');
            $table->boolean('is_visible_to_guardian')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('conversation_id');
            $table->index('advisor_id');
            $table->index('note_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advisory_notes');
    }
}
