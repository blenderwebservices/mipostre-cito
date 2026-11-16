<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithCart;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingZone;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class HomePage extends Component
{
    use InteractsWithCart;

    public string $coverageQuery = '';
    public ?string $coverageError = null;
    public ?string $coverageSuccess = null;

    public function checkCoverage(): void
    {
        $this->coverageError = null;
        $this->coverageSuccess = null;

        $zone = ShippingZone::findByQuery($this->coverageQuery);

        if ($zone) {
            session()->put('postrecito_zone_id', $zone->id);
            $this->coverageSuccess = "¡Sí tenemos cobertura en {$zone->neighborhood} (CP {$zone->zip_code})! Envío \${$zone->delivery_cost} MXN (gratis desde \${$zone->min_order_for_free_shipping} MXN).";
            $this->redirect(route('menu'), navigate: true);
            return;
        }

        $this->coverageError = 'Aún no llegamos a esa zona automáticamente. Prueba con CP 06700 (Roma Norte), 03100 (Del Valle), 06140 (Condesa) o 04000 (Coyoacán), o contáctanos por el Chatbot.';
    }

    public function render()
    {
        $activeCampaign = Campaign::currentlyActive()->with('coupon')->first();
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $favoriteProducts = Product::with(['category', 'variations' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->where('is_favorite', true)
            ->take(4)
            ->get();

        $activeZone = session()->has('postrecito_zone_id')
            ? ShippingZone::find(session('postrecito_zone_id'))
            : null;

        return view('livewire.home-page', [
            'activeCampaign' => $activeCampaign,
            'categories' => $categories,
            'favoriteProducts' => $favoriteProducts,
            'activeZone' => $activeZone,
            'cartSummary' => $this->getCartSummary(),
        ]);
    }
}

