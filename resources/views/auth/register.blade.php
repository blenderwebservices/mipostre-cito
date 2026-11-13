<x-layouts.app title="Crear Cuenta | Mi Postrecito">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-br from-gepp-50 via-white to-brand-50">
        <div class="sm:mx-auto sm:w-full sm:max-w-lg px-4">
            <a href="{{ route('home') }}" class="flex items-center justify-center gap-2.5 mb-6">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-ice-cream text-xl"></i>
                </div>
                <span class="font-handwriting text-4xl font-bold text-brand-600">Mi Postrecito</span>
            </a>
            <h2 class="text-center text-2xl font-extrabold text-slate-900">Crea tu cuenta de entregas</h2>
            <p class="mt-2 text-center text-sm text-slate-600">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}" class="font-bold text-brand-600 hover:text-brand-700">Inicia sesión aquí</a>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg px-4">
            <div class="bg-white py-8 px-6 shadow-xl rounded-3xl border border-slate-100 sm:px-10">
                <form method="POST" action="{{ route('register') }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nombre completo *</label>
                            <input name="name" type="text" value="{{ old('name') }}" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                            @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Teléfono / WhatsApp *</label>
                            <input name="phone" type="tel" value="{{ old('phone') }}" required placeholder="+52 55 1234 5678"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                            @error('phone') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Correo electrónico *</label>
                        <input name="email" type="email" value="{{ old('email') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                        @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Zona de Cobertura *</label>
                        <select name="default_zone" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                            <option value="Zona Centro / Roma / Condesa">Zona Centro / Roma / Condesa</option>
                            <option value="Zona Sur (Del Valle / Coyoacán / Nápoles)">Zona Sur (Del Valle / Coyoacán / Nápoles)</option>
                            <option value="Zona Poniente (Polanco / Anzures)">Zona Poniente (Polanco / Anzures)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Dirección de entrega *</label>
                        <input name="default_address" type="text" value="{{ old('default_address') }}" required placeholder="Calle, número, colonia y referencias"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                        @error('default_address') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Contraseña *</label>
                            <input name="password" type="password" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                            @error('password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Confirmar contraseña *</label>
                            <input name="password_confirmation" type="password" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-3 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-lg transition-all cursor-pointer mt-2">
                        Crear Cuenta y Pedir
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>

