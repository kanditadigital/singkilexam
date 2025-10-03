<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->string('question_category')->nullable();
            $table->enum('question_type', ['multiple_choice', 'true_false', 'multiple_response', 'tkp', 'matching']);
            $table->enum('question_format', ['text', 'image', 'text_image'])->default('text');
            $table->enum('option_format', ['text', 'image', 'text_image'])->default('text');
            $table->longText('question_text')->nullable();
            $table->string('question_image')->nullable();
            $table->string('slug');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
