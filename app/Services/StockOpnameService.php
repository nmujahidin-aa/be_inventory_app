<?php
namespace App\Services;

use App\Models\{StockOpname, StockOpnameItem};
use App\Enums\{StockOpnameStatus, StockMovementType};
use App\Interfaces\{StockOpnameRepositoryInterface, ItemRepositoryInterface, StockMovementRepositoryInterface};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockOpnameService
{
    public function __construct(
        private StockOpnameRepositoryInterface   $opnameRepo,
        private ItemRepositoryInterface          $itemRepo,
        private StockMovementRepositoryInterface $movementRepo,
    ) {}

    public function list(array $filters) { return $this->opnameRepo->all($filters); }
    public function find(int $id)        { return $this->opnameRepo->findWithItems($id); }

    public function create(array $data, $user): StockOpname
    {
        return $this->opnameRepo->create([
            'opname_number' => $this->opnameRepo->generateNumber(),
            'created_by'    => $user->id,
            'status'        => StockOpnameStatus::DRAFT,
            'opname_date'   => $data['opname_date'],
            'notes'         => $data['notes'] ?? null,
        ]);
    }

    public function update(int $id, array $data): StockOpname
    {
        $opname = $this->opnameRepo->findById($id);
        if (!$opname->isDraft()) throw ValidationException::withMessages(['status' => 'Hanya opname draft yang bisa diedit.']);
        return $this->opnameRepo->update($id, $data);
    }

    public function addItem(int $opnameId, array $data): StockOpnameItem
    {
        $opname = $this->opnameRepo->findById($opnameId);
        if (!$opname->isDraft()) throw ValidationException::withMessages(['status' => 'Item hanya bisa ditambahkan pada opname draft.']);

        $item = $this->itemRepo->findById($data['item_id']);

        return StockOpnameItem::updateOrCreate(
            ['stock_opname_id' => $opnameId, 'item_id' => $data['item_id']],
            ['system_quantity' => $item->stock_quantity, 'physical_quantity' => $data['physical_quantity'], 'note' => $data['note'] ?? null]
        );
    }

    public function submit(int $id, $user): StockOpname
    {
        $opname = $this->opnameRepo->findById($id);
        if (!$opname->isDraft()) throw ValidationException::withMessages(['status' => 'Hanya opname draft yang bisa disubmit.']);
        if ($opname->items()->count() === 0) throw ValidationException::withMessages(['items' => 'Opname harus memiliki minimal 1 item.']);
        if ($opname->created_by !== $user->id) abort(403);

        return $this->opnameRepo->update($id, ['status' => StockOpnameStatus::SUBMITTED, 'submitted_at' => now()]);
    }

    public function approve(int $id, $user): StockOpname
    {
        return DB::transaction(function () use ($id, $user) {
            $opname = $this->opnameRepo->findWithItems($id);

            if (!$opname->isSubmitted()) throw ValidationException::withMessages(['status' => 'Hanya opname submitted yang bisa diapprove.']);

            foreach ($opname->items as $opnameItem) {
                if ($opnameItem->difference === 0) continue;

                $item   = $this->itemRepo->findById($opnameItem->item_id);
                $before = $item->stock_quantity;
                $after  = $opnameItem->physical_quantity;

                $item->update(['stock_quantity' => $after]);

                $this->movementRepo->record([
                    'item_id'        => $item->id,
                    'reference_type' => StockOpname::class,
                    'reference_id'   => $opname->id,
                    'type'           => StockMovementType::ADJUSTMENT,
                    'quantity'       => $opnameItem->difference,
                    'stock_before'   => $before,
                    'stock_after'    => $after,
                    'note'           => "Adjustment opname #{$opname->opname_number}",
                    'created_by'     => $user->id,
                ]);

                $opnameItem->update(['adjustment_approved' => true]);
            }

            return $this->opnameRepo->update($id, [
                'status'      => StockOpnameStatus::ADJUSTED,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        });
    }

    public function reject(int $id, array $data, $user): StockOpname
    {
        $opname = $this->opnameRepo->findById($id);
        if (!$opname->isSubmitted()) throw ValidationException::withMessages(['status' => 'Hanya opname submitted yang bisa direject.']);

        return $this->opnameRepo->update($id, [
            'status'        => StockOpnameStatus::DRAFT,
            'approved_by'   => null,
            'submitted_at'  => null,
        ]);
    }
}