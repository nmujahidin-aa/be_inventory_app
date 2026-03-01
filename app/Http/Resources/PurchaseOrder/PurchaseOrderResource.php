<?php
namespace App\Http\Resources\PurchaseOrder;
use Illuminate\Http\Resources\Json\JsonResource;
class PurchaseOrderResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id'               => $this->id,
            'po_number'        => $this->po_number,
            'status'           => $this->status,
            'total_amount'     => $this->total_amount,
            'notes'            => $this->notes,
            'rejection_reason' => $this->rejection_reason,
            'sent_at'          => $this->sent_at?->toDateTimeString(),
            'confirmed_at'     => $this->confirmed_at?->toDateTimeString(),
            'approved_at'      => $this->approved_at?->toDateTimeString(),
            'vendor'           => $this->whenLoaded('vendor', fn() => ['id' => $this->vendor->id, 'name' => $this->vendor->name]),
            'creator'          => $this->whenLoaded('creator', fn() => ['id' => $this->creator->id, 'name' => $this->creator->name]),
            'approver'         => $this->whenLoaded('approver', fn() => $this->approver ? ['id' => $this->approver->id, 'name' => $this->approver->name] : null),
            'items'            => $this->whenLoaded('items', fn() => $this->items->map(fn($i) => [
                'id'              => $i->id,
                'item'            => ['id' => $i->item->id, 'name' => $i->item->name, 'code' => $i->item->code],
                'quantity_ordered'=> $i->quantity_ordered,
                'unit_price'      => $i->unit_price,
                'subtotal'        => $i->subtotal,
                'note'            => $i->note,
            ])),
            'created_at'       => $this->created_at?->toDateTimeString(),
        ];
    }
}