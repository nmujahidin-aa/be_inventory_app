<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\Vendor;
use App\Http\Requests\Vendor\StoreVendorRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\VendorService;

class VendorController extends ApiController
{
    public function __construct(private VendorService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all()));
    }

    public function store(StoreVendorRequest $request): JsonResponse
    {
        return $this->success($this->service->create($request->validated()), 'Vendor berhasil dibuat.', 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->success($this->service->find($id));
    }

    public function update(StoreVendorRequest $request, int $id): JsonResponse
    {
        return $this->success($this->service->update($id, $request->validated()), 'Vendor berhasil diupdate.');
    }

    public function deactivate(int $id): JsonResponse
    {
        $this->service->deactivate($id); return $this->success(null, 'Vendor berhasil dinonaktifkan.');
    }

    public function activate(int $id): JsonResponse
    {
        $this->service->activate($id); return $this->success(null, 'Vendor berhasil diaktifkan.');
    }
}
