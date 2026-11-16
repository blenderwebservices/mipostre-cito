<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_cart_value',
        'valid_from',
        'valid_until',
        'usage_limit',
        'times_used',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_cart_value' => 'decimal:2',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'usage_limit' => 'integer',
            'times_used' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function isValidForSubtotal(float $subtotal): array
    {
        if (! $this->is_active) {
            return ['valid' => false, 'message' => 'Este cupón ya no está activo.'];
        }

        $today = now()->toDateString();
        if ($this->valid_from && $this->valid_from->toDateString() > $today) {
            return ['valid' => false, 'message' => 'Este cupón aún no inicia su vigencia.'];
        }
        if ($this->valid_until && $this->valid_until->toDateString() < $today) {
            return ['valid' => false, 'message' => 'Este cupón ha expirado.'];
        }

        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Este cupón alcanzó su límite de usos.'];
        }

        if ($subtotal < (float) $this->min_cart_value) {
            return [
                'valid' => false,
                'message' => 'Compra mínima requerida de $' . number_format($this->min_cart_value, 2) . ' MXN para aplicar este cupón.',
            ];
        }

        return ['valid' => true, 'message' => 'Cupón aplicado correctamente.'];
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percentage') {
            return round($subtotal * ((float) $this->discount_value / 100), 2);
        }

        return min($subtotal, (float) $this->discount_value);
    }
}

