<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentGradeSnapshotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_grade_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id'); // يشير إلى advisory_conversations
            $table->unsignedBigInteger('student_id'); // الطالب
            $table->unsignedBigInteger('subject_id')->nullable(); // المادة
            $table->unsignedBigInteger('exam_id')->nullable(); // الفحص
            $table->decimal('t1', 8, 2)->nullable();
            $table->decimal('t2', 8, 2)->nullable();
            $table->decimal('t3', 8, 2)->nullable();
            $table->decimal('t4', 8, 2)->nullable();
            $table->decimal('tca', 8, 2)->nullable();
            $table->decimal('exm', 8, 2)->nullable();
            $table->decimal('total', 8, 2)->nullable();
            $table->string('grade', 10)->nullable();
            $table->decimal('percentage', 8, 2)->nullable();
            $table->date('snapshot_date');
            $table->timestamps();
            
            // Indexes
            $table->index('conversation_id');
            $table->index('student_id');
            $table->index('exam_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_grade_snapshots');
    }
}
