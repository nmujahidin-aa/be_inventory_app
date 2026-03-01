<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\QualityStatus;

class ReceivingItem extends Model
{
    protected $fillable = [
        'receiving_id', 'purchase_order_item_id', 'item_id',
        'quantity_received', 'quantity_returned', 'quality_status', 'note',
    ];

    protected function casts(): array
    {
        return ['quality_status' => QualityStatus::class];
    }

    public function receiving(): BelongsTo { return $this->belongsTo(Receiving::class); }
    public function purchaseOrderItem(): BelongsTo { return $this->belongsTo(PurchaseOrderItem::class); }
    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
}
