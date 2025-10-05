<?php

namespace App\Http\Controllers\Sch;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SchProfileController extends Controller
{
    public function edit()
    {
        $school = Auth::guard('schools')->user();

        return view('school.profile.edit', [
            'title' => 'Profil Sekolah',
            'school' => $school,
        ]);
    }

    public function update(Request $request)
    {
        $school = Auth::guard('schools')->user();

        $request->validate([
            'school_name' => 'required|string|max:255',
            'email' => 'required|email|unique:schools,email,' . $school->id,
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $payload = [
            'school_name' => $request->school_name,
            'email' => $request->email,
        ];

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $school->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak cocok.']);
            }
            $payload['password'] = Hash::make($request->new_password);
        }

        $school->update($payload);

        toast('Profil sekolah berhasil diperbarui', 'success');
        return redirect()->route('sch.profile.edit');
    }
}
