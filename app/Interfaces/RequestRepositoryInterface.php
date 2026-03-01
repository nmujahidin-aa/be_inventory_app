<?php
namespace App\Interfaces;

interface RequestRepositoryInterface extends BaseRepositoryInterface
{
    public function findWithItems(int $id);
    public function allFiltered(array $filters, ?int $userId = null);
    public function generateNumber(): string;
}
