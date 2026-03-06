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
}
