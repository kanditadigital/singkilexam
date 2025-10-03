<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentImport implements ToModel, WithHeadingRow
{
    protected $school;
    protected $branch;

    public function __construct($school, $branch)
    {
        $this->school = $school;
        $this->branch = $branch;
    }

    public function model(array $row)
    {
        $gender = $row['jenis_kelamin'] ?? $row['student_gender'] ?? 'L';
        $mappedGender = match (strtoupper($gender)) {
            'L', 'LAKI-LAKI', 'MALE' => 'Laki-laki',
            'P', 'PEREMPUAN', 'FEMALE' => 'Perempuan',
            default => 'Laki-laki', // default to Laki-laki if unknown
        };

        return new Student([
            'branch_id' => $this->branch,
            'school_id' => $this->school,
            'student_name' => $row['nama_siswa'] ?? $row['student_name'],
            'student_nisn' => $row['nisn'] ?? $row['student_nisn'],
            'username' => $row['nisn'] ?? $row['student_nisn'],
            'password' => Hash::make($row['nisn'].'*'), // default password
            'student_gender' => $mappedGender,
            'student_photo' => 'user.png',
        ]);
    }
}
