<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\UnitService;
use App\Http\Requests\Unit\StoreUnitRequest;

class UnitController extends ApiController
{
    public function __construct(private UnitService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all()));
    }

    public function store(StoreUnitRequest $request): JsonResponse
    {
        return $this->success($this->service->create($request->validated()), 'Satuan berhasil dibuat.', 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->success($this->service->find($id));
    }

    public function update(StoreUnitRequest $request, int $id): JsonResponse
    {
        return $this->success($this->service->update($id, $request->validated()), 'Satuan berhasil diupdate.');
    }

    public function destroy(int $id): JsonResponse 
    {
        $this->service->delete($id); return $this->success(null, 'Satuan berhasil dihapus.');
    }

}
