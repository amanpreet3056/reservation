@extends('layouts.frontend')

@section('title', __('Update Submitted'))

@section('content')
    <section class="bg-neutral-900/80 backdrop-blur rounded-3xl shadow-2xl overflow-hidden border border-neutral-800">
        <div class="px-8 py-16 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-300">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-8 w-8">
                    <path fill-rule="evenodd" d="M7.293 11.293a1 1 0 0 1 1.414 0L11 13.586l4.293-4.293a1 1 0 0 1 1.414 1.414l-5 5a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                </svg>
            </div>
            <h1 class="mt-6 text-3xl font-semibold text-white">{{ __('Thank you!') }}</h1>
            <p class="mt-3 text-base text-neutral-300 leading-relaxed">
                {{ session('status', __('Your updated reservation request has been received. Our team will confirm the new schedule shortly.')) }}
            </p>
            <div class="mt-10 grid gap-6 justify-items-center text-sm text-neutral-400">
                <div>
                    <p class="text-xs uppercase tracking-[0.3rem] text-neutral-500">{{ __('Reference') }}</p>
                    <p class="mt-2 font-semibold text-white">{{ $reservation->reference }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3rem] text-neutral-500">{{ __('Updated schedule') }}</p>
                    <p class="mt-2 font-semibold text-white">{{ $reservation->reservation_date?->format('d M Y') }} · {{ $reservation->reservation_time?->format('H:i') }}</p>
                </div>
            </div>
            <p class="mt-8 text-xs text-neutral-500">{{ __('Need immediate assistance? Call us at :phone.', ['phone' => $contactPhone]) }}</p>
        </div>
    </section>
@endsection