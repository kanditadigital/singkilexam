<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('employees')
            ->select('id', 'pass_text')
            ->whereNotNull('pass_text')
            ->lazyById()
            ->each(function ($employee) {
                if (!empty($employee->pass_text)) {
                    DB::table('employees')
                        ->where('id', $employee->id)
                        ->update(['password' => Hash::make($employee->pass_text)]);
                }
            });
    }

    public function down(): void
    {
        // Passwords derived from "pass_text" cannot be reverted to the previous double-hashed state.
    }
};
