<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('participant_type');
            $table->unsignedBigInteger('participant_id');
            $table->foreignId('created_by')->nullable()->constrained('schools')->nullOnDelete();
            $table->timestamps();

            $table->unique(['exam_id', 'participant_type', 'participant_id'], 'exam_participants_unique_participant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_participants');
    }
};
