<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::info('Registration attempt', [
            'email' => $request->email,
            'name' => $request->name,
            'ip' => $request->ip()
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Create a profile for the new user
            $user->profile()->create([
                'email' => $request->email,
                'full_name' => $request->name,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return response()->json([
                'user' => $user->load('profile'),
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'email' => $request->email
            ]);
            throw $e;
        }
    }

    public function login(Request $request)
    {
        Log::info('Login attempt', [
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            Log::warning('Failed login attempt', [
                'email' => $request->email,
                'ip' => $request->ip()
            ]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = User::where('email', $request->email)
            ->with('profile')
            ->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        Log::info('User logged in successfully', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        Log::info('Logout attempt', [
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'ip' => $request->ip()
        ]);

        $request->user()->currentAccessToken()->delete();

        Log::info('User logged out successfully', [
            'user_id' => $request->user()->id,
            'email' => $request->user()->email
        ]);

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
