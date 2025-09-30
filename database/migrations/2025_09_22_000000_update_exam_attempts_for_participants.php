<?php

use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->string('participant_type')->nullable()->after('id');
            $table->unsignedBigInteger('participant_id')->nullable()->after('participant_type');
        });

        DB::table('exam_attempts')->update([
            'participant_type' => Student::class,
            'participant_id' => DB::raw('student_id'),
        ]);

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
            $table->unique([
                'participant_type',
                'participant_id',
                'exam_session_id',
            ], 'exam_attempts_participant_unique');
        });
    }

    public function down(): void
    {
        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('id')->constrained('students')->nullOnDelete();
            $table->dropUnique('exam_attempts_participant_unique');
        });

        DB::table('exam_attempts')->whereNotNull('participant_id')->update([
            'student_id' => DB::raw('participant_id'),
        ]);

        Schema::table('exam_attempts', function (Blueprint $table) {
            $table->dropColumn(['participant_type', 'participant_id']);
        });
    }
};
