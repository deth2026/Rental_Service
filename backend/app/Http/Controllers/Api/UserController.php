<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::paginate(15));
    }

    public function store(Request $_request)
    {
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
        $data = $_request->all();

        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = Hash::make($data['password']);
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
