<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithCart;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\ShippingZone;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class PromosPage extends Component
{
    use InteractsWithCart;

    public ?string $appliedToast = null;

    public function applyCouponToSession(string $code): void
    {
        session()->put('postrecito_coupon_code', strtoupper($code));
        $this->appliedToast = "Cupón {$code} guardado. Se aplicará en tu carrito.";
    }

    public function render()
    {
        $campaigns = Campaign::currentlyActive()->with('coupon')->get();
        $coupons = Coupon::where('is_active', true)->orderBy('min_cart_value')->get();
        $zones = ShippingZone::where('is_active', true)->orderBy('delivery_cost')->get();

        return view('livewire.promos-page', [
            'campaigns' => $campaigns,
            'coupons' => $coupons,
            'zones' => $zones,
            'cartSummary' => $this->getCartSummary(),
        ]);
    }
}

