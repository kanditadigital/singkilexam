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
        Schema::table('exam_participants', function (Blueprint $table) {
            $table->foreignId('exam_session_id')->nullable()->constrained('exam_sessions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_participants', function (Blueprint $table) {
            $table->dropForeign(['exam_session_id']);
            $table->dropColumn('exam_session_id');
        });
    }
};
