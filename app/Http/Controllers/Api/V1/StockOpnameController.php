<?php
namespace App\Http\Controllers\Api\V1;

use App\Services\StockOpnameService;
use App\Http\Requests\StockOpname\StoreStockOpnameRequest;
use App\Http\Requests\StockOpname\AddOpnameItemRequest;
use App\Http\Requests\StockOpname\RejectStockOpnameRequest;
use App\Http\Resources\StockOpname\StockOpnameResource;
use Illuminate\Http\{JsonResponse, Request};

class StockOpnameController extends ApiController
{
    public function __construct(private StockOpnameService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all()));
    }

    public function store(StoreStockOpnameRequest $request): JsonResponse
    {
        return $this->success(new StockOpnameResource($this->service->create($request->validated(), $request->user())), 'Opname berhasil dibuat.', 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->success(new StockOpnameResource($this->service->find($id)));
    }

    public function update(StoreStockOpnameRequest $request, int $id): JsonResponse
    {
        return $this->success(new StockOpnameResource($this->service->update($id, $request->validated())), 'Opname diupdate.');
    }

    public function addItem(AddOpnameItemRequest $request, int $id): JsonResponse
    {
        return $this->success($this->service->addItem($id, $request->validated()), 'Item ditambahkan.', 201);
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        return $this->success(new StockOpnameResource($this->service->submit($id, $request->user())), 'Opname disubmit.');
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        return $this->success(new StockOpnameResource($this->service->approve($id, $request->user())), 'Opname diapprove & stok disesuaikan.');
    }

    public function reject(RejectStockOpnameRequest $request, int $id): JsonResponse
    { 
        return $this->success(new StockOpnameResource($this->service->reject($id, $request->validated(), $request->user())), 'Opname direject.');
    }
}
