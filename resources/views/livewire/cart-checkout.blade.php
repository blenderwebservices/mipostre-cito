<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Tu Carrito y Entrega</h1>
            <p class="text-xs sm:text-sm text-slate-500">Revisa tu pedido, aplica cupones y programa tu horario de entrega.</p>
        </div>
        <a href="{{ route('menu') }}" wire:navigate class="text-xs sm:text-sm font-bold text-gepp-600 hover:underline">← Seguir agregando</a>
    </div>

    @if(empty($cart))
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center">
            <i class="fa-solid fa-basket-shopping text-5xl text-slate-200 mb-4"></i>
            <h2 class="text-lg font-bold text-slate-800">Tu carrito está vacío</h2>
            <p class="text-sm text-slate-500 mt-1 mb-6">Explora los 8 sabores de Postrecito de Jaan y arma tu pedido.</p>
            <a href="{{ route('menu') }}" wire:navigate class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-brand-600 text-white text-sm font-bold hover:bg-brand-700">
                Ir al Menú
            </a>
        </div>
    @else
        <div class="grid lg:grid-cols-12 gap-8">
            {{-- Columna Izquierda: Ítems + Cupón --}}
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-4">
                    <h2 class="font-extrabold text-slate-900 text-base">Resumen de Productos</h2>
                    @foreach($cart as $item)
                        <div class="flex items-center justify-between gap-4 py-3 border-b border-slate-100 last:border-0">
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm text-slate-900">{{ $item['product_name'] }}</h3>
                                <p class="text-xs text-slate-500">{{ $item['variation_name'] }} · ${{ number_format($item['price'], 2) }} c/u</p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="inline-flex items-center bg-slate-100 rounded-full p-1">
                                    <button wire:click="updateQuantity({{ $item['variation_id'] }}, -1)" class="w-7 h-7 rounded-full bg-white text-slate-700 font-bold text-xs cursor-pointer">-</button>
                                    <span class="px-3 text-xs font-bold">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateQuantity({{ $item['variation_id'] }}, 1)" class="w-7 h-7 rounded-full bg-gepp-600 text-white font-bold text-xs cursor-pointer">+</button>
                                </div>
                                <span class="font-extrabold text-sm text-slate-900 w-20 text-right">${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                <button wire:click="removeItem({{ $item['variation_id'] }})" class="text-slate-400 hover:text-rose-500 cursor-pointer">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Cupón de Descuento --}}
                <div class="bg-white rounded-3xl border border-slate-200 p-6">
                    <h2 class="font-extrabold text-slate-900 text-base mb-3">¿Tienes un Cupón de Descuento?</h2>
                    <div class="flex gap-2">
                        <input type="text" wire:model="couponCode" placeholder="Ej. BIENVENIDO15, BUENFIN20"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm uppercase font-mono focus:ring-2 focus:ring-brand-500 outline-none">
                        @if($appliedCouponId)
                            <button type="button" wire:click="removeCoupon" class="px-4 py-2.5 rounded-xl bg-rose-100 text-rose-700 text-xs font-bold cursor-pointer">Quitar</button>
                        @else
                            <button type="button" wire:click="applyCoupon" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold cursor-pointer">Aplicar</button>
                        @endif
                    </div>
                    @if($couponFeedback)
                        <p class="text-xs mt-2 font-semibold {{ $couponValid ? 'text-emerald-600' : 'text-rose-600' }}">{{ $couponFeedback }}</p>
                    @endif
                </div>
            </div>

            {{-- Columna Derecha: Envío, Horario, Totales y Confirmación --}}
            <div class="lg:col-span-5">
                <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-5 sticky top-24">
                    <h2 class="font-extrabold text-slate-900 text-lg">Detalles de Entrega</h2>

                    {{-- Zona de Envío --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Zona de Envío (Cálculo dinámico)</label>
                        <select wire:model.live="selectedShippingZoneId" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm outline-none">
                            @foreach($zones as $z)
                                <option value="{{ $z->id }}">{{ $z->neighborhood }} (CP {{ $z->zip_code }}) — ${{ number_format($z->delivery_cost, 0) }}</option>
                            @endforeach
                        </select>
                        @if($selectedZone && $subtotal < $selectedZone->min_order_for_free_shipping)
                            <p class="text-[11px] text-gepp-600 mt-1 font-semibold">
                                Agrega ${{ number_format($selectedZone->min_order_for_free_shipping - $subtotal, 2) }} MXN más para envío GRATIS en {{ $selectedZone->neighborhood }}.
                            </p>
                        @endif
                    </div>

                    @auth
                        <form wire:submit="confirmOrder" class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Recibe *</label>
                                    <input type="text" wire:model="customerName" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Teléfono *</label>
                                    <input type="tel" wire:model="customerPhone" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                                </div>
                            </div>

                            @if($addresses->isNotEmpty())
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Dirección guardada</label>
                                    <select wire:model.live="selectedAddressId" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                                        @foreach($addresses as $addr)
                                            <option value="{{ $addr->id }}">{{ $addr->tag }}: {{ $addr->fullAddress() }}</option>
                                        @endforeach
                                        <option value="">Otra dirección...</option>
                                    </select>
                                </div>
                            @endif

                            @if(! $selectedAddressId)
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Dirección de entrega *</label>
                                    <input type="text" wire:model="customAddress" placeholder="Calle, número, colonia y referencias" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                                    @error('customAddress') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                </div>
                            @endif

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Fecha *</label>
                                    <input type="date" wire:model="deliveryDate" min="{{ now()->toDateString() }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Horario *</label>
                                    <select wire:model="deliveryTimeSlot" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                                        <option value="10:00 - 13:00">10:00 - 13:00</option>
                                        <option value="13:00 - 16:00">13:00 - 16:00</option>
                                        <option value="16:00 - 19:00">16:00 - 19:00</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Método de Pago</label>
                                <select wire:model="paymentMethod" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                                    <option value="efectivo">Efectivo contra entrega</option>
                                    <option value="transferencia">Transferencia SPEI</option>
                                    <option value="tarjeta_entrega">Tarjeta al recibir</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Notas</label>
                                <input type="text" wire:model="notes" placeholder="Indicaciones adicionales..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm">
                            </div>

                            <div class="pt-4 border-t border-slate-100 space-y-2 text-sm">
                                <div class="flex justify-between text-slate-600"><span>Subtotal</span><span>${{ number_format($subtotal, 2) }}</span></div>
                                @if($discount > 0)
                                    <div class="flex justify-between text-emerald-600 font-semibold"><span>Descuento ({{ $couponCode }})</span><span>-${{ number_format($discount, 2) }}</span></div>
                                @endif
                                <div class="flex justify-between text-slate-600">
                                    <span>Envío</span>
                                    <span>{{ $shipping == 0 ? 'GRATIS' : '$' . number_format($shipping, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-lg font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                                    <span>Total</span>
                                    <span class="text-brand-600">${{ number_format($total, 2) }} MXN</span>
                                </div>
                            </div>

                            <button type="submit" class="w-full py-3.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-lg cursor-pointer">
                                Confirmar Pedido (${{ number_format($total, 2) }} MXN)
                            </button>
                        </form>
                    @else
                        <div class="pt-4 border-t border-slate-100 space-y-2 text-sm">
                            <div class="flex justify-between text-slate-600"><span>Subtotal</span><span>${{ number_format($subtotal, 2) }}</span></div>
                            @if($discount > 0)
                                <div class="flex justify-between text-emerald-600 font-semibold"><span>Descuento</span><span>-${{ number_format($discount, 2) }}</span></div>
                            @endif
                            <div class="flex justify-between text-slate-600"><span>Envío estimado</span><span>{{ $shipping == 0 ? 'GRATIS' : '$' . number_format($shipping, 2) }}</span></div>
                            <div class="flex justify-between text-lg font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                                <span>Total</span>
                                <span class="text-brand-600">${{ number_format($total, 2) }} MXN</span>
                            </div>
                        </div>

                        <button type="button" wire:click="confirmOrder"
                            class="w-full py-3.5 rounded-full bg-gepp-600 hover:bg-gepp-700 text-white font-bold text-sm shadow-lg cursor-pointer flex items-center justify-center gap-2">
                            <i class="fa-solid fa-lock"></i> Iniciar Sesión para Pedir
                        </button>
                    @endauth
                </div>
            </div>
        </div>
    @endif
</div>

