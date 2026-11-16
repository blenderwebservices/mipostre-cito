<div class="fixed bottom-20 md:bottom-5 right-4 md:right-5 z-50">
    {{-- Floating Trigger Button --}}
    @if(! $isOpen)
        <button wire:click="toggleChat"
            class="group flex items-center gap-3 px-5 py-3.5 rounded-full bg-gradient-to-r from-gepp-600 to-brand-600 text-white shadow-2xl hover:shadow-brand-500/30 hover:scale-105 transition-all cursor-pointer">
            <div class="relative">
                <i class="fa-solid fa-robot text-xl"></i>
                <span class="absolute -top-1 -right-1 w-2.5 h-2.5 {{ $isLiveAi ? 'bg-emerald-400' : 'bg-amber-400' }} rounded-full ring-2 ring-white animate-pulse"
                    title="{{ $isLiveAi ? 'IA Activa (Gemini)' : 'Modo Mocking (Local)' }}"></span>
            </div>
            <div class="text-left hidden sm:block">
                <span class="block text-xs font-bold leading-none">Postrecito Bot</span>
                <span class="block text-[10px] text-white/85">{{ $isLiveAi ? 'IA Activa (Gemini)' : 'Modo Mocking' }}</span>
            </div>
        </button>
    @endif

    {{-- Chat Window --}}
    @if($isOpen)
        <div class="w-[calc(100vw-2.5rem)] sm:w-96 h-[34rem] max-h-[82vh] bg-white rounded-3xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-gepp-600 to-brand-600 text-white px-5 py-4 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-robot text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-sm leading-tight">Postrecito Bot (RAG)</h3>
                            @if($isLiveAi)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-400/20 text-emerald-100 border border-emerald-300/40">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                                    IA Activa
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400/25 text-amber-100 border border-amber-300/40" title="Sin GEMINI_API_KEY en .env, usando motor RAG local">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-300"></span>
                                    Modo Mocking
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-white/80">
                            {{ $isLiveAi ? 'Conectado a Gemini Embeddings + Retrieval' : 'Motor local RAG (configura GEMINI_API_KEY en .env)' }}
                        </p>
                    </div>
                </div>
                <button wire:click="toggleChat" class="text-white/80 hover:text-white p-1.5 cursor-pointer">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Messages Container --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50" id="rag-chat-messages">
                @foreach($messages as $msg)
                    @php $isUser = $msg['role'] === 'user'; @endphp
                    <div class="flex {{ $isUser ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-2xl px-4 py-2.5 text-xs sm:text-sm leading-relaxed shadow-xs {{ $isUser ? 'bg-gepp-600 text-white rounded-br-none' : 'bg-white text-slate-700 border border-slate-200 rounded-bl-none' }}">
                            <div class="chat-markdown space-y-1.5 [&_p]:leading-relaxed [&_p:last-child]:mb-0 [&_ul]:list-disc [&_ul]:pl-4 [&_ul]:space-y-0.5 [&_ol]:list-decimal [&_ol]:pl-4 [&_ol]:space-y-0.5 [&_strong]:font-bold [&_em]:italic [&_h1]:font-extrabold [&_h1]:text-sm [&_h2]:font-bold [&_h2]:text-sm [&_h3]:font-bold [&_code]:px-1 [&_code]:py-0.5 [&_code]:rounded [&_code]:text-[11px] {{ $isUser ? '[&_code]:bg-white/20 [&_a]:underline' : '[&_code]:bg-slate-100 [&_code]:text-brand-700 [&_a]:text-gepp-600 [&_a]:underline' }}">
                                {!! \Illuminate\Support\Str::markdown($msg['content'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                            </div>

                            @if(!empty($msg['is_order']) && !empty($msg['order_number']))
                                <div class="mt-2 pt-2 border-t border-emerald-100 flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                        <i class="fa-solid fa-circle-check"></i> Folio #{{ $msg['order_number'] }}
                                    </span>
                                    <a href="{{ route('orders.index') }}" class="text-[11px] font-bold text-gepp-600 hover:underline">
                                        Ver mis pedidos →
                                    </a>
                                </div>
                            @endif

                            @if(!empty($msg['requires_auth']))
                                <div class="mt-2.5 pt-2 border-t border-slate-100">
                                    <a href="{{ route('login') }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-brand-600 text-white text-xs font-bold hover:bg-brand-700 transition-colors">
                                        <i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesión para Pedir
                                    </a>
                                </div>
                            @endif

                            <span class="block text-[10px] mt-1 {{ $isUser ? 'text-blue-100 text-right' : 'text-slate-400' }}">
                                {{ $msg['time'] }}
                            </span>
                        </div>
                    </div>
                @endforeach

                <div wire:loading wire:target="sendMessage, askQuickQuestion" class="flex justify-start">
                    <div class="bg-white border border-slate-200 rounded-2xl rounded-bl-none px-4 py-2.5 text-xs text-slate-500 flex items-center gap-2">
                        <i class="fa-solid fa-spinner fa-spin text-brand-500"></i>
                        <span>Consultando catálogo...</span>
                    </div>
                </div>
            </div>

            {{-- Quick Questions --}}
            <div class="px-3 py-2 bg-white border-t border-slate-100 flex gap-1.5 overflow-x-auto shrink-0">
                <button wire:click="askQuickQuestion('¿Qué sabores y tamaños tienen?')"
                    class="whitespace-nowrap px-2.5 py-1 rounded-full bg-slate-100 hover:bg-brand-50 text-slate-600 hover:text-brand-600 text-[11px] font-semibold transition-colors cursor-pointer">
                    Sabores y precios
                </button>
                <button wire:click="askQuickQuestion('¿Cuáles son las zonas y horarios de entrega?')"
                    class="whitespace-nowrap px-2.5 py-1 rounded-full bg-slate-100 hover:bg-brand-50 text-slate-600 hover:text-brand-600 text-[11px] font-semibold transition-colors cursor-pointer">
                    Envíos y cobertura
                </button>
                <button wire:click="askQuickQuestion('Quiero pedir un Pay de Limón')"
                    class="whitespace-nowrap px-2.5 py-1 rounded-full bg-brand-50 hover:bg-brand-100 text-brand-700 text-[11px] font-semibold transition-colors cursor-pointer">
                    Pedir Pay de Limón
                </button>
            </div>

            {{-- Input Form --}}
            <form wire:submit="sendMessage" class="p-3 bg-white border-t border-slate-200 flex items-center gap-2 shrink-0">
                <input type="text" wire:model="userMessage" placeholder="Pregunta o haz tu pedido aquí..."
                    class="flex-1 px-3.5 py-2 rounded-full bg-slate-100 text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-brand-500/30 outline-none">
                <button type="submit" wire:loading.attr="disabled"
                    class="w-9 h-9 rounded-full bg-brand-600 hover:bg-brand-700 text-white flex items-center justify-center shrink-0 transition-colors cursor-pointer">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                </button>
            </form>
        </div>
    @endif
</div>
