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
            'branch_name'       => 'Cabdin Singkil',
            'email'             => 'cabdin@gmail.com',
            'password'          => Hash::make('cabdin123'),
            'branch_phone'      => '081234567890',
            'branch_address'    => 'Jl. Raya Singkil',
        ]);

        School::create([
            'branch_id' => 1,
            'school_npsn' => '1234567890',
            'school_name' => 'Sekolah Singkil',
            'email' => 'sekolah@gmail.com',
            'password' => Hash::make('sekolah123'),
            'school_phone' => '081234567890',
            'school_address' => 'Jl. Raya Singkil',
        ]);

        Employee::create([
            'branch_id' => 1,
            'school_id' => 1,
            'employee_name' => 'Puguh Sulistyo Pambudi, S.Kom',
            'email' => 'puguhedu@gmail.com',
            'password' => Hash::make('employee123'),
            'pass_text' => 'employee123',
            'employee_type' => 'Kepala Sekolah',
        ]);

        Student::create([
            'branch_id' => 1,
            'school_id' => 1,
            'student_name' => 'Rasyid Diansyah Waskito Pambudi',
            'student_nisn' => '1234567890',
            'username' => 'rasyid',
            'password' => Hash::make('student123'),
            'pass_text' => 'student123',
            'student_gender' => 'Laki-laki',
            'student_photo' => 'https://via.placeholder.com/150',
        ]);

        Exam::create([
            'exam_type' => 'TKA',
            'exam_name' => 'TRyout TKA SMA',
            'exam_description' => 'Ujian Akhir Semester',
            'exam_code' => 'TRYTKA',
            'exam_status' => 'Active',
        ]);

        Subject::create([
            'subject_name' => 'Matematika',
            'subject_code' => 'MTK',
        ]);

        ExamSession::create([
            'exam_id' => 1,
            'subject_id' => 1,
            'session_number' => '1',
            'session_duration' => 120,
            'session_start_time' => '2025-01-01 08:00:00',
            'session_end_time' => '2025-01-01 10:00:00',
            'random_question' => 'Y',
            'random_answer' => 'Y',
            'show_result' => 'Y',
            'show_score' => 'Y',
            'session_status' => 'Active',
        ]);
    }
}
