<?php

namespace App\Interfaces;

interface UnitRepositoryInterface extends BaseRepositoryInterface
{
    public function allSimple(): array;
}
