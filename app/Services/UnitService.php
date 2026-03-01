<?php

namespace App\Services;

use App\Interfaces\UnitRepositoryInterface;

/**
 * Class UnitService
 * @package App\Services
 */
class UnitService
{
    public function __construct(private UnitRepositoryInterface $unitRepo) {}

    public function list(array $filters)
    {
        return $this->unitRepo->all($filters);
    }

    public function listSimple(): array
    {
        return $this->unitRepo->allSimple();
    }

    public function find(int $id)
    {
        return $this->unitRepo->findById($id);
    }

    public function create(array $data)
    {
        return $this->unitRepo->create($data);
    }

    public function update(int $id, array $data)
    {
        return $this->unitRepo->update($id, $data);
    }

    public function delete(int $id): void
    {
        $unit = $this->unitRepo->findById($id);
        if ($unit->items()->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'unit' => ['Satuan masih digunakan oleh item, tidak bisa dihapus.'],
            ]);
        }
        $this->unitRepo->delete($id);
    }
}
