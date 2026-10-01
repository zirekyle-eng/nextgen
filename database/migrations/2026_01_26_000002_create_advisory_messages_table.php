<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvisoryMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advisory_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id'); // يشير إلى advisory_conversations
            $table->unsignedBigInteger('sender_id'); // معرف المرسل
            $table->enum('sender_type', ['guardian', 'advisor'])->default('guardian');
            $table->longText('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('conversation_id');
            $table->index('sender_id');
            $table->index('is_read');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advisory_messages');
    }
}
