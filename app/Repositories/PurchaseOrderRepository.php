<?php
namespace App\Repositories;

use App\Models\PurchaseOrder;
use App\Interfaces\PurchaseOrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PurchaseOrderRepository implements PurchaseOrderRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        return PurchaseOrder::with(['vendor', 'creator', 'approver'])
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['vendor_id']), fn($q) => $q->where('vendor_id', $filters['vendor_id']))
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): PurchaseOrder
    {
        return PurchaseOrder::with(['vendor', 'creator', 'approver', 'items.item'])->findOrFail($id);
    }

    public function findWithItems(int $id): PurchaseOrder
    {
        return PurchaseOrder::with(['vendor', 'creator', 'approver', 'items.item.unit'])->findOrFail($id);
    }

    public function create(array $data): PurchaseOrder
    {
        return PurchaseOrder::create($data);
    }

    public function update(int $id, array $data): PurchaseOrder
    {
        $po = $this->findById($id);
        $po->update($data);
        return $po->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->findById($id)->delete();
    }

    public function generateNumber(): string
    {
        $prefix = 'PO-' . date('Ym') . '-';
        $last = PurchaseOrder::where('po_number', 'like', $prefix . '%')
            ->orderByDesc('id')->value('po_number');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
