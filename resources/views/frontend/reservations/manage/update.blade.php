@extends('layouts.frontend')

@section('title', __('Update Reservation'))

@section('content')
    <section class="bg-neutral-900/80 backdrop-blur rounded-3xl shadow-2xl overflow-hidden border border-neutral-800">
        <header class="px-8 py-10 border-b border-neutral-800 bg-gradient-to-r from-emerald-500/10 via-transparent to-transparent">
            <span class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.25rem] text-emerald-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                {{ __('Manage Your Reservation') }}
            </span>
            <h1 class="mt-4 text-3xl font-semibold text-white sm:text-4xl">
                {{ __('Update date or time') }}
            </h1>
            <p class="mt-3 text-base text-neutral-300 leading-relaxed">
                {{ __('You can adjust only the date and time. For other changes kindly contact us at :phone.', ['phone' => $contactPhone]) }}
            </p>
        </header>

        <div class="px-8 py-10">\n            @if ($errors->any())\n                <div class="mb-6 rounded-2xl border border-rose-500/40 bg-rose-500/10 p-4 text-sm text-rose-200">\n                    <ul class="space-y-1">\n                        @foreach ($errors->all() as $error)\n                            <li>{{ $error }}</li>\n                        @endforeach\n                    </ul>\n                </div>\n            @endif
            <div class="grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-6">
                    <h2 class="text-lg font-semibold text-white">{{ __('Reservation summary') }}</h2>
                    <dl class="mt-4 space-y-3 text-sm text-neutral-300">
                        <div>
                            <dt class="text-xs uppercase tracking-[0.25rem] text-neutral-500">{{ __('Reference') }}</dt>
                            <dd class="mt-1 text-white font-semibold">{{ $reservation->reference }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.25rem] text-neutral-500">{{ __('Guest') }}</dt>
                            <dd class="mt-1 text-white font-semibold">{{ $reservation->guest?->full_name }}</dd>
                            <dd class="text-neutral-400">{{ $reservation->guest?->email }}</dd>
                            <dd class="text-neutral-400">{{ $reservation->guest?->phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.25rem] text-neutral-500">{{ __('Party size') }}</dt>
                            <dd class="mt-1 text-neutral-200">{{ $reservation->number_of_people }} {{ \Illuminate\Support\Str::plural('guest', $reservation->number_of_people) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.25rem] text-neutral-500">{{ __('Occasion') }}</dt>
                            <dd class="mt-1 text-neutral-200">{{ $reservation->occasion ?? __('Not specified') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.25rem] text-neutral-500">{{ __('Assigned table') }}</dt>
                            <dd class="mt-1 text-neutral-200">{{ $reservation->table?->name ?? __('Waiting for confirmation') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs uppercase tracking-[0.25rem] text-neutral-500">{{ __('Current schedule') }}</dt>
                            <dd class="mt-1 text-neutral-200">{{ $reservation->reservation_date?->format('d M Y') }} · {{ $reservation->reservation_time?->format('H:i') }}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <form method="POST" action="{{ route('reservations.manage.update', [$reservation->reference, $reservation->manage_token]) }}" class="space-y-5">
                        @csrf
                        @method('PUT')
                        <div>
                            <label for="reservation_date" class="block text-sm font-semibold text-neutral-300">{{ __('New date') }}</label>
                            <input id="reservation_date" type="date" name="reservation_date" value="{{ old('reservation_date', $reservation->reservation_date?->format('Y-m-d')) }}" min="{{ now()->format('Y-m-d') }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30">
                        </div>
                        <div>
                            <label for="reservation_time" class="block text-sm font-semibold text-neutral-300">{{ __('New time') }}</label>
                            <input id="reservation_time" type="time" name="reservation_time" value="{{ old('reservation_time', $reservation->reservation_time?->format('H:i')) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/30" step="900">
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <a href="{{ route('reservations.manage.cancel', [$reservation->reference, $reservation->manage_token]) }}" class="rounded-full border border-neutral-700 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-300 transition hover:border-neutral-500 hover:text-white">{{ __('Cancel reservation') }}</a>
                            <button type="submit" class="rounded-full bg-emerald-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-emerald-400">{{ __('Submit change') }}</button>
                        </div>
                    </form>
                    <p class="mt-6 text-xs text-neutral-500">{{ __('Need help? Call us at :phone.', ['phone' => $contactPhone]) }}</p>
                </div>
            </div>
        </div>
    </section>
@endsection