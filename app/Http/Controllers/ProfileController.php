<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profile;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('profile')->get();
        return response()->json([
            'data' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'profile' => $user->profile ? [
                        'id' => $user->profile->id,
                        'full_name' => $user->profile->full_name,
                        'role' => $user->profile->role,
                        'avatar_url' => $user->profile->avatar_url,
                        'bio' => $user->profile->bio,
                        'onboarding_completed' => $user->profile->onboarding_completed,
                    ] : null
                ];
            })
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'data.attributes.full_name' => 'required|string|max:255',
            'data.attributes.role' => 'required|string|max:255',
            'data.attributes.bio' => 'nullable|string',
            'data.attributes.email' => 'required|email|max:255',
            'data.attributes.onboarding_completed' => 'boolean',
        ]);

        $userId = auth()->id();

        $profile = Profile::create([
            'user_id' => $userId,
            'full_name' => $validated['data']['attributes']['full_name'],
            'role' => $validated['data']['attributes']['role'],
            'bio' => $validated['data']['attributes']['bio'] ?? null,
            'email' => $validated['data']['attributes']['email'],
            'onboarding_completed' => $validated['data']['attributes']['onboarding_completed'] ?? false,
        ]);

        return response()->json([
            'data' => [
                'type' => 'profiles',
                'id' => $profile->id,
                'attributes' => [
                    'full_name' => $profile->full_name,
                    'role' => $profile->role,
                    'bio' => $profile->bio,
                    'email' => $profile->email,
                    'onboarding_completed' => $profile->onboarding_completed,
                ],
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Profile $profile)
    {
        return response()->json($profile);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profile $profile)
    {
        $validated = $request->validate([
            'email' => 'nullable|email',
            'full_name' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'avatar_url' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'onboarding_completed' => 'nullable|boolean'
        ]);

        $profile->update($validated);
        return response()->json($profile);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profile $profile)
    {
        $profile->delete();
        return response()->json(null, 204);
    }
}
