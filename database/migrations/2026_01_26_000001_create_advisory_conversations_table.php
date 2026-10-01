<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdvisoryConversationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advisory_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guardian_id')->nullable(); // ولي الأمر
            $table->unsignedBigInteger('student_id')->nullable(); // الطالب
            $table->unsignedBigInteger('advisor_id')->nullable(); // المرشد/المعلم
            $table->string('subject', 255);
            $table->text('description')->nullable();
            $table->enum('status', ['open', 'in_progress', 'closed', 'pending'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('guardian_id');
            $table->index('student_id');
            $table->index('advisor_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('advisory_conversations');
    }
}
