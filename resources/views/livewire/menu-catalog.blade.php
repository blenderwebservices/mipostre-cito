<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Header + Search + Active Zone --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Menú Artesanal</h1>
            <p class="text-xs sm:text-sm text-slate-500">
                @if($activeZone)
                    Entregando en <strong>{{ $activeZone->neighborhood }} (CP {{ $activeZone->zip_code }})</strong> · Envío gratis desde ${{ number_format($activeZone->min_order_for_free_shipping, 0) }} MXN
                @else
                    Explora todos los sabores y elige tu tamaño ideal sin necesidad de registrarte.
                @endif
            </p>
        </div>

        <div class="relative w-full md:w-80">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" wire:model.live.debounce.250ms="search" placeholder="Buscar sabor (Limón, Oreo, Tapioca...)"
                class="w-full pl-10 pr-4 py-2.5 rounded-full bg-white border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
        </div>
    </div>

    {{-- Toast --}}
    @if($toastMessage)
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 2500)" x-show="show" x-transition
            class="fixed top-20 right-4 z-50 bg-emerald-600 text-white px-4 py-2.5 rounded-2xl shadow-xl text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ $toastMessage }}</span>
        </div>
    @endif

    {{-- Carrusel Horizontal Superior de Categorías (Especificación 3.2) --}}
    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-4 mb-6">
        <button wire:click="selectCategory(null)"
            class="whitespace-nowrap px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $selectedCategoryId === null ? 'bg-gepp-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:border-gepp-400' }}">
            Todos los Postres
        </button>
        @foreach($categories as $category)
            <button wire:click="selectCategory({{ $category->id }})"
                class="whitespace-nowrap inline-flex items-center gap-2 px-4 py-2 rounded-full text-xs sm:text-sm font-bold transition-all cursor-pointer {{ $selectedCategoryId === $category->id ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-200 hover:border-brand-300' }}">
                <i class="{{ $category->icon }} text-xs"></i>
                <span>{{ $category->name }}</span>
            </button>
        @endforeach
    </div>

    {{-- Grid de Productos con Selector Rápido de Tamaño --}}
    @if($products->isEmpty())
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center">
            <p class="font-bold text-slate-800">No encontramos postres con esa búsqueda.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                @php
                    $activeVarId = $selectedVariations[$product->id] ?? $product->variations->first()?->id;
                    $activeVar = $product->variations->firstWhere('id', $activeVarId) ?? $product->variations->first();
                    $inCartQty = $activeVar ? ($cart[$activeVar->id]['quantity'] ?? 0) : 0;
                @endphp
                <div class="bg-white rounded-3xl border border-slate-200 shadow-2xs hover:shadow-lg transition-all flex flex-col justify-between overflow-hidden">
                    <div>
                        <div class="h-36 {{ $product->category?->bg_color ?? 'bg-pink-100' }} flex items-center justify-center relative">
                            <i class="{{ $product->category?->icon ?? 'fa-solid fa-ice-cream' }} text-5xl {{ $product->category?->text_color ?? 'text-pink-600' }}"></i>
                            @if($product->badge)
                                <span class="absolute top-3 left-3 bg-white/95 text-brand-600 text-[11px] font-bold px-2.5 py-0.5 rounded-full shadow-xs">
                                    {{ $product->badge }}
                                </span>
                            @endif
                            @if($activeVar?->is_returnable_container)
                                <span class="absolute top-3 right-3 bg-gepp-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                                    <i class="fa-solid fa-recycle mr-0.5"></i> Retornable
                                </span>
                            @endif
                        </div>

                        <div class="p-5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gepp-600">{{ $product->category?->name }}</span>
                            <h3 class="font-extrabold text-slate-900 text-base mt-0.5">{{ $product->name }}</h3>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $product->description }}</p>

                            {{-- Selector Rápido de Tamaño (Variaciones) --}}
                            <div class="mt-4">
                                <span class="block text-[10px] font-bold uppercase text-slate-400 mb-1.5">Elige presentación:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($product->variations as $var)
                                        <button type="button" wire:click="selectVariation({{ $product->id }}, {{ $var->id }})"
                                            class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border transition-all cursor-pointer {{ $activeVar?->id === $var->id ? 'bg-gepp-600 text-white border-gepp-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:border-gepp-400' }}">
                                            {{ str_replace(['Vaso Individual ', 'Medio Litro ', 'Familiar ', 'Formato Pay '], '', $var->name) }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($activeVar)
                        <div class="px-5 pb-5 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                            <div>
                                @if($activeVar->original_price)
                                    <span class="text-xs text-slate-400 line-through block">${{ number_format($activeVar->original_price, 2) }}</span>
                                @endif
                                <span class="text-xl font-extrabold text-slate-900">${{ number_format($activeVar->price, 2) }}</span>
                                <span class="text-[10px] text-slate-400">MXN</span>
                            </div>

                            @if($inCartQty > 0)
                                <div class="inline-flex items-center bg-gepp-50 border border-gepp-200 rounded-full p-1">
                                    <button wire:click="updateVariationQty({{ $activeVar->id }}, -1)" class="w-7 h-7 rounded-full bg-white text-gepp-700 font-bold text-sm cursor-pointer">-</button>
                                    <span class="px-3 text-sm font-bold text-gepp-700">{{ $inCartQty }}</span>
                                    <button wire:click="updateVariationQty({{ $activeVar->id }}, 1)" class="w-7 h-7 rounded-full bg-gepp-600 text-white font-bold text-sm cursor-pointer">+</button>
                                </div>
                            @else
                                <button wire:click="addToCart({{ $product->id }})"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm cursor-pointer">
                                    <i class="fa-solid fa-plus"></i> Agregar
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Floating Action Button (FAB) del Carrito (Especificación 3.1) --}}
    @if($cartSummary['count'] > 0)
        <a href="{{ route('cart') }}" wire:navigate
            class="fixed bottom-20 md:bottom-6 left-4 md:left-6 z-40 inline-flex items-center gap-3 px-5 py-3.5 rounded-full bg-gepp-600 hover:bg-gepp-700 text-white shadow-2xl transition-all hover:scale-105">
            <i class="fa-solid fa-basket-shopping text-lg"></i>
            <div class="text-left leading-tight">
                <span class="block text-xs font-bold">{{ $cartSummary['count'] }} {{ $cartSummary['count'] === 1 ? 'ítem' : 'ítems' }}</span>
                <span class="block text-[11px] text-blue-100 font-semibold">${{ number_format($cartSummary['subtotal'], 2) }} MXN · Ir al carrito →</span>
            </div>
        </a>
    @endif
</div>

