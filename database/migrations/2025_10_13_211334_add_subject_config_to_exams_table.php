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
        Schema::table('exams', function (Blueprint $table) {
            $table->json('subject_config')->nullable()->after('exam_status'); // Store subject selection and question counts
            $table->integer('break_duration')->default(1)->after('subject_config'); // Break duration in minutes between subjects
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['subject_config', 'break_duration']);
        });
    }
};
