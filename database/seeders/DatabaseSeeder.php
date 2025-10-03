<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\School;
use App\Models\Student;
use App\Models\Employee;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\ExamSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Puguh Sulistyo Pambudi',
            'email' => 'puguh@gmail.com',
            'password' => Hash::make('puguh123'),
        ]);

        Branch::create([
            'branch_name' => 'Cabdin Aceh Singkil',
            'email' => 'cabdin@gmail.com',
            'password' => Hash::make('cabdin*'),
            'branch_phone' => '081234567890',
            'branch_address' => 'Jl. Raya Singkil',
        ]);

        School::create([
            'branch_id' => 1,
            'school_npsn' => '69988418',
            'school_name' => 'SMK Teknologi Al-Ishaqi',
            'email' => 'smktalishaqi@gmail.com',
            'password' => Hash::make('sekolah*'),
            'school_phone' => '081265683277',
            'school_address' => 'Jl. H. Ishaq',
            'is_active' => true,
        ]);
    }
}
