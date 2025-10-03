<?php
namespace App\Services;

use App\Models\School;

class KanditaService
{
    /**
     * Get School By Branch for dropdown
     */
    public function getSchoolsByBranch($branchId)
    {
        return School::where('branch_id', $branchId)
            ->orderBy('school_name')
            ->get();
    }

    /**
     * Generate Password for Student and Employee
     */
    public function generatePassword()
    {
        $randomPassword = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        return $randomPassword;
    }

    /**
     * Get rendom exam code 8 digit Huruf Besar dan Kecil
     */
    public function generateExamCode()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomExamCode = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < 5; $i++) {
            $randomExamCode .= $characters[random_int(0, $maxIndex)];
        }

        return $randomExamCode;
    }
}
