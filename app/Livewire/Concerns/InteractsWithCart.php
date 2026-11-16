<?php

namespace App\Livewire\Concerns;

use App\Models\ProductVariation;

trait InteractsWithCart
{
    public function getSessionCart(): array
    {
        $cart = session()->get('postrecito_cart', []);
        return is_array($cart) ? $cart : [];
    }

    public function saveSessionCart(array $cart): void
    {
        session()->put('postrecito_cart', $cart);
    }

    public function addVariationToSessionCart(int $variationId, int $qty = 1): ?ProductVariation
    {
        $variation = ProductVariation::with('product')->find($variationId);
        if (! $variation || ! $variation->is_active || ! $variation->product?->is_active) {
            return null;
        }

        $cart = $this->getSessionCart();
        if (isset($cart[$variationId])) {
            $cart[$variationId]['quantity'] += $qty;
        } else {
            $cart[$variationId] = [
                'variation_id' => $variation->id,
                'product_id' => $variation->product_id,
                'product_name' => $variation->product->name,
                'variation_name' => $variation->name,
                'sku' => $variation->sku,
                'price' => (float) $variation->price,
                'quantity' => $qty,
            ];
        }

        $this->saveSessionCart($cart);

        return $variation;
    }

    public function getCartSummary(): array
    {
        $cart = $this->getSessionCart();
        $count = 0;
        $subtotal = 0.0;

        foreach ($cart as $item) {
            $qty = (int) ($item['quantity'] ?? 0);
            $count += $qty;
            $subtotal += ((float) ($item['price'] ?? 0)) * $qty;
        }

        return [
            'count' => $count,
            'subtotal' => $subtotal,
        ];
    }
}

