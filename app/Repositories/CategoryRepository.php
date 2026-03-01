<?php

namespace App\Repositories;

use App\Models\Category;
use App\Interfaces\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface {
    public function all(array $filters = []) {
        return Category::when(isset($filters['search']), 
            fn($q) => $q->where('name','like',"%{$filters['search']}%"))->paginate($filters['per_page'] ?? 50); 
    }

    public function allSimple(): array
    { 
        return Category::orderBy('name')->get(['id','name'])->toArray();
    }

    public function findById(int $id): Category 
    { 
        return Category::findOrFail($id);
    }

    public function create(array $data): Category 
    { 
        return Category::create($data);
    }

    public function update(int $id, array $data): Category 
    { 
        $c = Category::findOrFail($id); $c->update($data); 
        return $c->fresh();
    }

    public function delete(int $id): bool 
    {
        return Category::findOrFail($id)->delete();
    }
}