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
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('schools')->nullOnDelete();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id'], 'exam_participants_exam_student_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_participants');
    }
};
