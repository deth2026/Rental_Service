<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_verified' => false,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
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
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function index()
    {
        return response()->json(User::paginate(15));
    }

    public function store(Request $_request)
    {
        $_request->validate([
            'password' => 'required|string|min:8',
        ]);

        $data = $_request->all();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $record = User::create($data);

        return response()->json($record, 201);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $_request, User $user)
    {
        $_request->validate([
            'password' => 'nullable|string|min:8',
        ]);

        $data = $_request->all();

        // Don't allow email change to avoid duplicate errors
        unset($data['email']);

        if (isset($data['password']) && $data['password'] !== '') {
            // Store the plain password before hashing
            $plainPassword = $data['password'];
            $data['password'] = Hash::make($plainPassword);
            // Save plaintext for display
            $data['plain_password'] = $plainPassword;
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json($user->fresh());
    }

    public function destroy(User $user)
    {
        if ($user->img_url) {
            $storagePath = preg_replace('/^storage\//', '', $user->img_url);
            if ($storagePath) {
                Storage::disk('public')->delete($storagePath);
            }
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    public function uploadAvatar(Request $request, User $user)
    {
        $validated = $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        if ($user->img_url) {
            $oldPath = preg_replace('/^storage\//', '', $user->img_url);
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $storedPath = $validated['avatar']->store('avatars', 'public');
        $user->update([
            'img_url' => $storedPath,
        ]);

        return response()->json([
            'message' => 'Avatar uploaded successfully',
            'user' => $user->fresh(),
        ]);
    }

    public function removeAvatar(Request $request, User $user)
    {
        if ($user->img_url) {
            $oldPath = preg_replace('/^storage\//', '', $user->img_url);
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $user->update(['img_url' => null]);

        return response()->json([
            'message' => 'Avatar removed successfully',
            'user' => $user->fresh(),
        ]);
    }
}
