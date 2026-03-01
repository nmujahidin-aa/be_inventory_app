<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestItem extends Model
{
    protected $fillable = [
        'request_id', 'item_id', 'quantity_requested', 'quantity_approved', 'note',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ItemRequest::class, 'request_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
