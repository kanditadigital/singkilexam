<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->string('session_number'); //ex. sesi 1, sesi 2, sesi 3, etc
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->dateTime('session_start_time');
            $table->dateTime('session_end_time');
            $table->integer('session_duration');
            $table->enum('random_question', ['Y', 'N'])->default('Y');
            $table->enum('random_answer', ['Y', 'N'])->default('Y');
            $table->enum('show_result', ['Y', 'N'])->default('Y');
            $table->enum('show_score', ['Y', 'N'])->default('Y');
            $table->enum('session_status', ['Active', 'Inactive'])->default('Inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
