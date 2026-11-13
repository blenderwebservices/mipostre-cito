<x-layouts.app title="Mis Pedidos | Mi Postrecito">
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2">
                <i class="fa-solid fa-ice-cream text-brand-600 text-xl"></i>
                <span class="font-handwriting text-3xl font-bold text-brand-600">Mi Postrecito</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-sm font-semibold text-gepp-600 hover:underline">
                    ← Seguir comprando
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 rounded-full bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 text-xs font-bold cursor-pointer">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-900">Mis Pedidos Programados</h1>
            <p class="text-sm text-slate-500 mt-1">Historial de entregas de Postrecito de Jaan para {{ auth()->user()->name }}</p>
        </div>

        @if(session('order_success'))
            @php $s = session('order_success'); @endphp
            <div class="mb-8 p-5 rounded-3xl bg-emerald-50 border border-emerald-200 flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-check"></i>
                </div>
                <div>
                    <h3 class="font-bold text-emerald-900">¡Pedido #{{ $s['number'] }} confirmado!</h3>
                    <p class="text-sm text-emerald-800 mt-0.5">
                        Tu entrega está programada para el <strong>{{ $s['date'] }}</strong> en horario de <strong>{{ $s['slot'] }}</strong>. Total: <strong>${{ number_format($s['total'], 2) }} MXN</strong>.
                    </p>
                </div>
            </div>
        @endif

        @if($orders->isEmpty())
            <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center">
                <i class="fa-solid fa-box-open text-4xl text-slate-300 mb-3"></i>
                <h3 class="text-lg font-bold text-slate-800">Todavía no has realizado pedidos</h3>
                <p class="text-sm text-slate-500 mt-1 mb-5">Explora nuestros 8 sabores artesanales y programa tu primera entrega.</p>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-brand-600 text-white text-sm font-bold hover:bg-brand-700">
                    Ir al Catálogo
                </a>
            </div>
        @else
            <div class="space-y-6">
                @foreach($orders as $order)
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
                        <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="font-extrabold text-slate-900">#{{ $order->order_number }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase {{ $order->status === 'entregado' ? 'bg-emerald-100 text-emerald-700' : ($order->status === 'cancelado' ? 'bg-rose-100 text-rose-700' : 'bg-gepp-100 text-gepp-700') }}">
                                    {{ str_replace('_', ' ', $order->status) }}
                                </span>
                                @if($order->source === 'chatbot')
                                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-brand-100 text-brand-700">
                                        <i class="fa-solid fa-robot mr-1"></i> Vía Chatbot RAG
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-500">
                                Entrega: <strong>{{ $order->scheduled_date->format('d/m/Y') }}</strong> ({{ $order->scheduled_slot }})
                            </div>
                        </div>

                        <div class="p-6 grid md:grid-cols-3 gap-6">
                            <div class="md:col-span-2 space-y-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Productos</h4>
                                @foreach($order->items as $item)
                                    <div class="flex items-center justify-between text-sm py-1 border-b border-slate-100 last:border-0">
                                        <span><strong>{{ $item->quantity }}x</strong> {{ $item->product_name }}</span>
                                        <span class="font-semibold text-slate-700">${{ number_format($item->subtotal, 2) }} MXN</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="bg-slate-50 rounded-2xl p-4 space-y-2 text-xs">
                                <p><strong>Dirección:</strong> {{ $order->delivery_address }}</p>
                                <p><strong>Zona:</strong> {{ $order->delivery_zone }}</p>
                                <p><strong>Pago:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</p>
                                <div class="pt-2 border-t border-slate-200 flex justify-between text-sm font-extrabold text-slate-900">
                                    <span>Total</span>
                                    <span>${{ number_format($order->total, 2) }} MXN</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>
</x-layouts.app>

