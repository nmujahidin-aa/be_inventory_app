<?php

namespace App\Services;

use App\Interfaces\ItemRepositoryInterface;
use App\Interfaces\StockMovementRepositoryInterface;

/**
 * Class InventoryService
 * @package App\Services
 */
class InventoryService
{
    public function __construct(private ItemRepositoryInterface $itemRepo, private StockMovementRepositoryInterface $movementRepo) {}

    public function list(array $filters)
    { 
        return $this->itemRepo->all($filters);
    }

    public function detail(int $itemId): array {
        $item = $this->itemRepo->findById($itemId);
        return [
            'item' => $item,
            'stock_quantity' => $item->stock_quantity,
            'reserved' => $item->reserved_quantity,
            'available' => $item->available_stock,
            'is_low_stock' => $item->isLowStock(),
        ];
    }
    public function allMovements(array $filters) 
    { 
        return $this->movementRepo->getAll($filters);
    }

    public function lowStock() 
    { 
        return $this->itemRepo->getLowStock();
    }

}
