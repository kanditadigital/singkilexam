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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'email')) {
                $table->string('email')->unique()->after('employee_name');
            }
            if (!Schema::hasColumn('employees', 'employee_phone')) {
                $table->string('employee_phone')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'employee_phone')) {
                $table->dropColumn('employee_phone');
            }
            if (Schema::hasColumn('employees', 'email')) {
                $table->dropUnique(['email']);
                $table->dropColumn('email');
            }
        });
    }
};

