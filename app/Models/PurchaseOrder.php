<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Enums\PurchaseOrderStatus;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number', 'vendor_id', 'created_by', 'approved_by', 'status',
        'total_amount', 'notes', 'sent_at', 'confirmed_at', 'approved_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status'       => PurchaseOrderStatus::class,
            'total_amount' => 'decimal:2',
            'sent_at'      => 'datetime',
            'confirmed_at' => 'datetime',
            'approved_at'  => 'datetime',
        ];
    }

    public function vendor(): BelongsTo { return $this->belongsTo(Vendor::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function items(): HasMany { return $this->hasMany(PurchaseOrderItem::class); }
    public function receivings(): HasMany { return $this->hasMany(Receiving::class); }

    public function isDraft(): bool { return $this->status === PurchaseOrderStatus::DRAFT; }
    public function isApproved(): bool { return $this->status === PurchaseOrderStatus::APPROVED; }
}
