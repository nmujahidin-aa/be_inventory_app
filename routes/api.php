<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\RequestController;
use App\Http\Controllers\API\V1\PurchaseOrderController;
use App\Http\Controllers\API\V1\ReceivingController;
use App\Http\Controllers\API\V1\InventoryController;
use App\Http\Controllers\API\V1\StockOpnameController;
use App\Http\Controllers\API\V1\UserController;
use App\Http\Controllers\API\V1\CategoryController;
use App\Http\Controllers\API\V1\UnitController;
use App\Http\Controllers\API\V1\VendorController;
use App\Http\Controllers\API\V1\ItemController;


Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('login',[AuthController::class, 'login']);
        Route::post('forgot-password',[AuthController::class, 'forgotPassword']);
        Route::post('reset-password',[AuthController::class, 'resetPassword']);
    });

    Route::middleware(['jwt.auth'])->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('logout',[AuthController::class, 'logout']);
            Route::get('me',[AuthController::class, 'me']);
        });

        Route::middleware('role:admin_gudang')->prefix('users')->group(function () {
            Route::get('/',[UserController::class, 'index']);
            Route::post('/',[UserController::class, 'store']);
            Route::get('/{id}',[UserController::class, 'show']);
            Route::put('/{id}',[UserController::class, 'update']);
            Route::patch('/{id}/role',[UserController::class, 'updateRole']);
            Route::patch('/{id}/deactivate',[UserController::class, 'deactivate']);
            Route::patch('/{id}/activate',[UserController::class, 'activate']);
        });

        Route::middleware('role:admin_gudang')->group(function () {
            Route::apiResource('categories',CategoryController::class);
            Route::apiResource('units',UnitController::class);

            Route::prefix('vendors')->group(function () {
                Route::get('/',[VendorController::class, 'index']);
                Route::post('/',[VendorController::class, 'store']);
                Route::get('/{id}',[VendorController::class, 'show']);
                Route::put('/{id}',[VendorController::class, 'update']);
                Route::patch('/{id}/deactivate',[VendorController::class, 'deactivate']);
            });

            Route::prefix('items')->group(function () {
                Route::get('/',[ItemController::class,'index']);
                Route::post('/',[ItemController::class, 'store']);
                Route::get('/{id}',[ItemController::class, 'show']);
                Route::put('/{id}',[ItemController::class, 'update']);
                Route::patch('/{id}/deactivate',[ItemController::class, 'deactivate']);
                Route::get('/{id}/movements',[ItemController::class, 'movements']);
            });
        });

        Route::prefix('requests')->group(function () {
            Route::get('/',[RequestController::class, 'index']);
            Route::post('/',[RequestController::class, 'store'])->middleware('role:technician');
            Route::get('/{id}',[RequestController::class, 'show']);
            Route::put('/{id}',[RequestController::class, 'update'])->middleware('role:technician');
            Route::delete('/{id}',[RequestController::class, 'destroy'])->middleware('role:technician');
            Route::patch('/{id}/submit',[RequestController::class, 'submit'])->middleware('role:technician');
            Route::patch('/{id}/approve',[RequestController::class, 'approve'])->middleware('role:spv');
            Route::patch('/{id}/reject',[RequestController::class, 'reject'])->middleware('role:spv');
        });

        Route::prefix('purchase-orders')->group(function () {
            Route::get('/',[PurchaseOrderController::class, 'index'])->middleware('role:admin_gudang|spv');
            Route::post('/',[PurchaseOrderController::class, 'store'])->middleware('role:admin_gudang');
            Route::get('/{id}',[PurchaseOrderController::class, 'show'])->middleware('role:admin_gudang|spv');
            Route::put('/{id}',[PurchaseOrderController::class, 'update'])->middleware('role:admin_gudang');
            Route::delete('/{id}',[PurchaseOrderController::class, 'destroy'])->middleware('role:admin_gudang');
            Route::patch('/{id}/submit',[PurchaseOrderController::class, 'submit'])->middleware('role:admin_gudang');
            Route::patch('/{id}/approve',[PurchaseOrderController::class, 'approve'])->middleware('role:spv');
            Route::patch('/{id}/reject',[PurchaseOrderController::class, 'reject'])->middleware('role:spv');
            Route::patch('/{id}/send',[PurchaseOrderController::class, 'sendToVendor'])->middleware('role:admin_gudang');
            Route::patch('/{id}/confirm',[PurchaseOrderController::class, 'confirm'])->middleware('role:admin_gudang');
            Route::patch('/{id}/cancel',[PurchaseOrderController::class, 'cancel'])->middleware('role:admin_gudang');
        });

        Route::middleware('role:admin_gudang')->prefix('receivings')->group(function () {
            Route::get('/',[ReceivingController::class, 'index']);
            Route::post('/',[ReceivingController::class, 'store']);
            Route::get('/{id}',[ReceivingController::class, 'show']);
            Route::post('/{id}/items',[ReceivingController::class, 'addItem']);
            Route::patch('/{id}/complete',[ReceivingController::class, 'complete']);
            Route::post('/{id}/return',[ReceivingController::class, 'returnItem']);
        });

        Route::middleware('role:admin_gudang')->prefix('inventory')->group(function () {
            Route::get('/',[InventoryController::class, 'index']);
            Route::get('/movements',[InventoryController::class, 'allMovements']);
            Route::get('/low-stock',[InventoryController::class, 'lowStock']);
            Route::get('/{itemId}',[InventoryController::class, 'show']);
        });

        Route::prefix('stock-opnames')->group(function () {
            Route::get('/',[StockOpnameController::class, 'index'])->middleware('role:admin_gudang|spv');
            Route::post('/',[StockOpnameController::class, 'store'])->middleware('role:admin_gudang');
            Route::get('/{id}',[StockOpnameController::class, 'show'])->middleware('role:admin_gudang|spv');
            Route::put('/{id}',[StockOpnameController::class, 'update'])->middleware('role:admin_gudang');
            Route::post('/{id}/items',[StockOpnameController::class, 'addItem'])->middleware('role:admin_gudang');
            Route::patch('/{id}/submit',[StockOpnameController::class, 'submit'])->middleware('role:admin_gudang');
            Route::patch('/{id}/approve',[StockOpnameController::class, 'approve'])->middleware('role:spv');
            Route::patch('/{id}/reject',[StockOpnameController::class, 'reject'])->middleware('role:spv');
        });
    });
});