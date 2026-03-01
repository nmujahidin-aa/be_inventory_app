<?php
namespace App\Repositories;

use App\Models\ItemRequest;
use App\Interfaces\RequestRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RequestRepository implements RequestRepositoryInterface
{
    public function all(array $filters = [])
    { 
        return $this->allFiltered($filters); 
    }

    public function allFiltered(array $filters, ?int $userId = null) 
    {
        return ItemRequest::with(['requester','approver','items.item'])
            ->when($userId, fn($q) => $q->where('requested_by', $userId))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ItemRequest
    {
        return ItemRequest::with(['requester', 'approver', 'items.item'])->findOrFail($id);
    }

    public function findWithItems(int $id): ItemRequest
    {
        return ItemRequest::with(['requester', 'approver', 'items.item.unit'])->findOrFail($id);
    }

    public function create(array $data): ItemRequest
    {
        return ItemRequest::create($data);
    }

    public function update(int $id, array $data): ItemRequest
    {
        $request = $this->findById($id);
        $request->update($data);
        return $request->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->findById($id)->delete();
    }

    public function generateNumber(): string
    {
        $prefix = 'REQ-' . date('Ym') . '-';
        $last = ItemRequest::where('request_number', 'like', $prefix . '%')
            ->orderByDesc('id')->value('request_number');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
