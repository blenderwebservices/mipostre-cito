<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'flavor_id',
        'presentation_id',
        'sku',
        'name',
        'slug',
        'description',
        'price',
        'original_price',
        'stock',
        'image_url',
        'badge',
        'is_available',
        'is_promo',
        'is_returnable_container',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'stock' => 'integer',
            'is_available' => 'boolean',
            'is_promo' => 'boolean',
            'is_returnable_container' => 'boolean',
        ];
    }

    public function flavor(): BelongsTo
    {
        return $this->belongsTo(Flavor::class);
    }

    public function presentation(): BelongsTo
    {
        return $this->belongsTo(Presentation::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function toRagDocument(): string
    {
        $flavorName = $this->flavor?->name ?? 'General';
        $presentationName = $this->presentation?->name ?? 'Estándar';
        $capacity = $this->presentation?->capacity ?? '';
        $promoText = $this->is_promo && $this->original_price
            ? "Promoción activa: Antes \${$this->original_price} MXN, ahora \${$this->price} MXN."
            : "Precio: \${$this->price} MXN.";
        $returnableText = $this->is_returnable_container
            ? 'Envase de vidrio retornable tipo Mi Mandadito (bonificación en próxima entrega).'
            : 'Envase grado alimenticio incluido.';

        return implode("\n", [
            "Producto ID: {$this->id} | SKU: {$this->sku}",
            "Nombre: {$this->name}",
            "Sabor: {$flavorName}",
            "Presentación: {$presentationName} ({$capacity})",
            $promoText,
            "Disponibilidad: " . ($this->is_available && $this->stock > 0 ? "Disponible ({$this->stock} en existencia)" : 'Agotado temporalmente'),
            "Descripción: {$this->description}",
            $returnableText,
        ]);
    }
}

