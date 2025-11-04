@extends('layouts.frontend')

@section('title', __('Reservation Cancelled'))

@section('content')
    <section class="bg-neutral-900/80 backdrop-blur rounded-3xl shadow-2xl overflow-hidden border border-neutral-800">
        <div class="px-8 py-16 text-center">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-rose-500/10 text-rose-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="mt-6 text-3xl font-semibold text-white">{{ __('Reservation cancelled') }}</h1>
            <p class="mt-3 text-base text-neutral-300 leading-relaxed">
                {{ session('status', __('We have cancelled your reservation. A confirmation email has been sent.')) }}
            </p>
            <div class="mt-10 grid gap-6 justify-items-center text-sm text-neutral-400">
                <div>
                    <p class="text-xs uppercase tracking-[0.3rem] text-neutral-500">{{ __('Reference') }}</p>
                    <p class="mt-2 font-semibold text-white">{{ $reservation->reference }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.3rem] text-neutral-500">{{ __('Guests') }}</p>
                    <p class="mt-2 font-semibold text-white">{{ $reservation->number_of_people }}</p>
                </div>
            </div>
            <p class="mt-8 text-xs text-neutral-500">{{ __('Changed your mind? Call us at :phone and we will be happy to help.', ['phone' => $contactPhone]) }}</p>
        </div>
    </section>
@endsection