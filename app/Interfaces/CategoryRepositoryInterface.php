<?php

namespace App\Interfaces;

interface CategoryRepositoryInterface extends BaseRepositoryInterface {
    public function allSimple(): array;
}
