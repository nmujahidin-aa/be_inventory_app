<?php
namespace App\Http\Resources\Item;
use Illuminate\Http\Resources\Json\JsonResource;
class ItemResource extends JsonResource {
    public function toArray($request): array {
        return [
            'id'               => $this->id,
            'code'             => $this->code,
            'name'             => $this->name,
            'description'      => $this->description,
            'category'         => $this->whenLoaded('category', fn() => ['id' => $this->category->id, 'name' => $this->category->name]),
            'unit'             => $this->whenLoaded('unit', fn() => ['id' => $this->unit->id, 'name' => $this->unit->name]),
            'stock_quantity'   => $this->stock_quantity,
            'reserved_quantity'=> $this->reserved_quantity,
            'available_stock'  => $this->available_stock,
            'min_stock'        => $this->min_stock,
            'is_low_stock'     => $this->isLowStock(),
            'is_active'        => $this->is_active,
            'created_at'       => $this->created_at?->toDateTimeString(),
        ];
    }
}