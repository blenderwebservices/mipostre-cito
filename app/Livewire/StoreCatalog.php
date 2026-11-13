<?php

namespace App\Livewire;

use App\Models\Flavor;
use App\Models\Order;
use App\Models\Presentation;
use App\Models\Product;
use App\Services\RagService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class StoreCatalog extends Component
{
    // Filtros de navegación
    public string $search = '';
    public ?int $selectedFlavorId = null;
    public ?int $selectedPresentationId = null;
    public string $activeTab = 'catalogo'; // catalogo, historia, presentaciones
    public string $selectedZone = 'Zona Centro / Roma / Condesa';

    // Carrito de compras (id => ['product' => Product, 'quantity' => int])
    public array $cart = [];
    public bool $isCartOpen = false;
    public bool $isCheckoutOpen = false;

    // Datos de Checkout / Pedido
    public string $customerName = '';
    public string $customerPhone = '';
    public string $deliveryAddress = '';
    public string $scheduledDate = '';
    public string $scheduledSlot = '13:00 - 16:00';
    public string $paymentMethod = 'efectivo';
    public string $orderNotes = '';

    // Notificaciones flash en UI
    public ?string $feedbackMessage = null;
    public ?string $feedbackType = 'success';

    protected $listeners = [
        'addToCartFromChat' => 'handleAddToCartFromChat',
        'openCart' => 'openCartDrawer',
        'openCheckout' => 'proceedToCheckout',
    ];

    public function mount(): void
    {
        $this->scheduledDate = now()->addDay()->toDateString();

        if (Auth::check()) {
            $user = Auth::user();
            $this->customerName = $user->name;
            $this->customerPhone = $user->phone ?? '';
            $this->deliveryAddress = $user->default_address ?? '';
            $this->selectedZone = $user->default_zone ?? $this->selectedZone;
        }

        // Cargar carrito de sesión si existe
        $savedCart = session()->get('postrecito_cart', []);
        if (is_array($savedCart)) {
            $this->cart = $savedCart;
        }
    }

    public function filterByFlavor(?int $flavorId): void
    {
        $this->selectedFlavorId = $this->selectedFlavorId === $flavorId ? null : $flavorId;
    }

    public function filterByPresentation(?int $presentationId): void
    {
        $this->selectedPresentationId = $this->selectedPresentationId === $presentationId ? null : $presentationId;
    }

    public function addToCart(int $productId, int $qty = 1): void
    {
        $product = Product::with(['flavor', 'presentation'])->find($productId);
        if (! $product || ! $product->is_available) {
            $this->showFeedback('Producto no disponible en este momento.', 'error');
            return;
        }

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity'] += $qty;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
                'flavor' => $product->flavor?->name,
                'presentation' => $product->presentation?->name,
                'capacity' => $product->presentation?->capacity,
                'icon' => $product->flavor?->icon ?? 'fa-solid fa-ice-cream',
                'quantity' => $qty,
            ];
        }

        $this->syncCart();
        $this->showFeedback("¡Agregado al carrito: {$product->name}!");
    }

    public function updateQuantity(int $productId, int $change): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity'] += $change;
            if ($this->cart[$productId]['quantity'] <= 0) {
                unset($this->cart[$productId]);
            }
            $this->syncCart();
        }
    }

    public function removeFromCart(int $productId): void
    {
        unset($this->cart[$productId]);
        $this->syncCart();
        $this->showFeedback('Producto retirado del carrito.');
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->syncCart();
        $this->showFeedback('Carrito vaciado.');
    }

    public function openCartDrawer(): void
    {
        $this->isCartOpen = true;
    }

    public function closeCartDrawer(): void
    {
        $this->isCartOpen = false;
    }

    public function proceedToCheckout(): void
    {
        if (empty($this->cart)) {
            $this->showFeedback('Tu carrito está vacío. Agrega algún delicioso postrecito antes de continuar.', 'error');
            return;
        }

        if (! Auth::check()) {
            // Guardar intención de compra e invitar al login
            session()->put('postrecito_cart', $this->cart);
            session()->put('url.intended', url()->current());
            $this->redirect(route('login'));
            return;
        }

        $this->isCartOpen = false;
        $this->isCheckoutOpen = true;
    }

    public function submitOrder(RagService $ragService): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->validate([
            'customerName' => 'required|min:3',
            'customerPhone' => 'required|min:8',
            'deliveryAddress' => 'required|min:6',
            'scheduledDate' => 'required|date|after_or_equal:today',
            'scheduledSlot' => 'required',
            'paymentMethod' => 'required|in:efectivo,transferencia,tarjeta_entrega',
        ], [
            'customerName.required' => 'Indica quién recibe el pedido.',
            'customerPhone.required' => 'Indica un teléfono para coordinar tu entrega.',
            'deliveryAddress.required' => 'La dirección de entrega es obligatoria.',
            'scheduledDate.required' => 'Elige la fecha de entrega.',
        ]);

        $orderData = [
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'delivery_zone' => $this->selectedZone,
            'delivery_address' => $this->deliveryAddress,
            'scheduled_date' => $this->scheduledDate,
            'scheduled_slot' => $this->scheduledSlot,
            'payment_method' => $this->paymentMethod,
            'notes' => $this->orderNotes,
            'items' => array_map(fn ($item) => [
                'product_id' => $item['id'],
                'product_name' => $item['name'],
                'quantity' => $item['quantity'],
            ], $this->cart),
        ];

        $order = $ragService->createOrderFromData(Auth::user(), $orderData, source: 'web');

        $this->cart = [];
        $this->syncCart();
        $this->isCheckoutOpen = false;

        session()->flash('order_success', [
            'number' => $order->order_number,
            'total' => $order->total,
            'date' => $order->scheduled_date->format('d/m/Y'),
            'slot' => $order->scheduled_slot,
        ]);

        $this->redirect(route('orders.index'));
    }

    public function handleAddToCartFromChat(int $productId, int $qty = 1): void
    {
        $this->addToCart($productId, $qty);
    }

    protected function syncCart(): void
    {
        session()->put('postrecito_cart', $this->cart);
        $this->dispatch('cartUpdated', count: $this->cartCount);
    }

    protected function showFeedback(string $message, string $type = 'success'): void
    {
        $this->feedbackMessage = $message;
        $this->feedbackType = $type;
    }

    public function getCartCountProperty(): int
    {
        return array_sum(array_column($this->cart, 'quantity'));
    }

    public function getSubtotalProperty(): float
    {
        $subtotal = 0.0;
        foreach ($this->cart as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
        }
        return $subtotal;
    }

    public function getDeliveryFeeProperty(): float
    {
        if ($this->subtotal <= 0) return 0.0;
        return $this->subtotal >= 150.00 ? 0.0 : 35.00;
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->deliveryFee;
    }

    public function render()
    {
        $flavors = Flavor::where('is_active', true)->orderBy('sort_order')->get();
        $presentations = Presentation::where('is_active', true)->orderBy('sort_order')->get();

        $query = Product::with(['flavor', 'presentation'])
            ->where('is_available', true);

        if ($this->selectedFlavorId) {
            $query->where('flavor_id', $this->selectedFlavorId);
        }

        if ($this->selectedPresentationId) {
            $query->where('presentation_id', $this->selectedPresentationId);
        }

        if (trim($this->search) !== '') {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhereHas('flavor', fn ($f) => $f->where('name', 'like', "%{$term}%"));
            });
        }

        $products = $query->orderBy('is_promo', 'desc')->orderBy('price')->get();

        return view('livewire.store-catalog', [
            'flavors' => $flavors,
            'presentations' => $presentations,
            'products' => $products,
        ]);
    }
}
