<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Enums\StockOpnameStatus;

class StockOpname extends Model
{
    protected $fillable = [
        'opname_number', 'created_by', 'approved_by', 'status',
        'opname_date', 'notes', 'submitted_at', 'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'status'       => StockOpnameStatus::class,
            'opname_date'  => 'date',
            'submitted_at' => 'datetime',
            'approved_at'  => 'datetime',
        ];
    }

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function items(): HasMany { return $this->hasMany(StockOpnameItem::class); }
    public function stockMovements(): MorphMany { return $this->morphMany(StockMovement::class, 'reference'); }

    public function isDraft(): bool { return $this->status === StockOpnameStatus::DRAFT; }
}
