<?php
namespace App\Repositories;

use App\Models\Item;
use App\Interfaces\ItemRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ItemRepository implements ItemRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        return Item::with(['category', 'unit'])
            ->when(isset($filters['search']), fn($q) =>
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%"))
            ->when(isset($filters['category_id']), fn($q) =>
                $q->where('category_id', $filters['category_id']))
            ->when(isset($filters['is_active']), fn($q) =>
                $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): Item
    {
        return Item::with(['category', 'unit'])->findOrFail($id);
    }

    public function findByCode(string $code): ?Item
    {
        return Item::where('code', $code)->first();
    }

    public function create(array $data): Item
    {
        return Item::create($data);
    }

    public function update(int $id, array $data): Item
    {
        $item = $this->findById($id);
        $item->update($data);
        return $item->fresh(['category', 'unit']);
    }

    public function delete(int $id): bool
    {
        return $this->findById($id)->delete();
    }

    public function getLowStock()
    {
        return Item::with(['category', 'unit'])
            ->whereColumn('stock_quantity', '<=', 'min_stock')
            ->where('is_active', true)
            ->get();
    }

    public function incrementStock(int $id, int $qty): void 
    {
        Item::lockForUpdate()->findOrFail($id)->increment('stock_quantity', $qty);
    }

    public function decrementStock(int $id, int $qty): void 
    {
        Item::lockForUpdate()->findOrFail($id)->decrement('stock_quantity', $qty);
    }

    public function reserveStock(int $id, int $qty): void
    {
        Item::lockForUpdate()->findOrFail($id)->increment('reserved_quantity', $qty);
    }
    public function releaseReservation(int $id, int $qty): void 
    { 
        Item::lockForUpdate()->findOrFail($id)->decrement('reserved_quantity', $qty); 
    }
}
