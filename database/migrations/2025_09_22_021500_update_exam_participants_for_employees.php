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
        Schema::disableForeignKeyConstraints();

        Schema::table('exam_participants', function (Blueprint $table) {
            $table->string('participant_type')->nullable()->after('school_id');
            $table->unsignedBigInteger('participant_id')->nullable()->after('participant_type');
        });

        DB::table('exam_participants')->update([
            'participant_type' => Student::class,
            'participant_id' => DB::raw('student_id'),
        ]);

        Schema::table('exam_participants', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropUnique('exam_participants_exam_student_unique');
            $table->dropColumn('student_id');
            $table->unique(['exam_id', 'participant_type', 'participant_id'], 'exam_participants_unique_participant');
        });

        Schema::table('exam_participant_logs', function (Blueprint $table) {
            $table->string('participant_type')->nullable()->after('school_id');
            $table->unsignedBigInteger('participant_id')->nullable()->after('participant_type');
        });

        DB::table('exam_participant_logs')->update([
            'participant_type' => Student::class,
            'participant_id' => DB::raw('student_id'),
        ]);

        Schema::table('exam_participant_logs', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('exam_participant_logs', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('school_id')->constrained('students')->nullOnDelete();
        });

        DB::table('exam_participant_logs')->whereNotNull('participant_id')->update([
            'student_id' => DB::raw('participant_id'),
        ]);

        Schema::table('exam_participant_logs', function (Blueprint $table) {
            $table->dropColumn(['participant_type', 'participant_id']);
        });

        Schema::table('exam_participants', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('school_id')->constrained('students')->nullOnDelete();
        });

        DB::table('exam_participants')->whereNotNull('participant_id')->update([
            'student_id' => DB::raw('participant_id'),
        ]);

        Schema::table('exam_participants', function (Blueprint $table) {
            $table->dropUnique('exam_participants_unique_participant');
            $table->dropColumn(['participant_type', 'participant_id']);
            $table->unique(['exam_id', 'student_id'], 'exam_participants_exam_student_unique');
        });

        Schema::enableForeignKeyConstraints();
    }
};
