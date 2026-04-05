<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\ContentItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    // GET /admin/stats
    public function stats(): JsonResponse
    {
        return response()->json([
            'agencies'      => User::where('role', 'agency')->count(),
            'clinics'       => Clinic::count(),
            'clinic_users'  => User::where('role', 'clinic')->count(),
            'content_items' => ContentItem::count(),
        ]);
    }

    // GET /admin/users
    public function indexUsers(): JsonResponse
    {
        $users = User::select('id', 'name', 'email', 'role', 'created_at')
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    // POST /admin/users  — crea usuario agency
    public function storeUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'agency',
        ]);

        return response()->json($user->only(['id', 'name', 'email', 'role', 'created_at']), 201);
    }

    // DELETE /admin/users/{user}
    public function destroyUser(Request $request, User $user): JsonResponse
    {
        // No permitir que el admin se elimine a sí mismo
        abort_if($user->id === $request->user()->id, 422, 'No puedes eliminar tu propio usuario.');

        $user->delete();

        return response()->json(null, 204);
    }
}
