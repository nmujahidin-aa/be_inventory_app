<?php
namespace App\Http\Controllers\Api\V1;

use App\Services\ReceivingService;
use App\Http\Requests\Receiving\StoreReceivingRequest;
use App\Http\Requests\Receiving\AddReceivingItemRequest;
use App\Http\Requests\Receiving\ReturnItemRequest;
use App\Http\Resources\Receiving\ReceivingResource;
use Illuminate\Http\{JsonResponse, Request};

class ReceivingController extends ApiController
{
    public function __construct(private ReceivingService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all()));
    }

    public function store(StoreReceivingRequest $request): JsonResponse
    {
        return $this->success(new ReceivingResource($this->service->create($request->validated(), $request->user())), 'Receiving berhasil dibuat.', 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->success(new ReceivingResource($this->service->find($id)));
    }

    public function addItem(AddReceivingItemRequest $request, int $id): JsonResponse
    {
        return $this->success($this->service->addItem($id, $request->validated(), $request->user()), 'Item berhasil ditambahkan.', 201);
    }

    public function complete(int $id): JsonResponse
    {
        return $this->success(new ReceivingResource($this->service->complete($id)), 'Receiving selesai.');
    }

    public function returnItem(ReturnItemRequest $request, int $id): JsonResponse 
    {
        return $this->success($this->service->returnItem($id, $request->validated()), 'Item direturn.');
    }
}