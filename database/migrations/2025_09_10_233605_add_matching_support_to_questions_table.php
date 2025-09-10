<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify question_type enum to include 'matching'
        DB::statement("ALTER TABLE questions MODIFY COLUMN question_type ENUM('multiple_choice','true_false','multiple_response','tkp','matching') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'matching' from question_type enum
        DB::statement("ALTER TABLE questions MODIFY COLUMN question_type ENUM('multiple_choice','true_false','multiple_response','tkp') NOT NULL");
    }
};