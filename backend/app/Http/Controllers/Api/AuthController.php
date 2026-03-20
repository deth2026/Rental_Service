<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;
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
            'role' => 'nullable|in:customer,shop_owner,admin'
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
            'role' => $payload['role'] ?? 'customer',
            'is_verified' => false,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        try {
            NotificationService::userRegistered($user);
        } catch (\Throwable $exception) {
            Log::warning('Failed to send admin registration notification', [
                'error' => $exception->getMessage(),
                'user_id' => $user->id,
            ]);
        }

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

        $normalizedEmail = strtolower(trim((string) $request->email));
        $plainPassword = (string) $request->password;
        $trimmedPassword = trim($plainPassword);
        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();
        $passwordMatches = false;

        if ($user) {
            $storedPassword = (string) $user->password;
            $passwordMatches =
                Hash::check($plainPassword, $storedPassword) ||
                Hash::check($trimmedPassword, $storedPassword) ||
                password_verify($plainPassword, $storedPassword) ||
                password_verify($trimmedPassword, $storedPassword);

            // Fallback for legacy plaintext passwords and auto-upgrade to hashed.
            if (!$passwordMatches) {
                $looksHashed =
                    str_starts_with($storedPassword, '$2y$') ||
                    str_starts_with($storedPassword, '$2a$') ||
                    str_starts_with($storedPassword, '$2b$') ||
                    str_starts_with($storedPassword, '$argon2i$') ||
                    str_starts_with($storedPassword, '$argon2id$');

                if (
                    !$looksHashed &&
                    (
                        hash_equals($storedPassword, $plainPassword) ||
                        hash_equals($storedPassword, $trimmedPassword)
                    )
                ) {
                    $passwordMatches = true;
                    $user->password = Hash::make($trimmedPassword !== '' ? $trimmedPassword : $plainPassword);
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
        
        // Update last login time
        $user->last_login = now();
        $user->save();

        return response()->json([
            'user' => $user,
            'token' => $token,
            'last_login' => $user->last_login,
        ]);
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
                'user' => null,
            ], 401);
        }
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'job_title' => $user->job_title,
                'bio' => $user->bio,
                'is_verified' => $user->is_verified,
                'profile_picture' => $user->profile_picture,
                'avatar_url' => $user->avatar_url,
                'last_login' => $user->last_login,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
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
