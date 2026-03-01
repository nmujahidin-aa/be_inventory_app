<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{

    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    /**
     * Ambil identifier unik user yang akan disimpan di dalam JWT.
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); 
    }

    /**
     * Tambahkan klaim (claims) tambahan jika diperlukan.
     */
    public function getJWTCustomClaims()
    {
        return [
            'is_active' => $this->is_active,
        ];
    }

    public function requests(): HasMany
    {
        return $this->hasMany(ItemRequest::class, 'requested_by');
    }

    public function approvedRequests(): HasMany
    {
        return $this->hasMany(ItemRequest::class, 'approved_by');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'created_by');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
