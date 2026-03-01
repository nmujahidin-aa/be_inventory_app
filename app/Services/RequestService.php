<?php
namespace App\Services;

use App\Models\{ItemRequest, RequestItem};
use App\Enums\{RequestStatus, StockMovementType};
use App\Interfaces\{RequestRepositoryInterface, ItemRepositoryInterface, StockMovementRepositoryInterface};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestService
{
    public function __construct(
        private RequestRepositoryInterface $requestRepo,
        private ItemRepositoryInterface $itemRepo,
        private StockMovementRepositoryInterface $movementRepo,
    ) {}

    public function list(array $filters, $user)
    {
        $userId = $user->hasRole('technician') ? $user->id : null;
        return $this->requestRepo->allFiltered($filters, $userId);
    }

    public function find(int $id): ItemRequest
    {
        return $this->requestRepo->findWithItems($id);
    }

    public function create(array $data, $user): ItemRequest
    {
        return DB::transaction(function () use ($data, $user) {
            $request = $this->requestRepo->create([
                'request_number' => $this->requestRepo->generateNumber(),
                'requested_by'   => $user->id,
                'status'         => RequestStatus::DRAFT,
                'notes'          => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $itemData) {
                RequestItem::create([
                    'request_id'         => $request->id,
                    'item_id'            => $itemData['item_id'],
                    'quantity_requested' => $itemData['quantity'],
                    'note'               => $itemData['note'] ?? null,
                ]);
            }

            return $this->requestRepo->findWithItems($request->id);
        });
    }

    public function update(int $id, array $data, $user): ItemRequest
    {
        $request = $this->requestRepo->findById($id);

        if (!$request->isDraft()) {
            throw ValidationException::withMessages(['status' => 'Hanya request draft yang bisa diedit.']);
        }
        if ($request->requested_by !== $user->id) {
            abort(403, 'Kamu tidak memiliki akses ke request ini.');
        }

        return DB::transaction(function () use ($id, $data, $request) {
            $this->requestRepo->update($id, ['notes' => $data['notes'] ?? $request->notes]);
            $request->items()->delete();

            foreach ($data['items'] as $item) {
                RequestItem::create([
                    'request_id'         => $id,
                    'item_id'            => $item['item_id'],
                    'quantity_requested' => $item['quantity'],
                    'note'               => $item['note'] ?? null,
                ]);
            }

            return $this->requestRepo->findWithItems($id);
        });
    }

    public function delete(int $id, $user): void
    {
        $request = $this->requestRepo->findById($id);
        if (!$request->isDraft())           throw ValidationException::withMessages(['status' => 'Hanya request draft yang bisa dihapus.']);
        if ($request->requested_by !== $user->id) abort(403);
        $this->requestRepo->delete($id);
    }

    public function submit(int $id, $user): ItemRequest
    {
        $request = $this->requestRepo->findById($id);
        if (!$request->isDraft())           throw ValidationException::withMessages(['status' => 'Hanya request draft yang bisa disubmit.']);
        if ($request->requested_by !== $user->id) abort(403);

        return $this->requestRepo->update($id, [
            'status'       => RequestStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);
    }

    public function approve(int $id, array $data, $user): ItemRequest
    {
        return DB::transaction(function () use ($id, $data, $user) {
            $request = $this->requestRepo->findWithItems($id);

            if (!$request->isSubmitted()) {
                throw ValidationException::withMessages(['status' => 'Hanya request submitted yang bisa diapprove.']);
            }

            foreach ($data['items'] as $approval) {
                $reqItem = $request->items()->where('item_id', $approval['item_id'])->firstOrFail();
                $qtyApproved = $approval['quantity_approved'];

                if ($qtyApproved === 0) {
                    $reqItem->update(['quantity_approved' => 0]);
                    continue;
                }

                $item = $this->itemRepo->findById($reqItem->item_id);

                if ($item->available_stock < $qtyApproved) {
                    throw ValidationException::withMessages([
                        'stock' => "Stok {$item->name} tidak mencukupi. Tersedia: {$item->available_stock}",
                    ]);
                }

                $before = $item->stock_quantity;
                $reqItem->update(['quantity_approved' => $qtyApproved]);

                $this->itemRepo->decrementStock($item->id, $qtyApproved);

                $this->movementRepo->record([
                    'item_id'        => $item->id,
                    'reference_type' => ItemRequest::class,
                    'reference_id'   => $request->id,
                    'type'           => StockMovementType::OUT,
                    'quantity'       => -$qtyApproved,
                    'stock_before'   => $before,
                    'stock_after'    => $before - $qtyApproved,
                    'note'           => "Approved request #{$request->request_number}",
                    'created_by'     => $user->id,
                ]);
            }

            return $this->requestRepo->update($id, [
                'status'      => RequestStatus::APPROVED,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        });
    }

    public function reject(int $id, array $data, $user): ItemRequest
    {
        $request = $this->requestRepo->findById($id);
        if (!$request->isSubmitted()) {
            throw ValidationException::withMessages(['status' => 'Hanya request submitted yang bisa direject.']);
        }

        return $this->requestRepo->update($id, [
            'status'           => RequestStatus::REJECTED,
            'approved_by'      => $user->id,
            'approved_at'      => now(),
            'rejection_reason' => $data['rejection_reason'],
        ]);
    }
}