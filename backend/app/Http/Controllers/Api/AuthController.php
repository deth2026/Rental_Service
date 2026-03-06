<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:customer,shop_owner,admin'
        ]);

        $normalizedEmail = strtolower(trim((string) $payload['email']));
        $exists = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->exists();
        if ($exists) {
            throw ValidationException::withMessages([
                'email' => ['This email is already registered. Please login.'],
            ]);
        }

        $user = User::create([
            'name' => trim((string) $payload['name']),
            'email' => $normalizedEmail,
            'phone' => trim((string) $payload['phone']),
            'password' => Hash::make((string) $payload['password']),
            'role' => $payload['role'],
            'is_verified' => false,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful! Please check your email to verify your account.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();
        $passwordMatches = false;

        if ($user) {
            $storedPassword = (string) $user->password;
            $passwordMatches = Hash::check($request->password, $storedPassword);

            // Fallback for legacy plaintext passwords and auto-upgrade to hashed.
            if (!$passwordMatches) {
                $looksHashed =
                    str_starts_with($storedPassword, '$2y$') ||
                    str_starts_with($storedPassword, '$2a$') ||
                    str_starts_with($storedPassword, '$2b$') ||
                    str_starts_with($storedPassword, '$argon2i$') ||
                    str_starts_with($storedPassword, '$argon2id$');

                if (!$looksHashed && hash_equals($storedPassword, (string) $request->password)) {
                    $passwordMatches = true;
                    $user->password = Hash::make($request->password);
                    $user->save();
                }
            }
        }

        if (!$user || !$passwordMatches) {
            throw ValidationException::withMessages([
                'password' => ['Incorrect password.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
