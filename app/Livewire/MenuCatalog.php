<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithCart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingZone;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MenuCatalog extends Component
{
    use InteractsWithCart;

    public string $search = '';
    public ?int $selectedCategoryId = null;
    public array $selectedVariations = [];
    public ?string $toastMessage = null;

    public function mount(): void
    {
        $products = Product::with(['variations' => fn ($q) => $q->where('is_active', true)->orderBy('price')])
            ->where('is_active', true)
            ->get();

        foreach ($products as $product) {
            $firstVar = $product->variations->first();
            if ($firstVar) {
                $this->selectedVariations[$product->id] = $firstVar->id;
            }
        }
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategoryId = $this->selectedCategoryId === $categoryId ? null : $categoryId;
    }

    public function selectVariation(int $productId, int $variationId): void
    {
        $this->selectedVariations[$productId] = $variationId;
    }

    public function addToCart(int $productId): void
    {
        $variationId = $this->selectedVariations[$productId] ?? null;
        if (! $variationId) {
            return;
        }

        $variation = $this->addVariationToSessionCart($variationId, 1);
        if ($variation) {
            $this->toastMessage = "Agregado: {$variation->product->name} ({$variation->name})";
        }
    }

    public function updateVariationQty(int $variationId, int $delta): void
    {
        $cart = $this->getSessionCart();
        if (! isset($cart[$variationId])) {
            return;
        }

        $cart[$variationId]['quantity'] += $delta;
        if ($cart[$variationId]['quantity'] <= 0) {
            unset($cart[$variationId]);
        }

        $this->saveSessionCart($cart);
    }

    public function render()
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        $query = Product::with(['category', 'variations' => fn ($q) => $q->where('is_active', true)->orderBy('price')])
            ->where('is_active', true);

        if ($this->selectedCategoryId) {
            $query->where('category_id', $this->selectedCategoryId);
        }

        if (trim($this->search) !== '') {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$term}%"));
            });
        }

        $products = $query->orderByDesc('is_favorite')->orderBy('name')->get();
        $activeZone = session()->has('postrecito_zone_id')
            ? ShippingZone::find(session('postrecito_zone_id'))
            : null;

        return view('livewire.menu-catalog', [
            'categories' => $categories,
            'products' => $products,
            'cart' => $this->getSessionCart(),
            'cartSummary' => $this->getCartSummary(),
            'activeZone' => $activeZone,
        ]);
    }
}

