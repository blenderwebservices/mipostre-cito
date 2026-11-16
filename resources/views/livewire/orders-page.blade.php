<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Mis Pedidos</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">Historial de entregas y recompra en 1 clic.</p>
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
                    Entrega programada para el <strong>{{ $s['date'] }}</strong> ({{ $s['slot'] }}). Total: <strong>${{ number_format($s['total'], 2) }} MXN</strong>.
                </p>
            </div>
        </div>
    @endif

    @if($orders->isEmpty())
        <div class="bg-white rounded-3xl border border-slate-200 p-12 text-center">
            <i class="fa-solid fa-box-open text-4xl text-slate-300 mb-3"></i>
            <h3 class="text-lg font-bold text-slate-800">Aún no tienes pedidos</h3>
            <a href="{{ route('menu') }}" wire:navigate class="mt-4 inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-brand-600 text-white text-sm font-bold">Ir al Menú</a>
        </div>
    @else
        <div class="space-y-5">
            @foreach($orders as $order)
                <div class="bg-white rounded-3xl border border-slate-200 shadow-2xs overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="font-extrabold text-slate-900">#{{ $order->order_number }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase bg-gepp-100 text-gepp-700">
                                {{ \App\Models\Order::statusLabel($order->status) }}
                            </span>
                            @if($order->source === 'chatbot')
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-brand-100 text-brand-700">
                                    <i class="fa-solid fa-robot mr-1"></i>Chatbot RAG
                                </span>
                            @endif
                        </div>
                        <button wire:click="reorder({{ $order->id }})"
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-brand-600 hover:bg-brand-700 text-white text-xs font-bold cursor-pointer">
                            <i class="fa-solid fa-rotate-right"></i> Volver a pedir
                        </button>
                    </div>
                    <div class="p-6 grid md:grid-cols-3 gap-6">
                        <div class="md:col-span-2 space-y-2">
                            @foreach($order->items as $item)
                                <div class="flex justify-between text-sm py-1 border-b border-slate-100 last:border-0">
                                    <span><strong>{{ $item->quantity }}x</strong> {{ $item->product_name_snapshot }} <span class="text-xs text-slate-500">({{ $item->variation_name_snapshot }})</span></span>
                                    <span class="font-semibold">${{ number_format($item->subtotal, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-4 space-y-1.5 text-xs">
                            <p><strong>Entrega:</strong> {{ $order->delivery_date->format('d/m/Y') }} ({{ $order->delivery_time_slot }})</p>
                            <p><strong>Dirección:</strong> {{ $order->delivery_address_snapshot }}</p>
                            @if($order->discount_amount > 0)
                                <p class="text-emerald-700"><strong>Descuento:</strong> -${{ number_format($order->discount_amount, 2) }}</p>
                            @endif
                            <p><strong>Envío:</strong> ${{ number_format($order->shipping_cost, 2) }}</p>
                            <p class="text-sm font-extrabold text-slate-900 pt-1 border-t border-slate-200">Total: ${{ number_format($order->total_amount, 2) }} MXN</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

