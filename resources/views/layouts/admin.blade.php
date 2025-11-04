<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('Reservation Admin'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'],
                    },
                },
            },
        };
    </script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <style>
        body,
        .font-sans {
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
        }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
    <script defer src="{{ asset('js/app.js') }}"></script>
</head>
<body class="min-h-screen bg-neutral-950 text-neutral-100 font-sans">
    <div class="min-h-screen flex">
        <aside class="hidden lg:flex lg:w-72 xl:w-80 flex-col border-r border-neutral-800/60 bg-neutral-950/80 backdrop-blur">
            <div class="px-6 py-8 border-b border-neutral-800/60">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-400 font-semibold">RC</span>
                    <div>
                        <span class="block text-sm font-semibold uppercase tracking-[0.3rem] text-neutral-500">Royal Coupon Code</span>
                        <span class="mt-1 block text-lg font-semibold text-white">{{ __('Reservation Suite') }}</span>
                    </div>
                </a>
            </div>

            @php
                $navItems = [
                    ['label' => __('Dashboard'), 'route' => 'admin.dashboard', 'icon' => 'dashboard'],
                    ['label' => __('Reservations'), 'route' => 'admin.reservations.index', 'icon' => 'calendar'],
                    ['label' => __('Tables'), 'route' => 'admin.tables.index', 'icon' => 'table'],
                    ['label' => __('Guests'), 'route' => 'admin.guests.index', 'icon' => 'users'],
                    ['label' => __('Reports'), 'route' => 'admin.reports.index', 'icon' => 'chart'],
                    ['label' => __('Settings'), 'route' => 'admin.settings.edit', 'icon' => 'settings'],
                ];

                if (auth()->user()?->isAdmin()) {
                    $navItems[] = ['label' => __('Users'), 'route' => 'admin.users.index', 'icon' => 'shield'];
                }

                $navItems[] = ['label' => __('Language'), 'route' => 'admin.language.index', 'icon' => 'language'];
            @endphp

            <nav class="flex-1 overflow-y-auto px-4 py-6">
                <ul class="space-y-2">
                    @foreach ($navItems as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                               class="group flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition @if(request()->routeIs($item['route'].'*')) bg-amber-500/10 text-amber-400 border border-amber-500/40 @else text-neutral-400 hover:text-white hover:bg-neutral-900/70 @endif">
                                <x-admin.nav-icon :name="$item['icon']" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="border-t border-neutral-800/60 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-neutral-900 text-amber-400 font-semibold">
                        {{ \Illuminate\Support\Str::of(auth()->user()->name ?? '?')->substr(0, 2)->upper() }}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-neutral-500 uppercase tracking-[0.25rem]">{{ auth()->user()->role }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-full border border-neutral-700 px-3 py-2 text-xs uppercase tracking-[0.2rem] text-neutral-400 transition hover:text-white hover:border-amber-400">
                            {{ __('Logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-h-screen">
            <header class="sticky top-0 z-40 border-b border-neutral-800/60 bg-neutral-950/80 backdrop-blur px-4 sm:px-8">
                <div class="flex h-20 items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Control Panel') }}</p>
                        <h1 class="mt-1 text-2xl font-semibold text-white">@yield('page-title')</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="hidden sm:flex flex-col text-right">
                            <span class="text-sm font-medium text-white">{{ now()->format('l, d F Y') }}</span>
                            <span class="text-xs text-neutral-500">{{ __('Local time: :time', ['time' => now()->format('H:i')]) }}</span>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 px-4 py-8 sm:px-8">
                @if (session('success'))
                    <div class="mb-6 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
