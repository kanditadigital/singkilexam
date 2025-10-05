<?php

namespace App\Http\Controllers\Cabdin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CabdinProfileController extends Controller
{
    public function edit()
    {
        $branch = Auth::guard('branches')->user();

        return view('cabdin.profile.edit', [
            'title' => 'Profil Cabdin',
            'branch' => $branch,
        ]);
    }

    public function update(Request $request)
    {
        $branch = Auth::guard('branches')->user();

        $request->validate([
            'branch_name' => 'required|string|max:255',
            'email' => 'required|email|unique:branches,email,' . $branch->id,
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $payload = [
            'branch_name' => $request->branch_name,
            'email' => $request->email,
        ];

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $branch->password)) {
                return back()->withErrors(['current_password' => 'Password saat ini tidak cocok.']);
            }
            $payload['password'] = Hash::make($request->new_password);
        }

        $branch->update($payload);

        toast('Profil cabdin berhasil diperbarui', 'success');
        return redirect()->route('cabdin.profile.edit');
    }
}
