<?php
namespace App\Http\Resources\Receiving;
use Illuminate\Http\Resources\Json\JsonResource;
class ReceivingResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id'               => $this->id,
            'receiving_number' => $this->receiving_number,
            'status'           => $this->status,
            'notes'            => $this->notes,
            'received_at'      => $this->received_at?->toDateTimeString(),
            'purchase_order'   => $this->whenLoaded('purchaseOrder', fn() => ['id' => $this->purchaseOrder->id, 'po_number' => $this->purchaseOrder->po_number, 'vendor' => $this->purchaseOrder->vendor?->name]),
            'receiver'         => $this->whenLoaded('receiver', fn() => ['id' => $this->receiver->id, 'name' => $this->receiver->name]),
            'items'            => $this->whenLoaded('items', fn() => $this->items->map(fn($i) => [
                'id'               => $i->id,
                'item'             => ['id' => $i->item->id, 'name' => $i->item->name],
                'quantity_received'=> $i->quantity_received,
                'quantity_returned'=> $i->quantity_returned,
                'quality_status'   => $i->quality_status,
                'note'             => $i->note,
            ])),
            'created_at'       => $this->created_at?->toDateTimeString(),
        ];
    }
}