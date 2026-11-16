<div>
    {{-- 1. Banner de Campaña Activa Dinámica --}}
    @if($activeCampaign)
        <section class="text-white relative overflow-hidden" style="background: linear-gradient(135deg, {{ $activeCampaign->theme_color ?? '#db2777' }} 0%, #0072b2 100%);">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 relative z-10">
                <div class="grid lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-7 space-y-4">
                        <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-xs text-xs font-bold uppercase tracking-wider">
                            <i class="fa-solid fa-sparkles text-amber-300"></i>
                            Campaña Activa · Hasta el {{ $activeCampaign->end_date->format('d/m/Y') }}
                        </span>
                        <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                            {{ $activeCampaign->name }}
                            @if($activeCampaign->subtitle)
                                <span class="block font-handwriting text-4xl sm:text-5xl text-pink-100 mt-1">{{ $activeCampaign->subtitle }}</span>
                            @endif
                        </h1>
                        <p class="text-sm sm:text-base text-white/90 max-w-xl">{{ $activeCampaign->description }}</p>

                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <a href="{{ route('menu') }}" wire:navigate
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white text-slate-900 font-bold text-sm shadow-lg hover:bg-slate-100 transition-all">
                                <i class="fa-solid fa-store text-brand-600"></i> Explorar Menú
                            </a>
                            @if($activeCampaign->coupon)
                                <a href="{{ route('promos') }}" wire:navigate
                                    class="inline-flex items-center gap-2 px-5 py-3 rounded-full bg-white/15 border border-white/30 text-white font-bold text-sm hover:bg-white/25 transition-all">
                                    <i class="fa-solid fa-ticket"></i> Cupón: {{ $activeCampaign->coupon->code }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Validador de Cobertura (Estilo Mi Mandadito) --}}
                    <div class="lg:col-span-5">
                        <div class="bg-white text-slate-800 rounded-3xl p-6 shadow-2xl">
                            <div class="flex items-center gap-2.5 mb-2">
                                <div class="w-9 h-9 rounded-full bg-gepp-50 text-gepp-600 flex items-center justify-center">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div>
                                    <h2 class="font-extrabold text-base text-slate-900">Verifica tu Cobertura</h2>
                                    <p class="text-xs text-slate-500">Ingresa tu Código Postal o Colonia para ver costos de envío</p>
                                </div>
                            </div>

                            <form wire:submit="checkCoverage" class="mt-4 space-y-3">
                                <div class="flex gap-2">
                                    <input type="text" wire:model="coverageQuery" placeholder="Ej. 06700, Del Valle, Roma..."
                                        class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                                    <button type="submit"
                                        class="px-5 py-2.5 rounded-xl bg-gepp-600 hover:bg-gepp-700 text-white font-bold text-sm transition-colors cursor-pointer">
                                        Validar
                                    </button>
                                </div>

                                @if($coverageError)
                                    <p class="text-xs text-rose-600 bg-rose-50 p-2.5 rounded-xl border border-rose-100">{{ $coverageError }}</p>
                                @endif
                                @if($activeZone)
                                    <p class="text-xs text-emerald-700 bg-emerald-50 p-2.5 rounded-xl border border-emerald-100">
                                        <i class="fa-solid fa-circle-check mr-1"></i>
                                        Zona activa: <strong>{{ $activeZone->neighborhood }} (CP {{ $activeZone->zip_code }})</strong> · Envío ${{ number_format($activeZone->delivery_cost, 2) }} MXN.
                                    </p>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @else
        {{-- Fallback Hero si no hay campaña activa hoy --}}
        <section class="bg-gradient-to-br from-gepp-600 to-brand-600 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-7 space-y-4">
                    <h1 class="text-3xl sm:text-5xl font-extrabold">Postrecito de Jaan <span class="font-handwriting block text-pink-100 mt-1">hasta tu domicilio</span></h1>
                    <p class="text-white/90">Postres cremosos artesanales hechos por Janet. Explora el menú y programa tu entrega.</p>
                    <a href="{{ route('menu') }}" wire:navigate class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white text-gepp-700 font-bold text-sm shadow-lg">Ver Menú</a>
                </div>
                <div class="lg:col-span-5 bg-white text-slate-800 rounded-3xl p-6 shadow-2xl">
                    <h2 class="font-extrabold text-base mb-2">Validador de Cobertura</h2>
                    <form wire:submit="checkCoverage" class="flex gap-2">
                        <input type="text" wire:model="coverageQuery" placeholder="CP o Colonia (ej. 03100)" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm outline-none">
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-gepp-600 text-white font-bold text-sm cursor-pointer">Validar</button>
                    </form>
                    @if($coverageError) <p class="text-xs text-rose-600 mt-2">{{ $coverageError }}</p> @endif
                </div>
            </div>
        </section>
    @endif

    {{-- 3. Categorías --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-extrabold text-slate-900">Explora por Categoría</h2>
            <a href="{{ route('menu') }}" wire:navigate class="text-xs font-bold text-gepp-600 hover:underline">Ver todo el menú →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($categories as $cat)
                <a href="{{ route('menu') }}" wire:navigate
                    class="bg-white rounded-3xl p-5 border border-slate-200/80 hover:border-brand-300 hover:shadow-md transition-all text-center group">
                    <div class="w-12 h-12 mx-auto rounded-2xl {{ $cat->bg_color }} {{ $cat->text_color }} flex items-center justify-center text-xl mb-3 group-hover:scale-110 transition-transform">
                        <i class="{{ $cat->icon }}"></i>
                    </div>
                    <h3 class="font-bold text-sm text-slate-900">{{ $cat->name }}</h3>
                    <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">{{ $cat->description }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- 4. Favoritos de Janet --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <h2 class="text-xl font-extrabold text-slate-900 mb-6">Los Favoritos de Postrecito de Jaan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($favoriteProducts as $product)
                <div class="bg-white rounded-3xl border border-slate-200 p-5 flex flex-col justify-between shadow-2xs hover:shadow-lg transition-all">
                    <div>
                        <div class="h-32 rounded-2xl {{ $product->category?->bg_color ?? 'bg-pink-100' }} flex items-center justify-center mb-4 relative">
                            <i class="{{ $product->category?->icon ?? 'fa-solid fa-ice-cream' }} text-4xl {{ $product->category?->text_color ?? 'text-pink-600' }}"></i>
                            @if($product->badge)
                                <span class="absolute top-2.5 right-2.5 bg-white text-brand-600 text-[10px] font-bold px-2.5 py-0.5 rounded-full shadow-xs">{{ $product->badge }}</span>
                            @endif
                        </div>
                        <h3 class="font-bold text-slate-900">{{ $product->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $product->description }}</p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-sm font-extrabold text-slate-900">Desde ${{ number_format($product->base_price, 2) }}</span>
                        <a href="{{ route('menu') }}" wire:navigate class="px-3.5 py-1.5 rounded-full bg-brand-600 text-white text-xs font-bold hover:bg-brand-700">Elegir tamaño</a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>

