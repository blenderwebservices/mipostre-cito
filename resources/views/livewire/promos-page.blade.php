<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-10">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-brand-600">Beneficios y Descuentos</span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">Promociones y Cupones Activos</h1>
    </div>

    @if($appliedToast)
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between">
            <span><i class="fa-solid fa-circle-check mr-2"></i>{{ $appliedToast }}</span>
            <a href="{{ route('cart') }}" wire:navigate class="text-xs font-bold underline">Ir al carrito →</a>
        </div>
    @endif

    {{-- Campañas Activas --}}
    @if($campaigns->isNotEmpty())
        <section class="space-y-4">
            <h2 class="text-lg font-extrabold text-slate-900">Campañas de Temporada</h2>
            <div class="grid md:grid-cols-2 gap-6">
                @foreach($campaigns as $campaign)
                    <div class="rounded-3xl p-6 text-white shadow-lg flex flex-col justify-between" style="background: linear-gradient(135deg, {{ $campaign->theme_color }} 0%, #0072b2 100%);">
                        <div>
                            <span class="inline-block px-3 py-1 rounded-full bg-white/20 text-[11px] font-bold mb-3">
                                Vigente hasta {{ $campaign->end_date->format('d/m/Y') }}
                            </span>
                            <h3 class="text-2xl font-extrabold">{{ $campaign->name }}</h3>
                            @if($campaign->subtitle)
                                <p class="font-handwriting text-2xl text-pink-100 mt-0.5">{{ $campaign->subtitle }}</p>
                            @endif
                            <p class="text-sm text-white/90 mt-2">{{ $campaign->description }}</p>
                        </div>
                        @if($campaign->coupon)
                            <div class="mt-5 pt-4 border-t border-white/20 flex items-center justify-between">
                                <span class="font-mono font-extrabold text-base tracking-wider">{{ $campaign->coupon->code }}</span>
                                <button wire:click="applyCouponToSession('{{ $campaign->coupon->code }}')"
                                    class="px-4 py-1.5 rounded-full bg-white text-slate-900 text-xs font-bold hover:bg-slate-100 cursor-pointer">
                                    Usar cupón
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Cupones Copiables --}}
    <section class="space-y-4">
        <h2 class="text-lg font-extrabold text-slate-900">Cupones Disponibles</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($coupons as $coupon)
                <div class="bg-white rounded-3xl border-2 border-dashed border-brand-200 p-6 flex flex-col justify-between shadow-2xs">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono text-lg font-extrabold text-brand-600 bg-brand-50 px-3 py-1 rounded-xl">{{ $coupon->code }}</span>
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                {{ $coupon->discount_type === 'percentage' ? intval($coupon->discount_value) . '% OFF' : '$' . number_format($coupon->discount_value, 0) . ' MXN OFF' }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 mt-2">{{ $coupon->description }}</p>
                        <p class="text-xs text-slate-400 mt-2">Compra mínima: ${{ number_format($coupon->min_cart_value, 2) }} MXN</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex gap-2">
                        <button wire:click="applyCouponToSession('{{ $coupon->code }}')"
                            class="flex-1 py-2 rounded-full bg-gepp-600 hover:bg-gepp-700 text-white text-xs font-bold cursor-pointer">
                            Aplicar al carrito
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Promociones de Envío por Zona --}}
    <section class="space-y-4">
        <h2 class="text-lg font-extrabold text-slate-900">Envíos Gratis por Zona de Cobertura</h2>
        <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-100">
                @foreach($zones as $zone)
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 text-sm">{{ $zone->neighborhood }}</span>
                            <span class="text-xs font-bold text-gepp-600 bg-gepp-50 px-2.5 py-0.5 rounded-full">CP {{ $zone->zip_code }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Costo estándar: ${{ number_format($zone->delivery_cost, 2) }} MXN</p>
                        <p class="text-xs font-bold text-emerald-600 mt-1">¡Envío GRATIS desde ${{ number_format($zone->min_order_for_free_shipping, 0) }} MXN!</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>

