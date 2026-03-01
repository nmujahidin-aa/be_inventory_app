<?php
namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Enums\PurchaseOrderStatus;
use App\Interfaces\PurchaseOrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    public function __construct(
        private PurchaseOrderRepositoryInterface $poRepo,
    ) {}

    public function list(array $filters){
        return $this->poRepo->all($filters);
    }

    public function find(int $id)
    {
        return $this->poRepo->findWithItems($id);
    }

    public function create(array $data, $user): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $user) {
            $po = $this->poRepo->create([
                'po_number' => $this->poRepo->generateNumber(),
                'vendor_id' => $data['vendor_id'],
                'created_by' => $user->id,
                'status' => PurchaseOrderStatus::DRAFT,
                'notes' => $data['notes'] ?? null,
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $subtotal = ($item['unit_price'] ?? 0) * $item['quantity_ordered'];
                $total   += $subtotal;
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'item_id' => $item['item_id'],
                    'quantity_ordered' => $item['quantity_ordered'],
                    'unit_price' => $item['unit_price'] ?? null,
                    'subtotal' => $subtotal ?: null,
                    'note' => $item['note'] ?? null,
                ]);
            }

            $po->update(['total_amount' => $total ?: null]);
            return $this->poRepo->findWithItems($po->id);
        });
    }

    public function update(int $id, array $data): PurchaseOrder
    {
        $po = $this->poRepo->findById($id);
        if (!$po->isDraft()) throw ValidationException::withMessages(['status' => 'Hanya PO draft yang bisa diedit.']);
        return $this->poRepo->update($id, $data);
    }

    public function delete(int $id): void
    {
        $po = $this->poRepo->findById($id);
        if (!$po->isDraft()) throw ValidationException::withMessages(['status' => 'Hanya PO draft yang bisa dihapus.']);
        $this->poRepo->delete($id);
    }

    public function submit(int $id, $user): PurchaseOrder
    {
        $po = $this->poRepo->findById($id);
        if (!$po->isDraft()) throw ValidationException::withMessages(['status' => 'Hanya PO draft yang bisa disubmit.']);
        return $this->poRepo->update($id, ['status' => PurchaseOrderStatus::PENDING_APPROVAL]);
    }

    public function approve(int $id, $user): PurchaseOrder
    {
        $po = $this->poRepo->findById($id);
        if (!$po->isPendingApproval()) throw ValidationException::withMessages(['status' => 'PO harus dalam status pending approval.']);
        return $this->poRepo->update($id, ['status' => PurchaseOrderStatus::APPROVED, 'approved_by' => $user->id, 'approved_at' => now()]);
    }

    public function reject(int $id, array $data, $user): PurchaseOrder
    {
        $po = $this->poRepo->findById($id);
        if (!$po->isPendingApproval()) throw ValidationException::withMessages(['status' => 'PO harus dalam status pending approval.']);
        return $this->poRepo->update($id, ['status' => PurchaseOrderStatus::REJECTED, 'approved_by' => $user->id, 'rejection_reason' => $data['rejection_reason']]);
    }

    public function sendToVendor(int $id, $user): PurchaseOrder
    {
        $po = $this->poRepo->findById($id);
        if (!$po->isApproved()) throw ValidationException::withMessages(['status' => 'PO harus approved sebelum dikirim ke vendor.']);
        return $this->poRepo->update($id, ['status' => PurchaseOrderStatus::SENT, 'sent_at' => now()]);
    }

    public function confirm(int $id, $user): PurchaseOrder
    {
        $po = $this->poRepo->findById($id);
        if ($po->status !== PurchaseOrderStatus::SENT) throw ValidationException::withMessages(['status' => 'PO harus dalam status sent.']);
        return $this->poRepo->update($id, ['status' => PurchaseOrderStatus::CONFIRMED, 'confirmed_at' => now()]);
    }

    public function cancel(int $id, $user): PurchaseOrder
    {
        $po = $this->poRepo->findById($id);
        $cancellable = [PurchaseOrderStatus::DRAFT, PurchaseOrderStatus::PENDING_APPROVAL, PurchaseOrderStatus::APPROVED];
        if (!in_array($po->status, $cancellable)) throw ValidationException::withMessages(['status' => 'PO tidak bisa dibatalkan pada status ini.']);
        return $this->poRepo->update($id, ['status' => PurchaseOrderStatus::CANCELLED]);
    }
}