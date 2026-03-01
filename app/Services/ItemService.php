<?php

namespace App\Services;

use App\Interfaces\ItemRepositoryInterface;
use App\Interfaces\StockMovementRepositoryInterface;
use App\Http\Resources\Item\ItemResource;

/**
 * Class ItemService
 * @package App\Services
 */
class ItemService
{
    public function __construct(private ItemRepositoryInterface $itemRepo, private StockMovementRepositoryInterface $movementRepo,) {}

    public function list(array $filters)
    {
        return $this->itemRepo->all($filters);
    }

    public function find(int $id): ItemResource
    {
        return new ItemResource($this->itemRepo->findById($id));
    }

    public function create(array $data): ItemResource
    {
        return new ItemResource($this->itemRepo->create($data));
    }

    public function update(int $id, array $data): ItemResource
    {
        return new ItemResource($this->itemRepo->update($id, $data));
    }

    public function deactivate(int $id): void
    {
        $this->itemRepo->update($id, ['is_active' => false]);
    }

    public function getLowStock()
    {
        return $this->itemRepo->getLowStock();
    }

    public function movements(int $id, array $filters) {
        $this->itemRepo->findById($id);
        return $this->movementRepo->getByItem($id, $filters);
    }
}
