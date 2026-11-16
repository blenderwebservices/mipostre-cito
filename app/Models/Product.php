<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'image_path',
        'badge',
        'is_favorite',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_favorite' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class);
    }

    public function toRagDocument(): string
    {
        $catName = $this->category?->name ?? 'Postres Artesanales';
        $varsText = $this->variations()
            ->where('is_active', true)
            ->get()
            ->map(function (ProductVariation $v) {
                $stock = $v->stock_quantity > 0 ? "Disponible ({$v->stock_quantity} pzas)" : 'Agotado';
                $ret = $v->is_returnable_container ? ' [Envase retornable]' : '';
                return "  - {$v->name} (SKU: {$v->sku}): \${$v->price} MXN ({$stock}){$ret}";
            })
            ->implode("\n");

        return implode("\n", [
            "Producto ID: {$this->id} | Categoría: {$catName}",
            "Nombre: {$this->name}",
            "Descripción: {$this->description}",
            "Presentaciones y Precios:",
            $varsText ?: "  - Precio base: \${$this->base_price} MXN",
        ]);
    }
}
