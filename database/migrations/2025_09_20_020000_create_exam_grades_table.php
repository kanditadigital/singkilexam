<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->unique()->constrained('exam_attempts')->onDelete('cascade');
            $table->unsignedInteger('total_questions');
            $table->unsignedInteger('answered_questions');
            $table->unsignedInteger('correct_questions')->default(0);
            $table->decimal('score', 6, 2)->default(0); // percent 0-100
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_grades');
    }
};

