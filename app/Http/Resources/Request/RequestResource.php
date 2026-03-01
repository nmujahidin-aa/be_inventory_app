<?php
namespace App\Http\Resources\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class RequestResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id'               => $this->id,
            'request_number'   => $this->request_number,
            'status'           => $this->status,
            'notes'            => $this->notes,
            'rejection_reason' => $this->rejection_reason,
            'submitted_at'     => $this->submitted_at?->toDateTimeString(),
            'approved_at'      => $this->approved_at?->toDateTimeString(),
            'requester'        => $this->whenLoaded('requester', fn() => ['id' => $this->requester->id, 'name' => $this->requester->name]),
            'approver'         => $this->whenLoaded('approver', fn() => $this->approver ? ['id' => $this->approver->id, 'name' => $this->approver->name] : null),
            'items'            => $this->whenLoaded('items', fn() => $this->items->map(fn($i) => [
                'id'                 => $i->id,
                'item'               => ['id' => $i->item->id, 'name' => $i->item->name, 'code' => $i->item->code],
                'quantity_requested' => $i->quantity_requested,
                'quantity_approved'  => $i->quantity_approved,
                'note'               => $i->note,
            ])),
            'created_at'       => $this->created_at?->toDateTimeString(),
        ];
    }
}