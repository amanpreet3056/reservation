@extends('layouts.frontend')

@section('title', __('My Reservations'))

@section('content')
    @php
        $profileAllergies = (array) old('allergies', $guest->preferences['allergies'] ?? []);
    @endphp

    <section class="rounded-3xl border border-neutral-800/60 bg-neutral-900/70 px-8 py-12 shadow-2xl">
        <header class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-semibold text-white">{{ __('Hello, :name!', ['name' => $guest->full_name]) }}</h1>
                <p class="text-sm text-neutral-400">{{ __('Here is a quick overview of your upcoming visits and saved preferences.') }}</p>
            </div>
            <a href="{{ route('reservations.form') }}" class="inline-flex items-center gap-2 rounded-full bg-amber-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-amber-400">{{ __('Book another visit') }}</a>
        </header>

        @if(session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('guest.profile.update') }}" class="mt-8 space-y-6">
            @csrf
            @method('PUT')
            <h2 class="text-sm font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Contact details') }}</h2>
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="first_name" class="block text-sm font-semibold text-neutral-300">{{ __('First name') }}</label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $guest->first_name) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-semibold text-neutral-300">{{ __('Last name') }}</label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $guest->last_name) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
            </div>
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="email" class="block text-sm font-semibold text-neutral-300">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $guest->email) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-semibold text-neutral-300">{{ __('Phone') }}</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $guest->phone) }}" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="password" class="block text-sm font-semibold text-neutral-300">{{ __('New password') }}</label>
                    <input id="password" name="password" type="password" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30" placeholder="{{ __('Leave blank to keep current password') }}">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-neutral-300">{{ __('Confirm new password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Preferences') }}</h3>
                <p class="mt-1 text-xs text-neutral-500">{{ __('Select allergens we should remember for you.') }}</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach(config('reservations.allergies', []) as $allergy)
                        <label class="group cursor-pointer">
                            <input type="checkbox" name="allergies[]" value="{{ $allergy }}" class="peer sr-only" @checked(in_array($allergy, $profileAllergies))>
                            <span class="inline-flex w-full rounded-2xl border border-neutral-800 px-4 py-3 text-sm text-neutral-300 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-200">{{ $allergy }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <label class="flex items-center gap-3 text-sm text-neutral-300">
                <input type="checkbox" name="marketing_opt_in" value="1" class="h-5 w-5 rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500" @checked((bool) old('marketing_opt_in', $guest->marketing_opt_in))>
                <span>{{ __('Keep me informed about seasonal menus and events.') }}</span>
            </label>

            <div class="flex items-center justify-between gap-4">
                <button type="submit" class="rounded-full bg-emerald-500 px-6 py-3 text-sm font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/40">{{ __('Save changes') }}</button>
                <form method="POST" action="{{ route('guest.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-neutral-400 hover:text-neutral-50">{{ __('Sign out') }}</button>
                </form>
            </div>
        </form>

        <div class="mt-10 grid gap-6 md:grid-cols-2">
            <section class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Upcoming reservations') }}</h3>
                <ul class="mt-4 space-y-4">
                    @forelse($upcoming as $reservation)
                        <li class="rounded-xl border border-neutral-800 bg-neutral-900/80 px-4 py-3 text-sm">
                            <div class="font-semibold text-white">{{ $reservation->reservation_date?->format('d M Y') }} - {{ $reservation->reservation_time?->format('H:i') }}</div>
                            <div class="text-neutral-400">{{ trans_choice(':count guest|:count guests', $reservation->number_of_people, ['count' => $reservation->number_of_people]) }}</div>
                            <div class="mt-1 text-xs text-neutral-500">{{ __('Status: :status', ['status' => $reservation->status_label]) }}</div>
                            <div class="mt-2 flex items-center gap-3 text-xs">
                                <a href="{{ $reservation->manageUrls()['update'] }}" class="text-amber-300 hover:text-amber-200">{{ __('Modify') }}</a>
                                <a href="{{ $reservation->manageUrls()['cancel'] }}" class="text-rose-300 hover:text-rose-200">{{ __('Cancel') }}</a>
                                <a href="{{ $reservation->manageUrls()['calendar'] }}" class="text-emerald-300 hover:text-emerald-200">{{ __('Add to calendar') }}</a>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-neutral-500">{{ __('No upcoming reservations yet.') }}</li>
                    @endforelse
                </ul>
            </section>

            <section class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-5">
                <h3 class="text-sm font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Recent visits') }}</h3>
                <ul class="mt-4 space-y-4">
                    @forelse($history as $reservation)
                        <li class="rounded-xl border border-neutral-800 bg-neutral-900/80 px-4 py-3 text-sm">
                            <div class="font-semibold text-white">{{ $reservation->reservation_date?->format('d M Y') }} - {{ $reservation->reservation_time?->format('H:i') }}</div>
                            <div class="text-neutral-400">{{ trans_choice(':count guest|:count guests', $reservation->number_of_people, ['count' => $reservation->number_of_people]) }}</div>
                            <div class="mt-1 text-xs text-neutral-500">{{ __('Status: :status', ['status' => $reservation->status_label]) }}</div>
                        </li>
                    @empty
                        <li class="text-sm text-neutral-500">{{ __('No past reservations recorded yet.') }}</li>
                    @endforelse
                </ul>
            </section>
        </div>

        @if($errors->any())
            <div class="mt-6 rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
@endsection
