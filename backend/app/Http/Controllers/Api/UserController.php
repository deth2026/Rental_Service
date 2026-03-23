<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
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
        // Debug: log the raw input
        $allInput = $request->all();
        error_log("Login input: " . json_encode($allInput));
        
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $normalizedEmail = strtolower(trim((string) $request->email));
        $plainPassword = (string) $request->password;
        $trimmedPassword = trim($plainPassword);
        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();
        
        error_log("User found: " . ($user ? $user->email : "null"));
        if ($user) {
            error_log("Stored password: " . $user->password);
            error_log("Hash::check result: " . (Hash::check($plainPassword, $user->password) ? "true" : "false"));
        }
        
        $isValidPassword = false;

        if ($user) {
            if (
                Hash::check($plainPassword, (string) $user->password) ||
                Hash::check($trimmedPassword, (string) $user->password) ||
                password_verify($plainPassword, (string) $user->password) ||
                password_verify($trimmedPassword, (string) $user->password)
            ) {
                $isValidPassword = true;
            } elseif (
                hash_equals((string) $user->password, $plainPassword) ||
                hash_equals((string) $user->password, $trimmedPassword)
            ) {
                // Backward compatibility: migrate legacy plain-text passwords to hashed format.
                $isValidPassword = true;
                $user->password = Hash::make($trimmedPassword !== '' ? $trimmedPassword : $plainPassword);
                $user->save();
            }
        }

        if (!$user || !$isValidPassword) {
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

    // ─── CRUD ──────────────────────────────────────────────────────────────

    public function index()
    {
        $users = User::orderByDesc('id')->get();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
            'phone'    => 'nullable|string|max:30',
            'role'     => 'nullable|string|in:customer,admin,shop_owner,shop_staff',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        return response()->json($user, 201);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'password' => 'nullable|string|min:8',
        ]);

        $data = $request->all();

        // Don't allow email change to avoid duplicate errors
        unset($data['email']);

        if (isset($data['password']) && $data['password'] !== '') {
            // Store the plain password before hashing
            $plainPassword = $data['password'];
            $data['password'] = Hash::make($plainPassword);
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

    // ─── Settings: Update Profile ───────────────────────────────────────────

    public function updateProfile(Request $request, $id = null)
    {
        // Use authenticated user if no ID provided, otherwise verify ownership
        $authenticatedUser = $request->user();
        
        // If ID is provided, ensure it matches the authenticated user
        if ($id && $id != $authenticatedUser->id) {
            return response()->json(['message' => 'Unauthorized to update this profile'], 403);
        }
        
        $user = $authenticatedUser;

        // Use the authenticated user's ID for validation
        $userId = $authenticatedUser->id;
        
        $validationRules = [
            'name'            => 'sometimes|required|string|max:255',
            'email'           => 'sometimes|required|email|unique:users,email,' . $userId,
            'phone'           => 'nullable|string|max:30',
            'job_title'       => 'nullable|string|max:255',
            'bio'             => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];

        $validated = $request->validate($validationRules);

        // Handle file upload - store in profile_picture column
        error_log('hasFile profile_picture: ' . ($request->hasFile('profile_picture') ? 'true' : 'false'));
        
        if ($request->hasFile('profile_picture')) {
            try {
                // Validate the file manually to get better error messages
                $file = $request->file('profile_picture');
                error_log('File info: ' . json_encode([
                    'name' => $file->getClientOriginalName(),
                    'isValid' => $file->isValid(),
                    'error' => $file->getError(),
                    'size' => $file->getSize()
                ]));
                
                if (!$file->isValid()) {
                    throw new \Exception('Uploaded file is not valid');
                }
                
                // Remove old file from storage
                if ($user->profile_picture) {
                    Storage::disk('public')->delete($user->profile_picture);
                }
                $path = $file->store('profile_pictures', 'public');
                error_log('Stored path: ' . $path);
                $validated['profile_picture'] = $path;
                error_log('Validated profile_picture: ' . ($validated['profile_picture'] ?? 'NOT SET'));
            } catch (\Exception $e) {
                \Log::error('Profile picture upload error: ' . $e->getMessage());
                throw new \Exception('Failed to upload profile picture: ' . $e->getMessage());
            }
        } else {
            error_log('No file uploaded - checking request all');
            error_log('Request all: ' . json_encode($request->all()));
        }

        $user->update($validated);

        $updatedUser = $user->fresh();
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => [
                'id' => $updatedUser->id,
                'name' => $updatedUser->name,
                'email' => $updatedUser->email,
                'phone' => $updatedUser->phone,
                'role' => $updatedUser->role,
                'job_title' => $updatedUser->job_title,
                'bio' => $updatedUser->bio,
                'is_verified' => $updatedUser->is_verified,
                'profile_picture' => $updatedUser->profile_picture,
                'avatar_url' => $updatedUser->avatar_url,
                'last_login' => User::hasLastLoginColumn() ? $updatedUser->last_login : null,
                'created_at' => $updatedUser->created_at,
                'updated_at' => $updatedUser->updated_at,
            ],
        ]);
    }

    // ─── Settings: Change Password ──────────────────────────────────────────

    public function changePassword(Request $request, $id = null)
    {
        // Use authenticated user if no ID provided, otherwise verify ownership
        $authenticatedUser = $request->user();
        
        // If ID is provided, ensure it matches the authenticated user
        if ($id && $id != $authenticatedUser->id) {
            return response()->json(['message' => 'Unauthorized to change this password'], 403);
        }
        
        $user = $authenticatedUser;

        $request->validate([
            'current_password'          => 'required|string',
            'new_password'              => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()   // uppercase + lowercase
                    ->numbers()     // at least one digit
                    ->symbols(),    // at least one special character
            ],
        ]);

        // Verify the current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect',
            ], 422);
        }

        // Prevent reusing the same password
        if (Hash::check($request->new_password, $user->password)) {
            return response()->json([
                'message' => 'New password must be different from the current password',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password changed successfully',
        ]);
    }
}
