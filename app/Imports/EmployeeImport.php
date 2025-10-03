<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToModel, WithHeadingRow
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
        return new Employee([
            'branch_id' => $this->branch,
            'school_id' => $this->school,
            'employee_name' => $row['nama_guru'] ?? $row['employee_name'],
            'email' => $row['email'] ?? null,
            'username' => $row['username'] ?? $row['nama_guru'],
            'employee_phone' => $row['telepon'] ?? $row['employee_phone'] ?? null,
            'employee_type' => $row['tipe'] ?? $row['employee_type'] ?? 'Guru',
            'password' => Hash::make('password123'), // default password
        ]);
    }
}
