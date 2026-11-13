<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'customer_name',
        'customer_phone',
        'delivery_zone',
        'delivery_address',
        'scheduled_date',
        'scheduled_slot',
        'payment_method',
        'subtotal',
        'delivery_fee',
        'total',
        'notes',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public static function generateOrderNumber(): string
    {
        return 'MP-' . now()->format('ymd') . '-' . strtoupper(substr(uniqid(), -4));
    }
}

