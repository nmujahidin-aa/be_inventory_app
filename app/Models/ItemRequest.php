<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Enums\RequestStatus;

class ItemRequest extends Model
{
    protected $table = 'requests';

    protected $fillable = [
        'request_number', 'requested_by', 'approved_by',
        'status', 'notes', 'submitted_at', 'approved_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status'       => RequestStatus::class,
            'submitted_at' => 'datetime',
            'approved_at'  => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequestItem::class, 'request_id');
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }

    public function isDraft(): bool { return $this->status === RequestStatus::DRAFT; }
    public function isSubmitted(): bool { return $this->status === RequestStatus::SUBMITTED; }
}
