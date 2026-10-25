<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** POST /api/login  { username, password } → { token, user } */
    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $data['username'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['username' => 'Invalid username or password.']);
        }

        if ($user->status !== 'Active') {
            throw ValidationException::withMessages(['username' => 'This account is not active.']);
        }

        $token = $user->createToken('frontend')->plainTextToken;
        auth()->setUser($user);
        AuditLog::record('User Login', "{$user->name} signed in.");

        return response()->json(['token' => $token, 'user' => $this->present($user)]);
    }

    /** GET /api/me */
    public function me(Request $request)
    {
        return response()->json($this->present($request->user()));
    }

    /** POST /api/logout */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    /** Shape the user like the old frontend's currentUser object. */
    private function present(User $user): array
    {
        $user->loadMissing('role.permissions', 'department', 'supplier');

        return [
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role?->name,
            'department' => $user->department?->name,
            'status' => $user->status,
            'supplierId' => $user->supplier?->code,
            'permissions' => $user->role?->permissions->pluck('key')->values() ?? [],
        ];
    }
}
