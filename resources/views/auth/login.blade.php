<x-guest-layout>
    <div class="mb-6">
        <p class="text-sm font-semibold uppercase tracking-widest text-sky-600">Acceso seguro</p>
        <h2 class="mt-2 text-3xl font-extrabold text-slate-900">Iniciar sesion</h2>
        <p class="mt-2 text-sm text-slate-500">Ingresa a tu panel de control de inventario y ventas.</p>
    </div>

    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Correo electronico')" class="text-slate-700" />
            <x-text-input
                id="email"
                class="mt-2 block w-full rounded-xl border-slate-200 bg-white text-slate-900 shadow-none focus:border-sky-500 focus:ring-sky-500"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-600" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Contrasena')" class="text-slate-700" />

            <x-text-input
                id="password"
                class="mt-2 block w-full rounded-xl border-slate-200 bg-white text-slate-900 shadow-none focus:border-sky-500 focus:ring-sky-500"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center text-sm text-slate-600">
                <input
                    id="remember_me"
                    type="checkbox"
                    class="h-4 w-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
                    name="remember"
                >
                <span class="ms-2">{{ __('Recordarme') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm font-semibold text-slate-500 transition hover:text-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 rounded" href="{{ route('password.request') }}">
                    {{ __('Olvidaste tu contrasena?') }}
                </a>
            @endif
        </div>

        <div>
            <x-primary-button class="w-full justify-center rounded-xl border-none bg-gradient-to-r from-sky-600 to-blue-700 px-5 py-3 text-sm font-bold tracking-[0.18em] text-white uppercase hover:from-sky-700 hover:to-blue-800 focus:ring-sky-500 focus:ring-offset-2 focus:ring-offset-white">
                {{ __('Ingresar') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
