<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'coupon_id',
        'shipping_zone_id',
        'customer_name',
        'customer_phone',
        'delivery_address_snapshot',
        'subtotal',
        'discount_amount',
        'shipping_cost',
        'total_amount',
        'delivery_date',
        'delivery_time_slot',
        'status',
        'payment_method',
        'notes',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'PJ-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendiente',
            'preparing' => 'En preparación',
            'en_route' => 'En camino',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            default => ucfirst($status),
        };
    }
}
