<?php
namespace App\Interfaces;
interface StockOpnameRepositoryInterface extends BaseRepositoryInterface {
    public function findWithItems(int $id);
    public function generateNumber(): string;
}