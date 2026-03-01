<?php
namespace App\Http\Resources\StockOpname;
use Illuminate\Http\Resources\Json\JsonResource;
class StockOpnameResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id'            => $this->id,
            'opname_number' => $this->opname_number,
            'status'        => $this->status,
            'opname_date'   => $this->opname_date?->toDateString(),
            'notes'         => $this->notes,
            'submitted_at'  => $this->submitted_at?->toDateTimeString(),
            'approved_at'   => $this->approved_at?->toDateTimeString(),
            'creator'       => $this->whenLoaded('creator', fn() => ['id' => $this->creator->id, 'name' => $this->creator->name]),
            'approver'      => $this->whenLoaded('approver', fn() => $this->approver ? ['id' => $this->approver->id, 'name' => $this->approver->name] : null),
            'items'         => $this->whenLoaded('items', fn() => $this->items->map(fn($i) => [
                'id'                  => $i->id,
                'item'                => ['id' => $i->item->id, 'name' => $i->item->name, 'code' => $i->item->code],
                'system_quantity'     => $i->system_quantity,
                'physical_quantity'   => $i->physical_quantity,
                'difference'          => $i->difference,
                'adjustment_approved' => $i->adjustment_approved,
                'note'                => $i->note,
            ])),
            'created_at'    => $this->created_at?->toDateTimeString(),
        ];
    }
}