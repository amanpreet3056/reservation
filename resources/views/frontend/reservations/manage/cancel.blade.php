@extends('layouts.frontend')

@section('title', __('Cancel Reservation'))

@section('content')
    <section class="bg-neutral-900/80 backdrop-blur rounded-3xl shadow-2xl overflow-hidden border border-neutral-800">
        <header class="px-8 py-10 border-b border-neutral-800 bg-gradient-to-r from-rose-500/10 via-transparent to-transparent">
            <span class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.25rem] text-rose-300">
                <span class="w-2 h-2 rounded-full bg-rose-400 animate-pulse"></span>
                {{ __('Cancel Reservation') }}
            </span>
            <h1 class="mt-4 text-3xl font-semibold text-white sm:text-4xl">
                {{ __('We are sorry to see you go') }}
            </h1>
            <p class="mt-3 text-base text-neutral-300 leading-relaxed">
                {{ __('Cancelling will free your table. If you need to reschedule instead, you can return to the update page. For any questions call us at :phone.', ['phone' => $contactPhone]) }}
            </p>
        </header>

        <div class="px-8 py-10">
            <div class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-6">
                <dl class="grid gap-4 sm:grid-cols-2 text-sm text-neutral-300">
                    <div>
                        <dt class="text-xs uppercase tracking-[0.25rem] text-neutral-500">{{ __('Reference') }}</dt>
                        <dd class="mt-1 text-white font-semibold">{{ $reservation->reference }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-[0.25rem] text-neutral-500">{{ __('Guest') }}</dt>
                        <dd class="mt-1 text-neutral-200">{{ $reservation->guest?->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-[0.25rem] text-neutral-500">{{ __('Date & time') }}</dt>
                        <dd class="mt-1 text-neutral-200">{{ $reservation->reservation_date?->format('d M Y') }} · {{ $reservation->reservation_time?->format('H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase tracking-[0.25rem] text-neutral-500">{{ __('Party size') }}</dt>
                        <dd class="mt-1 text-neutral-200">{{ $reservation->number_of_people }} {{ \Illuminate\Support\Str::plural('guest', $reservation->number_of_people) }}</dd>
                    </div>
                </dl>
            </div>

            <form method="POST" action="{{ route('reservations.manage.cancel.submit', [$reservation->reference, $reservation->manage_token]) }}" class="mt-8 flex flex-col gap-4 sm:flex-row sm:justify-end">
                @csrf
                <a href="{{ route('reservations.manage.edit', [$reservation->reference, $reservation->manage_token]) }}" class="inline-flex items-center justify-center rounded-full border border-neutral-700 px-5 py-3 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-300 transition hover:border-neutral-500 hover:text-white">{{ __('Keep reservation') }}</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-rose-500 px-5 py-3 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-rose-400">{{ __('Confirm cancellation') }}</button>
            </form>
        </div>
    </section>
@endsection