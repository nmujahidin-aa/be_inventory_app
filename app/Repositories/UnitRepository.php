<?php

namespace App\Repositories;

use App\Models\Unit;
use App\Interfaces\UnitRepositoryInterface;

class UnitRepository implements UnitRepositoryInterface {

    public function all(array $filters = [])
    {
        return Unit::paginate($filters['per_page'] ?? 50);
    }

    public function allSimple(): array
    {
        return Unit::orderBy('name')->get(['id','name'])->toArray();
    }

    public function findById(int $id): Unit
    {
        return Unit::findOrFail($id);
    }

    public function create(array $data): Unit
    {
        return Unit::create($data);
    }

    public function update(int $id, array $data): Unit
    {
        $u = Unit::findOrFail($id); $u->update($data); return $u->fresh();
    }

    public function delete(int $id): bool
    {
        return Unit::findOrFail($id)->delete();
    }
}