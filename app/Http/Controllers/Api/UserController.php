<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::latest()->paginate(10)->through(fn($user) => new UserResource($user));

        return ApiResponse::paginated($users, 'Daftar user berhasil diambil');
    }


    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return ApiResponse::success(new UserResource($user), 'User berhasil dibuat', 201);
    }

    public function show(User $user): JsonResponse
    {
        return ApiResponse::success(new UserResource($user), 'Detail user berhasil diambil');
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return ApiResponse::success(new UserResource($user), 'User berhasil diupdate');
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return ApiResponse::success(null, 'User berhasil dihapus');
    }
}
