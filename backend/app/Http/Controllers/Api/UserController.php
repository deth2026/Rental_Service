<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // ─── CRUD ──────────────────────────────────────────────────────────────

    public function index()
    {
        return response()->json(User::paginate(15));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)->mixedCase()->numbers()->symbols()],
            'phone'    => 'nullable|string|max:30',
            'role'     => 'nullable|string|in:customer,admin',
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
        $user->update($request->all());
        return response()->json($user->fresh());
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    // ─── Settings: Update Profile ───────────────────────────────────────────

    public function updateProfile(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validationRules = [
            'name'            => 'sometimes|required|string|max:255',
            'email'           => 'sometimes|required|email|unique:users,email,' . $id,
            'phone'           => 'nullable|string|max:30',
        ];

        // Only validate profile_picture if the column exists
        try {
            if (\Schema::hasColumn('users', 'profile_picture')) {
                $validationRules['profile_picture'] = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
            }
        } catch (\Exception $e) {
            // Ignore schema errors
        }

        $validated = $request->validate($validationRules);

        // Handle file upload only if profile_picture column exists
        if ($request->hasFile('profile_picture')) {
            try {
                if (\Schema::hasColumn('users', 'profile_picture')) {
                    // Remove old file from storage
                    if ($user->profile_picture) {
                        Storage::disk('public')->delete($user->profile_picture);
                    }
                    $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                    $validated['profile_picture'] = $path;
                }
            } catch (\Exception $e) {
                // Ignore storage errors, continue with other updates
            }
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $user->fresh(),
        ]);
    }

    // ─── Settings: Change Password ──────────────────────────────────────────

    public function changePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

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
