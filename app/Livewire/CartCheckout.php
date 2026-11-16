<?php

namespace App\Livewire;

use App\Livewire\Concerns\InteractsWithCart;
use App\Models\Address;
use App\Models\Coupon;
use App\Models\ShippingZone;
use App\Services\RagService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class CartCheckout extends Component
{
    use InteractsWithCart;

    public string $couponCode = '';
    public ?int $appliedCouponId = null;
    public ?string $couponFeedback = null;
    public bool $couponValid = false;

    public ?int $selectedShippingZoneId = null;
    public ?int $selectedAddressId = null;
    public string $customerName = '';
    public string $customerPhone = '';
    public string $customAddress = '';
    public string $deliveryDate = '';
    public string $deliveryTimeSlot = '13:00 - 16:00';
    public string $paymentMethod = 'efectivo';
    public string $notes = '';

    public function mount(): void
    {
        $this->deliveryDate = now()->addDay()->toDateString();

        $this->selectedShippingZoneId = session('postrecito_zone_id')
            ?? ShippingZone::where('is_active', true)->first()?->id;

        if (Auth::check()) {
            $user = Auth::user();
            $this->customerName = $user->name;
            $this->customerPhone = $user->phone_number ?? '';

            $defaultAddr = $user->defaultAddress();
            if ($defaultAddr) {
                $this->selectedAddressId = $defaultAddr->id;
                $matchedZone = ShippingZone::findByQuery($defaultAddr->zip_code);
                if ($matchedZone) {
                    $this->selectedShippingZoneId = $matchedZone->id;
                }
            }
        }

        if (session()->has('postrecito_coupon_code')) {
            $this->couponCode = (string) session('postrecito_coupon_code');
            $this->applyCoupon();
        }
    }

    public function updatedSelectedAddressId($value): void
    {
        if (! $value || ! Auth::check()) {
            return;
        }

        $addr = Address::where('user_id', Auth::id())->find($value);
        if ($addr) {
            $zone = ShippingZone::findByQuery($addr->zip_code);
            if ($zone) {
                $this->selectedShippingZoneId = $zone->id;
            }
        }
    }

    public function updateQuantity(int $variationId, int $delta): void
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

        if ($this->appliedCouponId) {
            $this->revalidateCoupon();
        }
    }

    public function removeItem(int $variationId): void
    {
        $cart = $this->getSessionCart();
        unset($cart[$variationId]);
        $this->saveSessionCart($cart);

        if ($this->appliedCouponId) {
            $this->revalidateCoupon();
        }
    }

    public function applyCoupon(): void
    {
        $code = strtoupper(trim($this->couponCode));
        if ($code === '') {
            return;
        }

        $coupon = Coupon::where('code', $code)->first();
        if (! $coupon) {
            $this->appliedCouponId = null;
            $this->couponValid = false;
            $this->couponFeedback = 'El código de cupón no existe.';
            return;
        }

        $subtotal = $this->getCartSummary()['subtotal'];
        $check = $coupon->isValidForSubtotal($subtotal);
        if (! $check['valid']) {
            $this->appliedCouponId = null;
            $this->couponValid = false;
            $this->couponFeedback = $check['message'];
            return;
        }

        $this->appliedCouponId = $coupon->id;
        $this->couponValid = true;
        $this->couponFeedback = $check['message'];
        session()->put('postrecito_coupon_code', $code);
    }

    public function removeCoupon(): void
    {
        $this->couponCode = '';
        $this->appliedCouponId = null;
        $this->couponValid = false;
        $this->couponFeedback = null;
        session()->forget('postrecito_coupon_code');
    }

    protected function revalidateCoupon(): void
    {
        $coupon = Coupon::find($this->appliedCouponId);
        if (! $coupon) {
            $this->removeCoupon();
            return;
        }

        $check = $coupon->isValidForSubtotal($this->getCartSummary()['subtotal']);
        if (! $check['valid']) {
            $this->appliedCouponId = null;
            $this->couponValid = false;
            $this->couponFeedback = $check['message'];
        }
    }

    public function confirmOrder(RagService $ragService): void
    {
        $cart = $this->getSessionCart();
        if (empty($cart)) {
            return;
        }

        if (! Auth::check()) {
            session()->put('url.intended', route('cart'));
            $this->redirect(route('login'), navigate: true);
            return;
        }

        $this->validate([
            'customerName' => 'required|min:3',
            'customerPhone' => 'required|min:8',
            'deliveryDate' => 'required|date|after_or_equal:today',
            'deliveryTimeSlot' => 'required',
            'paymentMethod' => 'required|in:efectivo,transferencia,tarjeta_entrega',
        ]);

        if (! $this->selectedAddressId && trim($this->customAddress) === '') {
            $this->addError('customAddress', 'Selecciona una dirección guardada o escribe tu dirección de entrega.');
            return;
        }

        $order = $ragService->createOrderFromData(Auth::user(), [
            'items' => array_values($cart),
            'address_id' => $this->selectedAddressId,
            'shipping_zone_id' => $this->selectedShippingZoneId,
            'coupon_id' => $this->appliedCouponId,
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'delivery_address_snapshot' => $this->selectedAddressId
                ? Address::find($this->selectedAddressId)?->fullAddress()
                : $this->customAddress,
            'delivery_date' => $this->deliveryDate,
            'delivery_time_slot' => $this->deliveryTimeSlot,
            'payment_method' => $this->paymentMethod,
            'notes' => $this->notes,
        ], source: 'web');

        $this->saveSessionCart([]);
        session()->forget('postrecito_coupon_code');

        session()->flash('order_success', [
            'number' => $order->order_number,
            'total' => $order->total_amount,
            'date' => $order->delivery_date->format('d/m/Y'),
            'slot' => $order->delivery_time_slot,
        ]);

        $this->redirect(route('orders.index'), navigate: true);
    }

    public function render()
    {
        $cart = $this->getSessionCart();
        $subtotal = $this->getCartSummary()['subtotal'];

        $coupon = $this->appliedCouponId ? Coupon::find($this->appliedCouponId) : null;
        $discount = $coupon ? $coupon->calculateDiscount($subtotal) : 0.0;

        $zone = $this->selectedShippingZoneId ? ShippingZone::find($this->selectedShippingZoneId) : null;
        $shipping = $zone ? $zone->calculateShippingCost($subtotal) : ($subtotal >= 200 || $subtotal <= 0 ? 0.0 : 35.0);

        $total = max(0, ($subtotal - $discount) + $shipping);

        $addresses = Auth::check() ? Auth::user()->addresses()->orderByDesc('is_default')->get() : collect();
        $zones = ShippingZone::where('is_active', true)->orderBy('neighborhood')->get();

        return view('livewire.cart-checkout', [
            'cart' => $cart,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,
            'selectedZone' => $zone,
            'addresses' => $addresses,
            'zones' => $zones,
        ]);
    }
}

