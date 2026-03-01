<?php
namespace App\Repositories;

use App\Models\Receiving;
use App\Interfaces\ReceivingRepositoryInterface;


class ReceivingRepository implements ReceivingRepositoryInterface {
    public function all(array $filters = []) {
        return Receiving::with(['purchaseOrder.vendor', 'receiver'])
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->latest()->paginate($filters['per_page'] ?? 15);
    }
    public function findById(int $id): Receiving { return Receiving::with(['purchaseOrder','receiver','items.item'])->findOrFail($id); }
    public function findWithItems(int $id): Receiving { return Receiving::with(['purchaseOrder.vendor','receiver','items.item','items.purchaseOrderItem'])->findOrFail($id); }
    public function create(array $data): Receiving { return Receiving::create($data); }
    public function update(int $id, array $data): Receiving { $r = Receiving::findOrFail($id); $r->update($data); return $r->fresh(); }
    public function delete(int $id): bool { return Receiving::findOrFail($id)->delete(); }
    public function generateNumber(): string {
        $prefix = 'GRN-' . date('Ym') . '-';
        $last = Receiving::where('receiving_number', 'like', $prefix . '%')->orderByDesc('id')->value('receiving_number');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}