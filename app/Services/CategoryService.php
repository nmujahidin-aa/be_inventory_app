<?php

namespace App\Services;

use App\Interfaces\CategoryRepositoryInterface;

/**
 * Class CategoryService
 * @package App\Services
 */
class CategoryService
{
    public function __construct(private CategoryRepositoryInterface $categoryRepo) {}

    public function list(array $filters) { return $this->categoryRepo->all($filters); }
    public function listSimple(): array { return $this->categoryRepo->allSimple(); }
    public function find(int $id) { return $this->categoryRepo->findById($id); }
    public function create(array $data) { return $this->categoryRepo->create($data); }
    public function update(int $id, array $data){ return $this->categoryRepo->update($id, $data); }

    public function delete(int $id): void
    {
        $category = $this->categoryRepo->findById($id);
        if ($category->items()->exists()) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                response()->json(['success' => false, 'message' => 'Kategori masih memiliki item terkait, tidak bisa dihapus.'], 422)
            );
        }
        $this->categoryRepo->delete($id);
    }
}
