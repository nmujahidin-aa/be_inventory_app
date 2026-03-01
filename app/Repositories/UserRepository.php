<?php

namespace App\Repositories;

use App\Models\User;
use App\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface {
    public function all(array $filters = []) {
        return User::with('roles')
            ->when(isset($filters['search']), fn($q) => $q->where('name','like',"%{$filters['search']}%")->orWhere('email','like',"%{$filters['search']}%"))
            ->when(isset($filters['role']), fn($q) => $q->role($filters['role']))
            ->when(array_key_exists('is_active', $filters), fn($q) => $q->where('is_active', $filters['is_active']))
            ->latest()->paginate($filters['per_page'] ?? 15);
    }
    
    public function findById(int $id): User { return User::with('roles')->findOrFail($id); }
    public function findByEmail(string $email): ?User { return User::where('email', $email)->first(); }
    public function findWithRoles(int $id): User { return User::with('roles.permissions')->findOrFail($id); }
    public function create(array $data): User { return User::create($data); }
    public function update(int $id, array $data): User { $u = User::findOrFail($id); $u->update($data); return $u->fresh('roles'); }
    public function delete(int $id): bool { return User::findOrFail($id)->delete(); }
}