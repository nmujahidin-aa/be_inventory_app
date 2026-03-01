<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    protected $fillable = [
        'stock_opname_id', 'item_id', 'system_quantity',
        'physical_quantity', 'adjustment_approved', 'note',
    ];

    protected $appends = ['difference'];

    protected function casts(): array
    {
        return ['adjustment_approved' => 'boolean'];
    }

    public function getDifferenceAttribute(): int
    {
        return $this->physical_quantity - $this->system_quantity;
    }

    public function stockOpname(): BelongsTo { return $this->belongsTo(StockOpname::class); }
    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
}
