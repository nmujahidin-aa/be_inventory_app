<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\Category;
use App\Http\Requests\Category\StoreCategoryRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\CategoryService;

class CategoryController extends ApiController
{
    public function __construct(private CategoryService $service) {}

    public function index(Request $request): JsonResponse
    { 
        return $this->paginated($this->service->list($request->all()));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        return $this->success($this->service->create($request->validated()), 'Kategori berhasil dibuat.', 201);
    }

    public function show(int $id): JsonResponse
    { 
        return $this->success($this->service->find($id));
    }

    public function update(StoreCategoryRequest $request, int $id): JsonResponse 
    { 
        return $this->success($this->service->update($id, $request->validated()), 'Kategori berhasil diupdate.');
    }

    public function destroy(int $id): JsonResponse
    { 
        $this->service->delete($id); 
        return $this->success(null, 'Kategori berhasil dihapus.');
    }

}
