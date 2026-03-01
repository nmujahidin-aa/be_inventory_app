<?php

namespace App\Services;

use App\Models\{Receiving, ReceivingItem};
use App\Enums\{ReceivingStatus, StockMovementType};
use App\Interfaces\{ReceivingRepositoryInterface, ItemRepositoryInterface, StockMovementRepositoryInterface};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Class ReceivingService
 * @package App\Services
 */
class ReceivingService
{
    public function __construct(
        private ReceivingRepositoryInterface     $receivingRepo,
        private ItemRepositoryInterface          $itemRepo,
        private StockMovementRepositoryInterface $movementRepo,
    ) {}

    public function list(array $filters)  { return $this->receivingRepo->all($filters); }
    public function find(int $id)         { return $this->receivingRepo->findWithItems($id); }

    public function create(array $data, $user): Receiving
    {
        return $this->receivingRepo->create([
            'receiving_number'  => $this->receivingRepo->generateNumber(),
            'purchase_order_id' => $data['purchase_order_id'],
            'received_by'       => $user->id,
            'status'            => ReceivingStatus::OPEN,
            'notes'             => $data['notes'] ?? null,
            'received_at'       => now(),
        ]);
    }

    public function addItem(int $receivingId, array $data, $user): ReceivingItem
    {
        return DB::transaction(function () use ($receivingId, $data, $user) {
            $receiving = $this->receivingRepo->findById($receivingId);

            if ($receiving->isCompleted()) {
                throw ValidationException::withMessages(['status' => 'Receiving sudah completed, tidak bisa menambah item.']);
            }

            $item   = $this->itemRepo->findById($data['item_id']);
            $before = $item->stock_quantity;
            $qty    = $data['quantity_received'];
            $quality = $data['quality_status'] ?? 'good';

            $receivingItem = ReceivingItem::create([
                'receiving_id'           => $receivingId,
                'purchase_order_item_id' => $data['purchase_order_item_id'],
                'item_id'                => $data['item_id'],
                'quantity_received'      => $qty,
                'quality_status'         => $quality,
                'note'                   => $data['note'] ?? null,
            ]);

            if ($quality !== 'returned') {
                $this->itemRepo->incrementStock($item->id, $qty);
                $this->movementRepo->record([
                    'item_id'        => $item->id,
                    'reference_type' => Receiving::class,
                    'reference_id'   => $receivingId,
                    'type'           => StockMovementType::IN,
                    'quantity'       => $qty,
                    'stock_before'   => $before,
                    'stock_after'    => $before + $qty,
                    'note'           => "Receiving #{$receiving->receiving_number}",
                    'created_by'     => $user->id,
                ]);
            }

            $this->receivingRepo->update($receivingId, ['status' => ReceivingStatus::PARTIAL]);

            return $receivingItem->load(['item', 'purchaseOrderItem']);
        });
    }

    public function complete(int $id): Receiving
    {
        $receiving = $this->receivingRepo->findById($id);
        if ($receiving->isCompleted()) {
            throw ValidationException::withMessages(['status' => 'Receiving sudah completed.']);
        }
        return $this->receivingRepo->update($id, ['status' => ReceivingStatus::COMPLETED]);
    }

    public function returnItem(int $receivingId, array $data): ReceivingItem
    {
        $item = ReceivingItem::where('receiving_id', $receivingId)
            ->findOrFail($data['receiving_item_id']);

        $item->update(['quality_status' => 'returned', 'note' => $data['note'] ?? $item->note]);
        return $item->fresh();
    }
}