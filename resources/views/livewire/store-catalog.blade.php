<div>
    {{-- Top Utility Bar (Estilo Mi Mandadito: Cobertura + Acceso rápido) --}}
    <div class="bg-gepp-600 text-white text-xs py-2 px-4">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-truck-fast text-gepp-100"></i>
                <span class="font-medium">Entregas programadas a domicilio · Envío GRATIS desde $150 MXN</span>
            </div>
            <div class="flex items-center gap-4">
                <span class="hidden sm:inline text-gepp-100">
                    <i class="fa-solid fa-location-dot mr-1"></i>
                    Cobertura activa:
                </span>
                <select wire:model.live="selectedZone" aria-label="Zona de cobertura"
                    class="bg-gepp-700 text-white text-xs font-semibold rounded-full px-3 py-1 border border-gepp-500 focus:outline-none focus:ring-2 focus:ring-white/40">
                    <option value="Zona Centro / Roma / Condesa">Zona Centro / Roma / Condesa</option>
                    <option value="Zona Sur (Del Valle / Coyoacán / Nápoles)">Zona Sur (Del Valle / Coyoacán / Nápoles)</option>
                    <option value="Zona Poniente (Polanco / Anzures)">Zona Poniente (Polanco / Anzures)</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Main Navbar --}}
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 py-3 gap-4">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0 group">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white flex items-center justify-center shadow-md group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-ice-cream text-lg"></i>
                    </div>
                    <div class="leading-tight">
                        <span class="font-handwriting text-2xl sm:text-3xl font-bold text-brand-600 block -mb-1">Mi Postrecito</span>
                        <span class="text-[10px] uppercase tracking-widest text-slate-400 font-semibold">de Jaan · A domicilio</span>
                    </div>
                </a>

                {{-- Search Bar (Desktop) --}}
                <div class="hidden md:flex flex-1 max-w-md relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" wire:model.live.debounce.250ms="search"
                        placeholder="Busca sabores (Pay de Limón, Oreo, 1 Litro...)"
                        class="w-full pl-10 pr-4 py-2.5 rounded-full bg-slate-100 border border-transparent focus:bg-white focus:border-gepp-500 focus:ring-2 focus:ring-gepp-500/20 text-sm transition-all outline-none">
                    @if($search !== '')
                        <button wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 sm:gap-3">
                    <a href="#historia" class="hidden lg:inline-block text-sm font-medium text-slate-600 hover:text-brand-600 px-2">Historia</a>
                    <a href="#presentaciones" class="hidden lg:inline-block text-sm font-medium text-slate-600 hover:text-brand-600 px-2">Tamaños</a>

                    @auth
                        <a href="{{ route('orders.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-full text-xs sm:text-sm font-semibold text-gepp-700 bg-gepp-50 hover:bg-gepp-100 transition-colors">
                            <i class="fa-solid fa-box"></i>
                            <span class="hidden sm:inline">Mis Pedidos</span>
                        </a>
                        @if(auth()->user()->is_admin)
                            <a href="/admin" class="hidden sm:inline-flex items-center gap-1 px-3 py-2 rounded-full text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                                <i class="fa-solid fa-sliders"></i> Admin
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" title="Cerrar sesión"
                                class="inline-flex items-center gap-1 px-3 py-2 rounded-full text-xs sm:text-sm font-medium text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span class="hidden sm:inline">Salir</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs sm:text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors">
                            <i class="fa-regular fa-user"></i>
                            <span>Iniciar Sesión</span>
                        </a>
                    @endauth

                    {{-- Cart Button --}}
                    <button wire:click="openCartDrawer"
                        class="relative inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm shadow-md hover:shadow-lg transition-all cursor-pointer">
                        <i class="fa-solid fa-basket-shopping"></i>
                        <span class="hidden sm:inline">Mi Carrito</span>
                        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold bg-white text-brand-600 rounded-full">
                            {{ $this->cartCount }}
                        </span>
                    </button>
                </div>
            </div>

            {{-- Mobile Search --}}
            <div class="pb-3 md:hidden">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" wire:model.live.debounce.250ms="search"
                        placeholder="Buscar sabores o presentaciones..."
                        class="w-full pl-9 pr-4 py-2 rounded-full bg-slate-100 text-sm focus:bg-white focus:ring-2 focus:ring-gepp-500/20 outline-none">
                </div>
            </div>
        </div>
    </header>

    {{-- Feedback Toast --}}
    @if($feedbackMessage)
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3500)" x-show="show" x-transition
            class="fixed top-24 right-4 z-50 max-w-sm px-4 py-3 rounded-2xl shadow-xl border flex items-center gap-3 {{ $feedbackType === 'error' ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' }}">
            <i class="fa-solid {{ $feedbackType === 'error' ? 'fa-circle-exclamation text-rose-500' : 'fa-circle-check text-emerald-500' }}"></i>
            <span class="text-sm font-medium">{{ $feedbackMessage }}</span>
        </div>
    @endif

    {{-- Hero Banner (Estilo Mi Mandadito + Postrecito) --}}
    <section class="bg-gradient-to-br from-gepp-600 via-gepp-500 to-brand-600 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white blur-3xl"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 rounded-full bg-brand-200 blur-3xl"></div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16 relative z-10">
            <div class="grid lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-7 space-y-5">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur-sm border border-white/20 text-xs font-semibold">
                        <i class="fa-solid fa-sparkles text-amber-300"></i>
                        <span>Receta artesanal de Janet · Entregas Programadas</span>
                    </div>
                    <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                        Tus postres cremosos favoritos,
                        <span class="font-handwriting text-4xl sm:text-6xl text-brand-100 block mt-1">directo hasta la puerta de tu casa</span>
                    </h1>
                    <p class="text-base sm:text-lg text-blue-50 max-w-2xl">
                        Explora los 8 sabores artesanales de <strong>Postrecito de Jaan</strong> en presentaciones desde vasito individual hasta formato familiar de 1 litro y moldes de pay. Arma tu pedido sin registrarte y programa tu horario de entrega al confirmar.
                    </p>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <a href="#catalogo" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white text-gepp-700 font-bold text-sm shadow-lg hover:bg-blue-50 transition-all">
                            <i class="fa-solid fa-store"></i> Ver Catálogo Completo
                        </a>
                        <a href="#presentaciones" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white/15 hover:bg-white/25 border border-white/30 text-white font-semibold text-sm transition-all">
                            <i class="fa-solid fa-tags"></i> Ver Tamaños y Precios
                        </a>
                    </div>
                </div>
                <div class="lg:col-span-5">
                    <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-6 shadow-2xl">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-xs font-bold uppercase tracking-wider text-brand-100">¿Cómo funciona?</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-400/20 text-emerald-100 text-xs font-semibold">Fácil y Rápido</span>
                        </div>
                        <ul class="space-y-3.5 text-sm">
                            <li class="flex items-start gap-3">
                                <span class="w-7 h-7 rounded-full bg-white text-gepp-600 font-bold flex items-center justify-center shrink-0 text-xs">1</span>
                                <span><strong>Explora el catálogo</strong> libremente por sabor o presentación sin necesidad de cuenta.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-7 h-7 rounded-full bg-white text-gepp-600 font-bold flex items-center justify-center shrink-0 text-xs">2</span>
                                <span><strong>Arma tu pedido</strong> con el carrito o pídele recomendaciones y pedidos al <strong>Chatbot IA</strong>.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="w-7 h-7 rounded-full bg-white text-gepp-600 font-bold flex items-center justify-center shrink-0 text-xs">3</span>
                                <span><strong>Inicia sesión para confirmar</strong> tu fecha, franja horaria y dirección de entrega.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Catalog Section --}}
    <main id="catalogo" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Presentation Filter Pills (Estilo Mi Mandadito) --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-layer-group text-gepp-600"></i>
                    Filtrar por Presentación
                </h2>
                @if($selectedPresentationId || $selectedFlavorId || $search !== '')
                    <button wire:click="$set('selectedPresentationId', null); $set('selectedFlavorId', null); $set('search', '')"
                        class="text-xs font-semibold text-brand-600 hover:text-brand-700 cursor-pointer">
                        <i class="fa-solid fa-rotate-left mr-1"></i> Limpiar filtros
                    </button>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <button wire:click="filterByPresentation(null)"
                    class="px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all cursor-pointer {{ $selectedPresentationId === null ? 'bg-gepp-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:border-gepp-400' }}">
                    Todas las presentaciones
                </button>
                @foreach($presentations as $pres)
                    <button wire:click="filterByPresentation({{ $pres->id }})"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all cursor-pointer {{ $selectedPresentationId === $pres->id ? 'bg-gepp-600 text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:border-gepp-400' }}">
                        <i class="{{ $pres->icon }} text-xs"></i>
                        <span>{{ $pres->name }} ({{ $pres->capacity }})</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Flavor Filter Pills --}}
        <div class="mb-10">
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Sabores de Postrecito de Jaan</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($flavors as $flavor)
                    <button wire:click="filterByFlavor({{ $flavor->id }})"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl text-xs sm:text-sm font-semibold transition-all cursor-pointer border {{ $selectedFlavorId === $flavor->id ? 'bg-brand-600 text-white border-brand-600 shadow-sm' : 'bg-white text-slate-700 border-slate-200 hover:border-brand-300' }}">
                        <i class="{{ $flavor->icon }}"></i>
                        <span>{{ $flavor->name }}</span>
                        @if($flavor->is_favorite)
                            <i class="fa-solid fa-star text-amber-400 text-[10px]"></i>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Products Grid --}}
        @if($products->isEmpty())
            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-ice-cream text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">No encontramos postrecitos con ese filtro</h3>
                <p class="text-sm text-slate-500 mt-1">Prueba limpiando tu búsqueda o seleccionando otra presentación.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($products as $product)
                    @php
                        $inCartQty = $cart[$product->id]['quantity'] ?? 0;
                        $flavor = $product->flavor;
                        $presentation = $product->presentation;
                    @endphp
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden group">
                        <div>
                            {{-- Product Visual Header --}}
                            <div class="h-40 {{ $flavor?->bg_color ?? 'bg-brand-50' }} flex items-center justify-center relative p-4">
                                <i class="{{ $flavor?->icon ?? 'fa-solid fa-ice-cream' }} text-5xl {{ $flavor?->text_color ?? 'text-brand-600' }} group-hover:scale-110 transition-transform duration-300"></i>

                                @if($product->badge)
                                    <span class="absolute top-3 left-3 bg-white/95 text-brand-600 text-[11px] font-bold px-2.5 py-1 rounded-full shadow-sm">
                                        {{ $product->badge }}
                                    </span>
                                @endif

                                @if($product->is_returnable_container)
                                    <span class="absolute top-3 right-3 bg-gepp-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-sm" title="Envase retornable estilo Mi Mandadito">
                                        <i class="fa-solid fa-recycle mr-1"></i> Retornable
                                    </span>
                                @endif

                                @if($presentation)
                                    <span class="absolute bottom-3 right-3 bg-slate-900/75 text-white text-[11px] font-semibold px-2.5 py-0.5 rounded-full">
                                        {{ $presentation->capacity }}
                                    </span>
                                @endif
                            </div>

                            {{-- Content --}}
                            <div class="p-5">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-gepp-600 mb-1">
                                    {{ $flavor?->name }} · {{ $presentation?->name }}
                                </div>
                                <h3 class="font-bold text-slate-900 text-base leading-snug mb-2">
                                    {{ $product->name }}
                                </h3>
                                <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                    {{ $product->description }}
                                </p>
                            </div>
                        </div>

                        {{-- Price & Add to Cart Controls (Estilo Mi Mandadito) --}}
                        <div class="px-5 pb-5 pt-2 border-t border-slate-100 flex items-center justify-between gap-3">
                            <div>
                                @if($product->is_promo && $product->original_price)
                                    <span class="text-xs text-slate-400 line-through block">${{ number_format($product->original_price, 2) }}</span>
                                @endif
                                <span class="text-xl font-extrabold text-slate-900">${{ number_format($product->price, 2) }}</span>
                                <span class="text-[10px] text-slate-400 font-semibold">MXN</span>
                            </div>

                            @if($inCartQty > 0)
                                <div class="inline-flex items-center bg-gepp-50 border border-gepp-200 rounded-full p-1">
                                    <button wire:click="updateQuantity({{ $product->id }}, -1)"
                                        class="w-7 h-7 rounded-full bg-white text-gepp-700 hover:bg-gepp-600 hover:text-white font-bold text-sm flex items-center justify-center shadow-xs transition-colors cursor-pointer">
                                        -
                                    </button>
                                    <span class="px-3 text-sm font-bold text-gepp-700">{{ $inCartQty }}</span>
                                    <button wire:click="updateQuantity({{ $product->id }}, 1)"
                                        class="w-7 h-7 rounded-full bg-gepp-600 text-white hover:bg-gepp-700 font-bold text-sm flex items-center justify-center shadow-xs transition-colors cursor-pointer">
                                        +
                                    </button>
                                </div>
                            @else
                                <button wire:click="addToCart({{ $product->id }})"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold shadow-sm hover:shadow transition-all cursor-pointer">
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Agregar</span>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    {{-- Presentations Highlight Section --}}
    <section id="presentaciones" class="py-16 bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-widest text-brand-600">Para cualquier antojo</span>
                <h2 class="text-3xl font-extrabold text-slate-900 mt-1">Nuestras Presentaciones</h2>
                <p class="text-slate-500 mt-2">Desde un gustito personal hasta moldes completos para tus eventos.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                @foreach($presentations as $pres)
                    <div class="rounded-3xl p-6 border {{ $pres->badge === 'Para eventos' ? 'border-brand-200 bg-brand-50/50' : 'border-slate-200 bg-slate-50/50' }} flex flex-col justify-between text-center hover:shadow-md transition-all">
                        <div>
                            @if($pres->badge)
                                <span class="inline-block px-3 py-1 rounded-full bg-brand-100 text-brand-700 text-[11px] font-bold mb-3">{{ $pres->badge }}</span>
                            @endif
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-white shadow-xs flex items-center justify-center text-gepp-600 text-2xl mb-4">
                                <i class="{{ $pres->icon }}"></i>
                            </div>
                            <h3 class="font-bold text-slate-900 text-lg">{{ $pres->name }}</h3>
                            <p class="text-xs font-semibold text-gepp-600 mb-2">{{ $pres->capacity }}</p>
                            <p class="text-xl font-extrabold text-slate-900 mb-3">${{ number_format($pres->base_price, 2) }} <span class="text-xs font-normal text-slate-500">MXN</span></p>
                            <p class="text-xs text-slate-500 leading-relaxed mb-4">{{ $pres->description }}</p>
                        </div>
                        <button wire:click="filterByPresentation({{ $pres->id }})" onclick="document.getElementById('catalogo').scrollIntoView({behavior:'smooth'})"
                            class="w-full py-2 rounded-full border border-gepp-600 text-gepp-600 hover:bg-gepp-600 hover:text-white text-xs font-bold transition-colors cursor-pointer">
                            Ver sabores en este tamaño
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Nuestra Historia (Postrecito de Jaan) --}}
    <section id="historia" class="py-16 bg-cream border-t border-amber-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="bg-white rounded-3xl p-8 shadow-lg border border-brand-100 relative">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-14 h-14 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-2xl">
                            <i class="fa-solid fa-heart"></i>
                        </div>
                        <div>
                            <h3 class="font-handwriting text-3xl font-bold text-brand-600">La Receta de Janet</h3>
                            <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">100% Artesanal · Hecho en casa</p>
                        </div>
                    </div>
                    <p class="text-slate-600 leading-relaxed mb-4">
                        Hace más de un año, Janet sintió curiosidad por el mundo de los postres. A base de prueba y error, cambiando medidas y perfeccionando ingredientes, logró crear su propia fórmula de crema base: el corazón de <strong>Postrecito de Jaan</strong>.
                    </p>
                    <blockquote class="border-l-4 border-brand-500 pl-4 py-2 bg-brand-50 rounded-r-xl text-slate-800 font-medium text-sm">
                        "No es solo un postre, es el resultado de pasión y dedicación para que cada bocado sea una sonrisa."
                    </blockquote>
                </div>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                        <span class="text-3xl sm:text-4xl font-extrabold text-brand-600 block">1+</span>
                        <span class="text-xs font-semibold text-slate-500 mt-1 block">Año de experiencia</span>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                        <span class="text-3xl sm:text-4xl font-extrabold text-gepp-600 block">8</span>
                        <span class="text-xs font-semibold text-slate-500 mt-1 block">Sabores únicos</span>
                    </div>
                    <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100">
                        <span class="text-3xl sm:text-4xl font-extrabold text-emerald-600 block">100%</span>
                        <span class="text-xs font-semibold text-slate-500 mt-1 block">Artesanal</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-slate-900 text-slate-300 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-ice-cream text-brand-500 text-xl"></i>
                <span class="font-handwriting text-2xl font-bold text-white">Postrecito de Jaan</span>
            </div>
            <p class="text-xs text-slate-400">
                &copy; {{ date('Y') }} Mi Postrecito · Entregas programadas estilo Mi Mandadito.
            </p>
        </div>
    </footer>

    {{-- Slide-over Cart Drawer --}}
    @if($isCartOpen)
        <div class="fixed inset-0 z-50 overflow-hidden">
            <div wire:click="closeCartDrawer" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"></div>
            <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-2xl flex flex-col justify-between z-10">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center">
                            <i class="fa-solid fa-basket-shopping"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900">Tu Mandadito Dulce</h3>
                            <p class="text-xs text-slate-500">{{ $this->cartCount }} {{ $this->cartCount === 1 ? 'producto' : 'productos' }}</p>
                        </div>
                    </div>
                    <button wire:click="closeCartDrawer" class="text-slate-400 hover:text-slate-600 p-2 cursor-pointer">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6 space-y-4">
                    @forelse($cart as $item)
                        <div class="flex items-center justify-between gap-3 p-3.5 rounded-2xl border border-slate-100 bg-slate-50/70">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center shrink-0">
                                    <i class="{{ $item['icon'] ?? 'fa-solid fa-ice-cream' }}"></i>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-sm text-slate-900 truncate">{{ $item['name'] }}</h4>
                                    <p class="text-xs text-slate-500">${{ number_format($item['price'], 2) }} c/u</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <div class="inline-flex items-center bg-white border border-slate-200 rounded-full p-0.5">
                                    <button wire:click="updateQuantity({{ $item['id'] }}, -1)" class="w-6 h-6 rounded-full text-slate-600 hover:bg-slate-100 text-xs font-bold cursor-pointer">-</button>
                                    <span class="px-2 text-xs font-bold">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateQuantity({{ $item['id'] }}, 1)" class="w-6 h-6 rounded-full text-slate-600 hover:bg-slate-100 text-xs font-bold cursor-pointer">+</button>
                                </div>
                                <button wire:click="removeFromCart({{ $item['id'] }})" class="text-slate-400 hover:text-rose-500 p-1 cursor-pointer">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="fa-solid fa-basket-shopping text-4xl text-slate-200 mb-3"></i>
                            <p class="text-sm font-medium text-slate-500">Tu carrito está vacío.</p>
                            <p class="text-xs text-slate-400 mt-1">Agrega tus sabores favoritos del catálogo.</p>
                        </div>
                    @endforelse
                </div>

                @if(!empty($cart))
                    <div class="p-6 border-t border-slate-100 bg-slate-50 space-y-4">
                        <div class="space-y-1.5 text-sm">
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span>${{ number_format($this->subtotal, 2) }} MXN</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Envío ({{ $selectedZone }})</span>
                                @if($this->deliveryFee == 0)
                                    <span class="text-emerald-600 font-bold">GRATIS</span>
                                @else
                                    <span>${{ number_format($this->deliveryFee, 2) }} MXN</span>
                                @endif
                            </div>
                            @if($this->subtotal < 150)
                                <p class="text-[11px] text-gepp-600 font-medium">
                                    Te faltan ${{ number_format(150 - $this->subtotal, 2) }} MXN para obtener envío gratis.
                                </p>
                            @endif
                            <div class="flex justify-between text-base font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                                <span>Total</span>
                                <span>${{ number_format($this->total, 2) }} MXN</span>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button wire:click="clearCart" class="px-4 py-3 rounded-full border border-slate-300 text-slate-600 text-xs font-bold hover:bg-slate-100 cursor-pointer">
                                Vaciar
                            </button>
                            <button wire:click="proceedToCheckout"
                                class="flex-1 py-3 px-6 rounded-full bg-gepp-600 hover:bg-gepp-700 text-white text-sm font-bold shadow-lg transition-all cursor-pointer flex items-center justify-center gap-2">
                                @auth
                                    <span>Programar Entrega</span>
                                    <i class="fa-solid fa-arrow-right"></i>
                                @else
                                    <i class="fa-solid fa-lock"></i>
                                    <span>Iniciar Sesión para Pedir</span>
                                @endauth
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Checkout Modal (Solo usuarios autenticados) --}}
    @if($isCheckoutOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl relative">
                <button wire:click="$set('isCheckoutOpen', false)" class="absolute top-5 right-5 text-slate-400 hover:text-slate-600 cursor-pointer">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>

                <div class="mb-6">
                    <span class="text-xs font-bold uppercase tracking-wider text-gepp-600">Entrega Programada</span>
                    <h3 class="text-2xl font-extrabold text-slate-900">Confirma tu Pedido</h3>
                </div>

                <form wire:submit="submitOrder" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre quien recibe *</label>
                            <input type="text" wire:model="customerName" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                            @error('customerName') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Teléfono / WhatsApp *</label>
                            <input type="tel" wire:model="customerPhone" required placeholder="+52 55 1234 5678" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                            @error('customerPhone') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Zona de Cobertura</label>
                        <select wire:model="selectedZone" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                            <option value="Zona Centro / Roma / Condesa">Zona Centro / Roma / Condesa</option>
                            <option value="Zona Sur (Del Valle / Coyoacán / Nápoles)">Zona Sur (Del Valle / Coyoacán / Nápoles)</option>
                            <option value="Zona Poniente (Polanco / Anzures)">Zona Poniente (Polanco / Anzures)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Dirección completa *</label>
                        <input type="text" wire:model="deliveryAddress" required placeholder="Calle, número exterior/interior, colonia..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                        @error('deliveryAddress') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Fecha de Entrega *</label>
                            <input type="date" wire:model="scheduledDate" required min="{{ now()->toDateString() }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Horario *</label>
                            <select wire:model="scheduledSlot" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                                <option value="10:00 - 13:00">10:00 - 13:00 hrs</option>
                                <option value="13:00 - 16:00">13:00 - 16:00 hrs</option>
                                <option value="16:00 - 19:00">16:00 - 19:00 hrs</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Método de Pago</label>
                        <select wire:model="paymentMethod" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                            <option value="efectivo">Efectivo contra entrega</option>
                            <option value="transferencia">Transferencia SPEI</option>
                            <option value="tarjeta_entrega">Tarjeta al recibir (Terminal móvil)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Notas de entrega</label>
                        <textarea wire:model="orderNotes" rows="2" placeholder="Ej: Tocar timbre, sin nuez..." class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none"></textarea>
                    </div>

                    <div class="bg-slate-50 rounded-2xl p-4 flex items-center justify-between border border-slate-200">
                        <span class="text-sm font-bold text-slate-700">Total a pagar:</span>
                        <span class="text-xl font-extrabold text-brand-600">${{ number_format($this->total, 2) }} MXN</span>
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full py-3.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold shadow-lg transition-all cursor-pointer flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check-circle"></i>
                        <span>Confirmar Pedido</span>
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- Chatbot RAG Flotante --}}
    @livewire('rag-chatbot')
</div>
