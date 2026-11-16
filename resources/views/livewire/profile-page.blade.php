<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
    <div class="bg-white rounded-3xl border border-slate-200 p-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-2xl font-bold">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900">{{ $user->name }}</h1>
                <p class="text-xs text-slate-500">{{ $user->email }} · {{ $user->phone_number }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="px-4 py-2 rounded-full bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 text-xs font-bold cursor-pointer">
                Cerrar sesión
            </button>
        </form>
    </div>

    @if($profileMessage)
        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold">
            {{ $profileMessage }}
        </div>
    @endif

    {{-- Libreta de Direcciones --}}
    <section class="bg-white rounded-3xl border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-slate-900">Libreta de Direcciones</h2>
            <button wire:click="$toggle('showAddressForm')" class="px-4 py-2 rounded-full bg-gepp-600 text-white text-xs font-bold cursor-pointer">
                {{ $showAddressForm ? 'Cancelar' : '+ Nueva Dirección' }}
            </button>
        </div>

        @if($showAddressForm)
            <form wire:submit="saveAddress" class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="grid sm:grid-cols-3 gap-3">
                    <input type="text" wire:model="tag" placeholder="Etiqueta (Casa, Oficina)" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white">
                    <input type="text" wire:model="street" placeholder="Calle *" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white sm:col-span-2">
                </div>
                <div class="grid sm:grid-cols-4 gap-3">
                    <input type="text" wire:model="exterior_number" placeholder="No. Ext *" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white">
                    <input type="text" wire:model="interior_number" placeholder="No. Int" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white">
                    <input type="text" wire:model="neighborhood" placeholder="Colonia *" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white">
                    <input type="text" wire:model="zip_code" placeholder="CP *" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white">
                </div>
                <input type="text" wire:model="references" placeholder="Referencias de entrega" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white">
                <button type="submit" class="px-5 py-2 rounded-full bg-brand-600 text-white text-xs font-bold cursor-pointer">Guardar Dirección</button>
            </form>
        @endif

        <div class="grid sm:grid-cols-2 gap-4">
            @foreach($addresses as $addr)
                <div class="p-4 rounded-2xl border {{ $addr->is_default ? 'border-gepp-500 bg-gepp-50/40' : 'border-slate-200' }} flex flex-col justify-between gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-sm text-slate-900">{{ $addr->tag }}</span>
                            @if($addr->is_default)
                                <span class="text-[10px] font-bold bg-gepp-600 text-white px-2 py-0.5 rounded-full">Predeterminada</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-600">{{ $addr->fullAddress() }}</p>
                        @if($addr->references)
                            <p class="text-[11px] text-slate-400 mt-1">{{ $addr->references }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        @if(! $addr->is_default)
                            <button wire:click="setDefaultAddress({{ $addr->id }})" class="font-bold text-gepp-600 hover:underline cursor-pointer">Usar por defecto</button>
                        @endif
                        <button wire:click="deleteAddress({{ $addr->id }})" class="font-bold text-rose-500 hover:underline cursor-pointer">Eliminar</button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Pedidos Recientes --}}
    <section class="bg-white rounded-3xl border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-extrabold text-slate-900">Pedidos Recientes</h2>
            <a href="{{ route('orders.index') }}" wire:navigate class="text-xs font-bold text-gepp-600 hover:underline">Ver historial completo →</a>
        </div>
        <div class="space-y-3">
            @forelse($recentOrders as $order)
                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-sm">
                    <div>
                        <span class="font-bold">#{{ $order->order_number }}</span>
                        <span class="text-xs text-slate-500 ml-2">{{ $order->delivery_date->format('d/m/Y') }}</span>
                    </div>
                    <span class="font-extrabold text-slate-900">${{ number_format($order->total_amount, 2) }} MXN</span>
                </div>
            @empty
                <p class="text-xs text-slate-500">Aún no tienes pedidos registrados.</p>
            @endforelse
        </div>
    </section>
</div>

