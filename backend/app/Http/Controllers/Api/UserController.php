<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }
}
