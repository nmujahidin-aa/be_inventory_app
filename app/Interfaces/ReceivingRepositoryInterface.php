<?php

namespace App\Interfaces;

interface ReceivingRepositoryInterface extends BaseRepositoryInterface {
    public function findWithItems(int $id);
    public function generateNumber(): string;
}