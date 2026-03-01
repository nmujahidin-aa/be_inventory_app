<?php
namespace App\Http\Controllers\Api\V1;

use App\Services\PurchaseOrderService;
use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\RejectPurchaseOrderRequest;
use App\Http\Resources\PurchaseOrder\PurchaseOrderResource;
use Illuminate\Http\{JsonResponse, Request};

class PurchaseOrderController extends ApiController
{
    public function __construct(private PurchaseOrderService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->paginated($this->service->list($request->all()));
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        return $this->success(new PurchaseOrderResource($this->service->create($request->validated(), $request->user())), 'PO berhasil dibuat.', 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->success(new PurchaseOrderResource($this->service->find($id)));
    }

    public function update(StorePurchaseOrderRequest $request, int $id): JsonResponse
    {
        return $this->success(new PurchaseOrderResource($this->service->update($id, $request->only('notes'))), 'PO berhasil diupdate.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->delete($id); return $this->success(null, 'PO berhasil dihapus.');
    }

    public function submit(Request $request, int $id): JsonResponse
    {
        return $this->success(new PurchaseOrderResource($this->service->submit($id, $request->user())), 'PO disubmit.');
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        return $this->success(new PurchaseOrderResource($this->service->approve($id, $request->user())), 'PO diapprove.');
    }

    public function reject(RejectPurchaseOrderRequest $request, int $id): JsonResponse
    {
        return $this->success(new PurchaseOrderResource($this->service->reject($id, $request->validated(), $request->user())), 'PO direject.');
    }

    public function sendToVendor(Request $request, int $id): JsonResponse
    {
        return $this->success(new PurchaseOrderResource($this->service->sendToVendor($id, $request->user())), 'PO dikirim ke vendor.');
    }

    public function confirm(Request $request, int $id): JsonResponse
    {
        return $this->success(new PurchaseOrderResource($this->service->confirm($id, $request->user())), 'PO dikonfirmasi.');
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        return $this->success(new PurchaseOrderResource($this->service->cancel($id, $request->user())), 'PO dibatalkan.');
    }
}
