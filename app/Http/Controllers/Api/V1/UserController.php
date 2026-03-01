<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UpdateRoleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\UserService;

class UserController extends ApiController
{
    public function __construct(private UserService $service) {}

    public function index(Request $request): JsonResponse
    { 
        return $this->paginated($this->service->list($request->all())); 
    }

    public function store(StoreUserRequest $request): JsonResponse
    { 
        return $this->success($this->service->create($request->validated()), 'User berhasil dibuat.', 201); 
    }

    public function show(int $id): JsonResponse
    { 
        return $this->success($this->service->find($id)); 
    }

    public function update(UpdateUserRequest $request, int $id): JsonResponse 
    { 
        return $this->success($this->service->update($id, $request->validated()), 'User berhasil diupdate.'); 
    }

    public function updateRole(UpdateRoleRequest $request, int $id): JsonResponse 
    { 
        return $this->success($this->service->updateRole($id, $request->role), 'Role berhasil diubah.'); 
    }

    public function deactivate(Request $request, int $id): JsonResponse 
    { 
        $this->service->deactivate($id, $request->user()->id); 
        return $this->success(null, 'User berhasil dinonaktifkan.'); 
    }
    public function activate(int $id): JsonResponse
    { 
        $this->service->activate($id); 
        return $this->success(null, 'User berhasil diaktifkan.'); 
    }
}