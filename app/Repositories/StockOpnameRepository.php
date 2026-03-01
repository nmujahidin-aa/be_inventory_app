<?php

namespace App\Repositories;

use App\Models\StockOpname;
use App\Interfaces\StockOpnameRepositoryInterface;


class StockOpnameRepository implements StockOpnameRepositoryInterface {
    public function all(array $filters = []) {
        return StockOpname::with(['creator', 'approver'])
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->latest()->paginate($filters['per_page'] ?? 15);
    }
    public function findById(int $id): StockOpname { return StockOpname::with(['creator','approver','items.item'])->findOrFail($id); }
    public function findWithItems(int $id): StockOpname { return StockOpname::with(['creator','approver','items.item'])->findOrFail($id); }
    public function create(array $data): StockOpname { return StockOpname::create($data); }
    public function update(int $id, array $data): StockOpname { $o = StockOpname::findOrFail($id); $o->update($data); return $o->fresh(); }
    public function delete(int $id): bool { return StockOpname::findOrFail($id)->delete(); }
    public function generateNumber(): string {
        $prefix = 'OPN-' . date('Ym') . '-';
        $last = StockOpname::where('opname_number', 'like', $prefix . '%')->orderByDesc('id')->value('opname_number');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}