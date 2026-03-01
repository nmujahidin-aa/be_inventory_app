<?php
namespace App\Interfaces;

interface PurchaseOrderRepositoryInterface extends BaseRepositoryInterface
{
    public function findWithItems(int $id);
    public function generateNumber(): string;
}
