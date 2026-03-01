<?php
namespace App\Http\Controllers\Api\V1;

use App\Services\RequestService;
use App\Http\Requests\Request\StoreRequestRequest;
use App\Http\Requests\Request\ApproveRequestRequest;
use App\Http\Requests\Request\RejectRequestRequest;
use App\Http\Resources\Request\RequestResource;
use Illuminate\Http\{JsonResponse, Request};

class RequestController extends ApiController
{
    public function __construct(private RequestService $service) {}

    public function index(Request $request): JsonResponse
    { 
        return $this->paginated($this->service->list($request->all(), $request->user()));
    }

    public function store(StoreRequestRequest $request): JsonResponse 
    { 
        return $this->success(new RequestResource($this->service->create($request->validated(), $request->user())), 'Request berhasil dibuat.', 201);
    }

    public function show(int $id): JsonResponse
    { 
        return $this->success(new RequestResource($this->service->find($id)));
    }

    public function find(int $id) { 
        return $this->service->find($id); 
    }

    public function update(StoreRequestRequest $request, int $id): JsonResponse 
    { 
        return $this->success(new RequestResource($this->service->update($id, $request->validated(), $request->user())), 'Request berhasil diupdate.');
    }

    public function destroy(Request $request, int $id): JsonResponse 
    { 
        $this->service->delete($id, $request->user()); 
        return $this->success(null, 'Request berhasil dihapus.'); 
    }

    public function submit(Request $request, int $id): JsonResponse  
    { 
        return $this->success(new RequestResource($this->service->submit($id, $request->user())), 'Request berhasil disubmit.');
    }

    public function approve(ApproveRequestRequest $request, int $id): JsonResponse 
    { 
        return $this->success(new RequestResource($this->service->approve($id, $request->validated(), $request->user())), 'Request berhasil diapprove.'); 
    }

    public function reject(RejectRequestRequest $request, int $id): JsonResponse
    { 
        return $this->success(new RequestResource($this->service->reject($id, $request->validated(), $request->user())), 'Request berhasil direject.'); 
    }
}
