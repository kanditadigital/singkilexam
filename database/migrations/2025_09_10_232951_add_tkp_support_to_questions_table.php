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
        // Modify question_type enum to include 'tkp'
        DB::statement("ALTER TABLE questions MODIFY COLUMN question_type ENUM('multiple_choice','true_false','multiple_response','tkp') NOT NULL");
        
        // Update question_category to include 'TKP' if not already there
        // First, check if we need to modify the column structure for question_category
        DB::statement("ALTER TABLE questions MODIFY COLUMN question_category VARCHAR(255) NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'tkp' from question_type enum
        DB::statement("ALTER TABLE questions MODIFY COLUMN question_type ENUM('multiple_choice','true_false','multiple_response') NOT NULL");
        
        // Keep question_category as is since it was already VARCHAR
    }
};