<?php
namespace App\Repositories;

use App\Models\StockMovement;
use App\Interfaces\StockMovementRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StockMovementRepository implements StockMovementRepositoryInterface
{
    public function record(array $data): StockMovement
    {
        return StockMovement::create($data);
    }

    public function getByItem(int $itemId, array $filters = []): LengthAwarePaginator
    {
        return StockMovement::with(['creator'])
            ->where('item_id', $itemId)
            ->when(isset($filters['type']), fn($q) => $q->where('type', $filters['type']))
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }

    public function getAll(array $filters = []): LengthAwarePaginator
    {
        return StockMovement::with(['item', 'creator'])
            ->when(isset($filters['type']), fn($q) => $q->where('type', $filters['type']))
            ->when(isset($filters['item_id']), fn($q) => $q->where('item_id', $filters['item_id']))
            ->latest()
            ->paginate($filters['per_page'] ?? 20);
    }
}
