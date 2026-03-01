<?php

namespace App\Services;

use App\Interfaces\VendorRepositoryInterface;

/**
 * Class VendorService
 * @package App\Services
 */
class VendorService
{
    public function __construct(private VendorRepositoryInterface $vendorRepo) {}

    public function list(array $filters) { return $this->vendorRepo->all($filters); }
    public function find(int $id) { return $this->vendorRepo->findById($id); }
    public function create(array $data) { return $this->vendorRepo->create($data); }
    public function update(int $id, array $data) { return $this->vendorRepo->update($id, $data); }
    public function deactivate(int $id): void { $this->vendorRepo->update($id, ['is_active' => false]); }
    public function activate(int $id): void { $this->vendorRepo->update($id, ['is_active' => true]); }
}
