<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempt_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained('exam_attempts')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->unsignedInteger('order_index');
            $table->boolean('flagged')->default(false);
            $table->longText('answer')->nullable(); // JSON for multiple answers
            $table->json('options_order')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempt_questions');
    }
};

