<?php
namespace App\Http\Controllers\Api\V1;

use App\Services\InventoryService;
use Illuminate\Http\{JsonResponse, Request};

class InventoryController extends ApiController
{
    public function __construct(private InventoryService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all()));
    }

    public function show(int $itemId): JsonResponse
    { 
        return $this->success($this->service->detail($itemId)); 
    }

    public function allMovements(Request $request): JsonResponse 
    { 
        return $this->paginated($this->service->allMovements($request->all()));
    }

    public function lowStock(): JsonResponse
    { 
        return $this->success($this->service->lowStock());
    }
}