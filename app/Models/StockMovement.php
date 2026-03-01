<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Enums\StockMovementType;

class StockMovement extends Model
{
    protected $fillable = [
        'item_id', 'reference_type', 'reference_id',
        'type', 'quantity', 'stock_before', 'stock_after',
        'note', 'created_by',
    ];

    protected function casts(): array
    {
        return ['type' => StockMovementType::class];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
