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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->string('question_category')->nullable(); // Literasi, Numerasi, Teknis, Pedagogik
            $table->enum('question_type',['multiple_choice','true_false','multiple_response']);
            $table->enum('question_format',['text','image','text_image'])->default('text'); // Format soal
            $table->enum('option_format',['text','image','text_image'])->default('text'); // Format pilihan jawaban
            $table->longText('question_text')->nullable();
            $table->string('question_image')->nullable();
            $table->string('slug');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
