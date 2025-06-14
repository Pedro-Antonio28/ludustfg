<?php

namespace App\Http\Controllers\Api\Director;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DirectorProfileController extends Controller
{
    public function show(Request $request)
    {
        $director = auth()->user();

        $teachers = \App\Models\Teacher::where('director_id', $director->id)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json([
            'name' => $director->name,
            'profile_img' => $director->profile_img,
            'school_name' => $director->school_name,
            'teachers' => $teachers,
        ]);
    }

    public function update(Request $request)
    {
        $director = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'profile_img' => 'nullable|image|max:2048',
        ]);

        $director->name = $validated['name'];

        if ($request->hasFile('profile_img')) {
            $path = $request->file('profile_img')->store('Profile-Images', 'public');
            $director->profile_img = $path;
        }

        $director->save();

        return response()->json([
            'name' => $director->name,
            'profile_img' => $director->profile_img,
        ]);
    }
}
