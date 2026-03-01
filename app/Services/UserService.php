<?php

namespace App\Services;

use App\Models\User;
use App\Interfaces\UserRepositoryInterface;
use App\Http\Resources\User\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Class UserService
 * @package App\Services
 */
class UserService
{
    public function __construct(private UserRepositoryInterface $userRepo) {}

    public function list(array $filters): UserResource
    {
        return $this->userRepo->all($filters);
    }

    public function create(array $data): UserResource
    {
        $user = $this->userRepo->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        $user->assignRole($data['role']);

        return new UserResource($user->load('roles'));
    }

    public function find(int $id): UserResource
    {
        return new UserResource($this->userRepo->findWithRoles($id));
    }

    public function update(int $id, array $data): UserResource
    {
        $user = $this->userRepo->update($id, $data);
        return new UserResource($user->load('roles'));
    }

    public function updateRole(int $id, string $role): UserResource
    {
        $user = $this->userRepo->findById($id);
        $user->syncRoles([$role]);
        return new UserResource($user->load('roles'));
    }

    public function deactivate(int $id, int $requesterId): void
    {
        if ($id === $requesterId) {
            throw ValidationException::withMessages([
                'user' => ['Kamu tidak bisa menonaktifkan akun sendiri.'],
            ]);
        }

        $user = $this->userRepo->findById($id);
        $user->update(['is_active' => false]);
        $user->tokens()->delete();
    }

    public function activate(int $id): void
    {
        $this->userRepo->update($id, ['is_active' => true]);
    }
}
