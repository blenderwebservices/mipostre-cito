<x-layouts.app title="Iniciar Sesión | Mi Postrecito">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-br from-gepp-50 via-white to-brand-50">
        <div class="sm:mx-auto sm:w-full sm:max-w-md px-4">
            <a href="{{ route('home') }}" class="flex items-center justify-center gap-2.5 mb-6">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-white flex items-center justify-center shadow-md">
                    <i class="fa-solid fa-ice-cream text-xl"></i>
                </div>
                <span class="font-handwriting text-4xl font-bold text-brand-600">Mi Postrecito</span>
            </a>
            <h2 class="text-center text-2xl font-extrabold text-slate-900">Inicia sesión para realizar tu pedido</h2>
            <p class="mt-2 text-center text-sm text-slate-600">
                ¿Aún no tienes cuenta?
                <a href="{{ route('register') }}" class="font-bold text-brand-600 hover:text-brand-700">Regístrate en 30 segundos</a>
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4">
            <div class="bg-white py-8 px-6 shadow-xl rounded-3xl border border-slate-100 sm:px-10">
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase mb-1">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                        @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase mb-1">Contraseña</label>
                        <input id="password" name="password" type="password" required
                            class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-gepp-500 outline-none">
                        @error('password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                            Recordarme
                        </label>
                        <a href="{{ route('home') }}" class="text-xs font-semibold text-gepp-600 hover:underline">← Volver al catálogo</a>
                    </div>

                    <button type="submit"
                        class="w-full py-3 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-lg transition-all cursor-pointer">
                        Ingresar y Continuar
                    </button>
                </form>

                <div class="mt-6 pt-5 border-t border-slate-100 text-xs text-slate-500 space-y-1">
                    <p class="font-bold text-slate-700">Acceso rápido de prueba:</p>
                    <p>Cliente: <code class="bg-slate-100 px-1.5 py-0.5 rounded">cliente@ejemplo.com</code> / <code class="bg-slate-100 px-1.5 py-0.5 rounded">password</code></p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

