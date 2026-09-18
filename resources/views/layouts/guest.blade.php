<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'BIG-DACO') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased [font-family:'Outfit',sans-serif] bg-slate-950 text-slate-100">
        <div class="relative min-h-screen overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.2),_transparent_48%),radial-gradient(circle_at_bottom_right,_rgba(34,197,94,0.18),_transparent_40%),linear-gradient(145deg,_#020617,_#0f172a_55%,_#1e293b)]"></div>

            <div class="relative min-h-screen px-4 py-8 sm:px-6 lg:px-8 flex items-center justify-center">
                <div class="w-full max-w-5xl rounded-3xl border border-white/10 bg-slate-900/65 shadow-2xl backdrop-blur-sm overflow-hidden">
                    <div class="grid lg:grid-cols-2">
                        <aside class="hidden lg:flex flex-col justify-between p-10 bg-slate-900/70 border-r border-white/10">
                            <div>
                                <p class="inline-flex items-center rounded-full border border-sky-300/40 bg-sky-500/10 px-3 py-1 text-xs font-semibold tracking-wide text-sky-200">
                                    Plataforma de gestion comercial
                                </p>
                                <h1 class="mt-6 text-4xl font-extrabold leading-tight text-white">BIG-DACO</h1>
                                <p class="mt-4 text-slate-300 text-base leading-relaxed">
                                    Controla inventario, ventas y reportes desde un solo lugar con datos claros y decisiones mas rapidas.
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <article class="rounded-2xl border border-sky-300/20 bg-sky-500/10 p-4">
                                    <p class="text-xs uppercase tracking-wider text-sky-200/80">Inventario</p>
                                    <p class="mt-2 text-2xl font-bold text-white">En linea</p>
                                </article>
                                <article class="rounded-2xl border border-emerald-300/20 bg-emerald-500/10 p-4">
                                    <p class="text-xs uppercase tracking-wider text-emerald-200/80">Ventas</p>
                                    <p class="mt-2 text-2xl font-bold text-white">En tiempo real</p>
                                </article>
                            </div>
                        </aside>

                        <section class="bg-white/95 px-6 py-8 sm:px-10 sm:py-10 text-slate-800">
                            {{ $slot }}
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
