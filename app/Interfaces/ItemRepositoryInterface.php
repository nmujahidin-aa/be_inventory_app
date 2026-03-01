<?php
namespace App\Interfaces;

interface ItemRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCode(string $code);
    public function getLowStock();
    public function incrementStock(int $itemId, int $qty): void;
    public function decrementStock(int $itemId, int $qty): void;
    public function reserveStock(int $itemId, int $qty): void;
    public function releaseReservation(int $itemId, int $qty): void;
}
