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
        return School::where('branch_id', $branchId)->get();
    }

    /**
     * Generate Password for Student and Employee
     */
    public function generatePassword()
    {
        $randomPassword = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        return $randomPassword;
    }
}