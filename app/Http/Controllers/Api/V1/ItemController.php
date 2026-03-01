<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Item\StoreItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\ItemService;

class ItemController extends ApiController
{
    public function __construct(private ItemService $service) {}

    public function index(Request $request): JsonResponse
    { 
        return $this->paginated($this->service->list($request->all()));
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        return $this->success($this->service->create($request->validated()), 'Item berhasil dibuat.', 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->success($this->service->find($id));
    }

    public function update(UpdateItemRequest $request, int $id): JsonResponse
    {
        return $this->success($this->service->update($id, $request->validated()), 'Item berhasil diupdate.');
    }

    public function deactivate(int $id): JsonResponse
    {
        $this->service->deactivate($id); return $this->success(null, 'Item berhasil dinonaktifkan.');
    }

    public function movements(Request $request, int $id): JsonResponse
    {
        return $this->paginated($this->service->movements($id, $request->all()));
    }
}
