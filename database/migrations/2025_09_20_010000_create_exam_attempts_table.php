<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('participant_type');
            $table->unsignedBigInteger('participant_id');
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('exam_session_id')->constrained('exam_sessions')->onDelete('cascade');
            $table->enum('status', ['in_progress', 'submitted'])->default('in_progress');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['participant_type', 'participant_id', 'exam_session_id'], 'exam_attempts_participant_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
