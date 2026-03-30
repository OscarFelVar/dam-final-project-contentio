<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ClinicController extends Controller
{
    // GET /clinics
    public function index(Request $request): JsonResponse
    {
        $clinics = $request->user()->clinics()->orderBy('name')->get();
        return response()->json($clinics);
    }

    // POST /clinics
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $clinic = $request->user()->clinics()->create($data);

        return response()->json($clinic, 201);
    }

    // PUT /clinics/{clinic}
    public function update(Request $request, Clinic $clinic): JsonResponse
    {
        abort_if($clinic->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $clinic->update($data);

        return response()->json($clinic);
    }

    // DELETE /clinics/{clinic}
    public function destroy(Request $request, Clinic $clinic): JsonResponse
    {
        abort_if($clinic->user_id !== $request->user()->id, 403);

        if ($clinic->contentItems()->exists()) {
            return response()->json(
                ['message' => 'No se puede eliminar una clínica con contenidos asociados.'],
                422
            );
        }

        $clinic->delete();

        return response()->json(null, 204);
    }

    // GET /clinics/{clinic}/users
    public function indexUsers(Request $request, Clinic $clinic): JsonResponse
    {
        abort_if($clinic->user_id !== $request->user()->id, 403);

        $users = $clinic->clinicUsers()->get(['users.id', 'users.name', 'users.email']);

        return response()->json($users);
    }

    // POST /clinics/{clinic}/users
    public function storeUser(Request $request, Clinic $clinic): JsonResponse
    {
        abort_if($clinic->user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', Password::min(8)],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'clinic',
        ]);

        $clinic->clinicUsers()->attach($user->id);

        return response()->json($user->only(['id', 'name', 'email']), 201);
    }

    // DELETE /clinics/{clinic}/users/{user}
    public function destroyUser(Request $request, Clinic $clinic, User $user): JsonResponse
    {
        abort_if($clinic->user_id !== $request->user()->id, 403);

        $clinic->clinicUsers()->detach($user->id);

        return response()->json(null, 204);
    }
}
