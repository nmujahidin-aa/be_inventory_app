<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Enums\ReceivingStatus;

class Receiving extends Model
{
    protected $fillable = [
        'receiving_number', 'purchase_order_id', 'received_by',
        'status', 'notes', 'received_at',
    ];

    protected function casts(): array
    {
        return [
            'status'      => ReceivingStatus::class,
            'received_at' => 'datetime',
        ];
    }

    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function receiver(): BelongsTo { return $this->belongsTo(User::class, 'received_by'); }
    public function items(): HasMany { return $this->hasMany(ReceivingItem::class); }
    public function stockMovements(): MorphMany { return $this->morphMany(StockMovement::class, 'reference'); }
}
