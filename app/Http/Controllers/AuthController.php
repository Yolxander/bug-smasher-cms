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
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            Log::info('User created successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // Create a profile for the new user
            $user->profile()->create([
                'email' => $request->email,
                'full_name' => $request->name,
            ]);

            Log::info('User profile created', [
                'user_id' => $user->id,
                'profile_id' => $user->profile->id
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('Registration completed successfully', [
                'user_id' => $user->id,
                'token_created' => true
            ]);

            return response()->json([
                'user' => $user->load('profile'),
                'token' => $token,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Registration validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);
            throw $e;
        }
    }

    public function login(Request $request)
    {
        Log::info('Login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            Log::info('Login validation passed', [
                'email' => $request->email
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                Log::warning('Login failed - Invalid credentials', [
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

            Log::info('User authenticated successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            Log::info('Login completed successfully', [
                'user_id' => $user->id,
                'token_created' => true
            ]);

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Login validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->except(['password'])
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->except(['password'])
            ]);
            throw $e;
        }
    }

    public function logout(Request $request)
    {
        Log::info('Logout attempt', [
            'user_id' => $request->user()->id,
            'ip' => $request->ip()
        ]);

        try {
            $request->user()->currentAccessToken()->delete();

            Log::info('Logout successful', [
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'message' => 'Successfully logged out'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()->id
            ]);
            throw $e;
        }
    }
}
