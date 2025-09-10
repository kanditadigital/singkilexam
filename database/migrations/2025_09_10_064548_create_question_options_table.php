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
        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->string('option_label')->nullable(); // A, B, C, D untuk multiple_choice; True/False untuk true_false; checkbox label untuk multiple_response
            $table->string('option_key')->nullable(); // key unik untuk setiap option
            $table->longText('option_text')->nullable(); // text pilihan jawaban
            $table->string('option_image')->nullable(); // gambar pilihan jawaban (opsional)
            $table->boolean('is_correct')->default(false); // untuk multiple_choice dan true_false
            $table->integer('score')->default(0); // skor untuk setiap pilihan (berguna untuk multiple_response)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_options');
    }
};
