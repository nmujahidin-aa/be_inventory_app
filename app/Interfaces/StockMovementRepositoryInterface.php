<?php
namespace App\Interfaces;

interface StockMovementRepositoryInterface
{
    public function record(array $data);
    public function getByItem(int $itemId, array $filters = []);
    public function getAll(array $filters = []);
}
