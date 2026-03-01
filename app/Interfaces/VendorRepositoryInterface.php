<?php

namespace App\Interfaces;

interface VendorRepositoryInterface extends BaseRepositoryInterface
{
    public function findActive(array $filters = []);
}
