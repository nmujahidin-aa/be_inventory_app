<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\ItemRepositoryInterface;
use App\Interfaces\RequestRepositoryInterface;
use App\Interfaces\PurchaseOrderRepositoryInterface;
use App\Interfaces\StockMovementRepositoryInterface;
use App\Interfaces\StockOpnameRepositoryInterface;
use App\Interfaces\ReceivingRepositoryInterface;
use App\Repositories\ItemRepository;
use App\Repositories\RequestRepository;
use App\Repositories\PurchaseOrderRepository;
use App\Repositories\StockMovementRepository;
use App\Repositories\StockOpnameRepository;
use App\Repositories\ReceivingRepository;
use App\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Interfaces\CategoryRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Interfaces\VendorRepositoryInterface;
use App\Repositories\VendorRepository;
use App\Interfaces\UnitRepositoryInterface;
use App\Repositories\UnitRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(RequestRepositoryInterface::class, RequestRepository::class);
        $this->app->bind(PurchaseOrderRepositoryInterface::class, PurchaseOrderRepository::class);
        $this->app->bind(StockMovementRepositoryInterface::class, StockMovementRepository::class);
        $this->app->bind(StockOpnameRepositoryInterface::class, StockOpnameRepository::class);
        $this->app->bind(ReceivingRepositoryInterface::class, ReceivingRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(VendorRepositoryInterface::class, VendorRepository::class);
        $this->app->bind(UnitRepositoryInterface::class, UnitRepository::class);


    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
