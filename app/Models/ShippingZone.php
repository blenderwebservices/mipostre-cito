<?php

namespace App\Models;

use App\Services\RagService;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = [
        'zip_code',
        'neighborhood',
        'zone_name',
        'delivery_cost',
        'min_order_for_free_shipping',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'delivery_cost' => 'decimal:2',
            'min_order_for_free_shipping' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $zone): void {
            $zone->zip_code = trim($zone->zip_code);
            $zone->neighborhood = trim($zone->neighborhood);
        });

        $syncRag = function (): void {
            $zonesText = static::where('is_active', true)->get()
                ->map(fn (self $z) => "CP {$z->zip_code} ({$z->neighborhood}, {$z->zone_name}): Envío \${$z->delivery_cost} MXN (Gratis desde \${$z->min_order_for_free_shipping} MXN)")
                ->implode("\n");

            $chunk = KnowledgeChunk::updateOrCreate(
                [
                    'source_type' => 'shipping_zone',
                    'source_id' => null,
                    'title' => 'Zonas de Cobertura y Costos de Envío',
                ],
                [
                    'content' => "Cobertura actual por Código Postal y Colonia:\n{$zonesText}",
                    'metadata' => ['category' => 'envios'],
                    'is_active' => true,
                ]
            );

            $chunk->update([
                'embedding' => app(RagService::class)->generateEmbedding("{$chunk->title}\n{$chunk->content}"),
            ]);
        };

        static::saved($syncRag);
        static::deleted($syncRag);
    }

    public function calculateShippingCost(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        if ($subtotal >= (float) $this->min_order_for_free_shipping) {
            return 0.0;
        }

        return (float) $this->delivery_cost;
    }

    public static function findByQuery(string $query): ?self
    {
        $term = trim($query);
        if ($term === '') {
            return null;
        }

        return static::where('is_active', true)
            ->where(function ($q) use ($term) {
                $q->where('zip_code', $term)
                    ->orWhere('neighborhood', 'like', "%{$term}%")
                    ->orWhere('zone_name', 'like', "%{$term}%");
            })
            ->first();
    }
}
