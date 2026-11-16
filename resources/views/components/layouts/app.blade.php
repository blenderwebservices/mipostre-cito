<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#0072b2">
    <title>{{ $title ?? 'Postrecito de Jaan | Entregas a Domicilio' }}</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fdf2f8',
                            100: '#fce7f3',
                            200: '#fbcfe8',
                            500: '#ec4899',
                            600: '#db2777',
                            700: '#be185d',
                        },
                        gepp: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#0284c7',
                            600: '#0072b2',
                            700: '#0369a1',
                        },
                        cream: '#fffdf5',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        handwriting: ['Caveat', 'cursive'],
                    }
                }
            }
        }
    </script>

    <style>
        body { background-color: #f8fafc; font-family: 'Plus Jakarta Sans', sans-serif; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    @livewireStyles
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col justify-between pb-20 md:pb-0 selection:bg-brand-500 selection:text-white">

    {{-- Top Navigation Bar (Desktop & Mobile Brand Header) --}}
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" wire:navigate class="flex items-center gap-2.5 shrink-0 group">
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-ice-cream text-base"></i>
                </div>
                <div class="leading-tight">
                    <span class="font-handwriting text-2xl sm:text-3xl font-bold text-brand-600 block -mb-1">Postrecito de Jaan</span>
                    <span class="text-[10px] uppercase tracking-widest text-slate-400 font-semibold">Estilo Mi Mandadito</span>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-1">
                <a href="{{ route('home') }}" wire:navigate
                    class="px-3.5 py-2 rounded-full text-sm font-semibold transition-colors {{ request()->routeIs('home') ? 'bg-gepp-50 text-gepp-700' : 'text-slate-600 hover:text-gepp-600' }}">
                    <i class="fa-solid fa-house mr-1.5 text-xs"></i>Inicio
                </a>
                <a href="{{ route('menu') }}" wire:navigate
                    class="px-3.5 py-2 rounded-full text-sm font-semibold transition-colors {{ request()->routeIs('menu') ? 'bg-gepp-50 text-gepp-700' : 'text-slate-600 hover:text-gepp-600' }}">
                    <i class="fa-solid fa-store mr-1.5 text-xs"></i>Menú
                </a>
                <a href="{{ route('promos') }}" wire:navigate
                    class="px-3.5 py-2 rounded-full text-sm font-semibold transition-colors {{ request()->routeIs('promos') ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:text-brand-600' }}">
                    <i class="fa-solid fa-ticket mr-1.5 text-xs"></i>Promos
                </a>
                @auth
                    <a href="{{ route('orders.index') }}" wire:navigate
                        class="px-3.5 py-2 rounded-full text-sm font-semibold transition-colors {{ request()->routeIs('orders.index') ? 'bg-gepp-50 text-gepp-700' : 'text-slate-600 hover:text-gepp-600' }}">
                        <i class="fa-solid fa-box mr-1.5 text-xs"></i>Mis Pedidos
                    </a>
                    <a href="{{ route('profile') }}" wire:navigate
                        class="px-3.5 py-2 rounded-full text-sm font-semibold transition-colors {{ request()->routeIs('profile') ? 'bg-gepp-50 text-gepp-700' : 'text-slate-600 hover:text-gepp-600' }}">
                        <i class="fa-regular fa-user mr-1.5 text-xs"></i>Perfil
                    </a>
                @endauth
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2">
                @auth
                    @if(auth()->user()->is_admin)
                        <a href="/admin" class="hidden sm:inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200">
                            <i class="fa-solid fa-sliders"></i> Admin
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" wire:navigate
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs sm:text-sm font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700">
                        <i class="fa-regular fa-user"></i>
                        <span>Entrar</span>
                    </a>
                @endauth

                @php
                    $navCartCount = array_sum(array_column(session('postrecito_cart', []), 'quantity'));
                @endphp
                <a href="{{ route('cart') }}" wire:navigate
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-bold shadow-sm transition-all">
                    <i class="fa-solid fa-basket-shopping"></i>
                    <span class="hidden sm:inline">Carrito</span>
                    <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-white text-brand-600 text-xs font-extrabold">
                        {{ $navCartCount }}
                    </span>
                </a>
            </div>
        </div>
    </header>

    {{-- Page Content --}}
    <div class="flex-1">
        {{ $slot }}
    </div>

    {{-- Desktop Footer --}}
    <footer class="hidden md:block bg-slate-900 text-slate-300 py-8 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-ice-cream text-brand-500 text-lg"></i>
                <span class="font-handwriting text-2xl font-bold text-white">Postrecito de Jaan</span>
            </div>
            <p class="text-slate-400">&copy; {{ date('Y') }} Postrecito de Jaan · Entregas programadas a domicilio.</p>
        </div>
    </footer>

    {{-- Mobile Bottom Navigation Bar (Fija en móviles - Especificación 3.1) --}}
    <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white border-t border-slate-200 shadow-[0_-4px_20px_rgba(0,0,0,0.06)]">
        <div class="grid grid-cols-5 h-16">
            <a href="{{ route('home') }}" wire:navigate
                class="flex flex-col items-center justify-center gap-1 text-[11px] font-semibold {{ request()->routeIs('home') ? 'text-brand-600' : 'text-slate-500' }}">
                <i class="fa-solid fa-house text-base"></i>
                <span>Inicio</span>
            </a>
            <a href="{{ route('menu') }}" wire:navigate
                class="flex flex-col items-center justify-center gap-1 text-[11px] font-semibold {{ request()->routeIs('menu') ? 'text-brand-600' : 'text-slate-500' }}">
                <i class="fa-solid fa-store text-base"></i>
                <span>Menú</span>
            </a>
            <a href="{{ route('promos') }}" wire:navigate
                class="flex flex-col items-center justify-center gap-1 text-[11px] font-semibold {{ request()->routeIs('promos') ? 'text-brand-600' : 'text-slate-500' }}">
                <i class="fa-solid fa-ticket text-base"></i>
                <span>Promos</span>
            </a>
            <a href="{{ route('orders.index') }}" wire:navigate
                class="flex flex-col items-center justify-center gap-1 text-[11px] font-semibold {{ request()->routeIs('orders.index') ? 'text-brand-600' : 'text-slate-500' }}">
                <i class="fa-solid fa-box text-base"></i>
                <span>Pedidos</span>
            </a>
            <a href="{{ auth()->check() ? route('profile') : route('login') }}" wire:navigate
                class="flex flex-col items-center justify-center gap-1 text-[11px] font-semibold {{ request()->routeIs('profile') || request()->routeIs('login') ? 'text-brand-600' : 'text-slate-500' }}">
                <i class="fa-regular fa-user text-base"></i>
                <span>{{ auth()->check() ? 'Perfil' : 'Entrar' }}</span>
            </a>
        </div>
    </nav>

    {{-- Persistent RAG Chatbot --}}
    @livewire('rag-chatbot')

    @livewireScripts
</body>
</html>
