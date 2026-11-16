<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithCart;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OrdersPage extends Component
{
    use InteractsWithCart;

    public function reorder(int $orderId): void
    {
        $order = Order::with('items.productVariation.product')
            ->where('user_id', Auth::id())
            ->find($orderId);

        if (! $order) {
            return;
        }

        foreach ($order->items as $item) {
            if ($item->product_variation_id) {
                $this->addVariationToSessionCart($item->product_variation_id, $item->quantity);
            }
        }

        $this->redirect(route('cart'), navigate: true);
    }

    public function render()
    {
        $orders = Auth::user()
            ->orders()
            ->with(['items', 'coupon', 'shippingZone'])
            ->latest()
            ->get();

        return view('livewire.orders-page', [
            'orders' => $orders,
        ]);
    }
}

