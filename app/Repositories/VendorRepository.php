<?php

namespace App\Repositories;

use App\Models\Vendor;
use App\Interfaces\VendorRepositoryInterface;

class VendorRepository implements VendorRepositoryInterface {
    public function all(array $filters = []) {
        return Vendor::when(isset($filters['search']), fn($q) => $q->where('name','like',"%{$filters['search']}%"))
            ->when(array_key_exists('is_active', $filters), fn($q) => $q->where('is_active', $filters['is_active']))
            ->latest()->paginate($filters['per_page'] ?? 15);
    }
    public function findActive(array $filters = []) 
    {
        return $this->all(array_merge($filters, ['is_active' => true]));
    }

    public function findById(int $id): Vendor
    {
        return Vendor::findOrFail($id);
    }

    public function create(array $data): Vendor
    {
        return Vendor::create($data);
    }

    public function update(int $id, array $data): Vendor
    {
        $v = Vendor::findOrFail($id); $v->update($data); return $v->fresh();
    }

    public function delete(int $id): bool 
    { 
        return Vendor::findOrFail($id)->delete();
    }
}