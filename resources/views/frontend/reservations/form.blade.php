@extends('layouts.frontend')

@section('title', __('Book Your Reservation'))

@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $timeSlots = [];
    $slot = Carbon::createFromTime(11, 0);
    $endSlot = Carbon::createFromTime(23, 0);

    while ($slot <= $endSlot) {
        $timeSlots[$slot->format('H:i')] = $slot->format('h:i A');
        $slot->addMinutes(30);
    }

    $selectedPeople = (int) request('number_of_people', 1);
    if ($selectedPeople < 1 || $selectedPeople > 12) {
        $selectedPeople = 1;
    }

    $rawDate = request('reservation_date');
    try {
        $selectedDate = $rawDate ? Carbon::parse($rawDate)->format('Y-m-d') : now()->format('Y-m-d');
    } catch (\Throwable $exception) {
        $selectedDate = now()->format('Y-m-d');
    }

    $selectedTime = request('reservation_time');
    if (!array_key_exists($selectedTime, $timeSlots)) {
        $selectedTime = array_key_first($timeSlots);
    }

    $visitPurposes = config('reservations.visit_purposes', []);
    $selectedService = 'casual_visit';
    $selectedServiceLabel = $visitPurposes['casual_visit'] ?? __('Book a Table');
    $allergies = config('reservations.allergies', []);
    $guestAccount = auth('guest')->user();
    $guestPreferences = $guestAccount?->preferences ?? [];
    $selectedFirstName = old('first_name', $guestAccount->first_name ?? '');
    $selectedLastName = old('last_name', $guestAccount->last_name ?? '');
    $selectedEmail = old('email', $guestAccount->email ?? '');
    $selectedPhone = old('phone', $guestAccount->phone ?? '');
    $selectedCompany = old('company', $guestAccount->company ?? '');
    $selectedAllergies = (array) old('allergies', $guestPreferences['allergies'] ?? []);
    $marketingOptIn = (bool) old('marketing_opt_in', $guestAccount->marketing_opt_in ?? false);
@endphp

@section('content')
    <section class="bg-neutral-900/80 backdrop-blur rounded-3xl shadow-2xl overflow-hidden border border-neutral-800">
        <header class="px-8 py-10 border-b border-neutral-800 bg-gradient-to-r from-amber-500/10 via-transparent to-transparent">
            <span class="inline-flex items-center gap-2 text-sm font-semibold uppercase tracking-[0.25rem] text-amber-400">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                {{ __('Royal Coupon Code | Dining Reservation') }}
            </span>
            <h1 class="mt-4 text-3xl font-semibold text-white sm:text-4xl">
                {{ __('Tell us who to welcome') }}
            </h1>
            <p class="mt-3 text-base text-neutral-300 leading-relaxed">
                {{ __('Share a few details and we will save you a seat. Your request goes straight to our hosts for a prompt confirmation.') }}
            </p>
        </header>

        @if($closure)
            <div class="mx-8 mt-6 rounded-2xl border border-amber-500/40 bg-amber-500/10 p-6 text-amber-200">
                <h2 class="text-lg font-semibold">{{ __('Online bookings are temporarily paused') }}</h2>
                <p class="mt-2 text-sm leading-relaxed">
                    {{ $closure->message }} {{ __('Kindly Call on :phone for more information.', ['phone' => $contactPhone]) }}
                </p>
                <p class="mt-4 text-xs uppercase tracking-widest text-amber-300/80">
                    {{ __('Unavailable from :start to :end', [
                        'start' => $closure->starts_at?->format('d M Y H:i'),
                        'end' => optional($closure->ends_at)->format('d M Y H:i') ?? __('until further notice'),
                    ]) }}
                </p>
            </div>
        @endif

        <div id="reservation-form" class="relative">
            <div
                class="px-8 py-10"
                data-reservation-app
                data-submit-url="{{ route('reservations.store') }}"
                data-availability-url="{{ route('reservations.availability') }}"
                data-default-service="{{ $selectedService }}"
                data-visit-purposes='@json($visitPurposes)'
                @if($closure)
                    data-booking-disabled="true"
                    data-booking-disabled-message='{{ $closure->message }} {{ __('Kindly Call on :phone for more information.', ['phone' => $contactPhone]) }}'
                @endif
            >
                @if($guestAccount)
                    <form id="guest-logout-form" method="POST" action="{{ route('guest.logout') }}" class="hidden">
                        @csrf
                    </form>
                @endif

                    <div class="text-sm text-neutral-400 min-h-[1.5rem]" data-global-feedback></div>
                <form data-reservation-form class="space-y-12">
                    @csrf
                    <div class="space-y-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <span class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Plan') }}</span>
                                <h2 class="mt-2 text-xl font-semibold text-white sm:text-2xl">{{ $selectedServiceLabel }}</h2>
                            </div>
                        </div>
                        <input type="hidden" name="service_type" value="{{ $selectedService }}" data-service-input>
                        @if($guestAccount)
                            <input type="hidden" name="guest_id" value="{{ $guestAccount->id }}">
                        @endif
                    </div>

                    <div class="grid gap-6 sm:grid-cols-3">
                        <div>
                            <label for="reservation_date" class="block text-sm font-semibold text-neutral-300">
                                {{ __('Date') }}
                            </label>
                            <input
                                id="reservation_date"
                                type="text"
                                name="reservation_date"
                                data-date-picker
                                data-default-date="{{ $selectedDate }}"
                                class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                                placeholder="dd-mm-yyyy"
                                autocomplete="off"
                                value="{{ $selectedDate }}"
                            >
                            <p class="mt-2 text-xs text-neutral-500">{{ __('Bookings available for the next 30 days.') }}</p>
                        </div>

                        <div>
                            <label for="reservation_time" class="block text-sm font-semibold text-neutral-300">
                                {{ __('Time') }}
                            </label>
                            <select
                                id="reservation_time"
                                name="reservation_time"
                                class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                                data-time-select
                            >
                                @foreach ($timeSlots as $value => $label)
                                    <option value="{{ $value }}" @selected($value === $selectedTime)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-neutral-500" data-availability-feedback></p>
                        </div>

                        <div>
                            <label for="number_of_people" class="block text-sm font-semibold text-neutral-300">
                                {{ __('Party size') }}
                            </label>
                            <div class="mt-2 relative">
                                <select
                                    id="number_of_people"
                                    name="number_of_people"
                                    class="w-full appearance-none rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                                >
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" @selected($i === $selectedPeople)>{{ $i }} {{ Str::plural(__('Guest'), $i) }}</option>
                                    @endfor
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-neutral-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.939l3.71-3.71a.75.75 0 111.06 1.061l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @if($guestAccount)
                            <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/5 px-4 py-3 text-sm text-emerald-100">
                                {{ __('Welcome back, :name!', ['name' => $guestAccount->full_name]) }}
                                <div class="mt-1 text-xs text-emerald-200">
                                    <span>{{ __('Your saved details are pre-filled below.') }}</span>
                                    <a href="{{ route('guest.dashboard') }}" class="ml-2 text-emerald-300 underline">{{ __('Manage account') }}</a>
                                    <button type="button" class="ml-2 text-emerald-300 underline" onclick="document.getElementById('guest-logout-form')?.submit();">{{ __('Sign out') }}</button>
                                </div>
                            </div>
                        @endif

                        <div>
                            <span class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Your details') }}</span>
                            <h2 class="mt-2 text-xl font-semibold text-white sm:text-2xl">{{ __('Tell us who to welcome') }}</h2>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="first_name" class="block text-sm font-semibold text-neutral-300">{{ __('First name') }}</label>
                                <input id="first_name" name="first_name" type="text" autocomplete="given-name" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30" placeholder="{{ __('Asha') }}" value="{{ $selectedFirstName }}">
                            </div>
                            <div>
                                <label for="last_name" class="block text-sm font-semibold text-neutral-300">{{ __('Last name') }}</label>
                                <input id="last_name" name="last_name" type="text" autocomplete="family-name" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30" placeholder="{{ __('Kapoor') }}" value="{{ $selectedLastName }}">
                            </div>
                        </div>

                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="email" class="block text-sm font-semibold text-neutral-300">{{ __('Email') }}</label>
                                <input id="email" name="email" type="email" autocomplete="email" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30" placeholder="asha@example.com" value="{{ $selectedEmail }}">
                            </div>
                            <div>
                                <label for="phone" class="block text-sm font-semibold text-neutral-300">{{ __('Phone') }}</label>
                                <input id="phone" name="phone" type="tel" autocomplete="tel" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30" placeholder="+1 555 123 4567" value="{{ $selectedPhone }}">
                            </div>
                        </div>
                    </div>

                    @if(!empty($allergies))
                        <fieldset class="space-y-4">
                            <legend class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Allergens') }}</legend>
                            <p class="text-sm text-neutral-400">{{ __('Select all that apply so we can tailor your menu.') }}</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach($allergies as $allergy)
                                    <label class="group cursor-pointer">
                                        <input type="checkbox" name="allergies[]" value="{{ $allergy }}" class="peer sr-only" @checked(in_array($allergy, $selectedAllergies ?? []))>
                                        <span class="inline-flex w-full rounded-2xl border border-neutral-800 px-4 py-3 text-sm text-neutral-300 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-500/10 peer-checked:text-emerald-200">{{ $allergy }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @endif

                    <div>
                        <label for="message" class="block text-sm font-semibold text-neutral-300">{{ __('What else would you like us to know?') }}</label>
                        <textarea id="message" name="message" rows="4" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30" placeholder="{{ __('Share anything that will help us prepare the perfect visit.') }}">{{ old('message') }}</textarea>
                    </div>

                    <div class="space-y-6">
                        <label class="flex items-center gap-3 text-sm text-neutral-300">
                            <input type="checkbox" name="marketing_opt_in" value="1" class="h-5 w-5 rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500" @checked($marketingOptIn)>
                            <span>{{ __('Keep me in the loop about seasonal menus and events.') }}</span>
                        </label>

                        <p class="text-xs text-neutral-500 leading-relaxed">
                            {{ __('By reserving, you agree to our 24-hour cancellation policy. Need help? Email :email.', ['email' => 'asha@dishdemo.com']) }}
                        </p>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="text-sm text-neutral-400 min-h-[1.5rem]" data-feedback></div>
                            <button type="submit" class="inline-flex items-center justify-center rounded-full bg-emerald-500 px-6 py-3 text-sm font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/40" data-action="submit">
                                {{ __('Confirm reservation') }}
                            </button>
                        </div>
                    </div>
                </form>

                <div data-success-panel class="mt-10 hidden">
                    <div class="rounded-3xl border border-emerald-500/40 bg-emerald-500/10 p-8 text-emerald-100">
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-200">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-white">{{ __('Reservation request received!') }}</h2>
                                <p class="mt-2 text-sm leading-relaxed text-emerald-100/90" data-success-message>
                                    {{ __('Thank you! Your reservation request has been submitted. We will reach out soon.') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4" data-timeline-container hidden>
                            <h3 class="text-sm font-semibold uppercase tracking-[0.3rem] text-emerald-200">{{ __('What happens next') }}</h3>
                            <ul class="space-y-3" data-timeline></ul>
                        </div>

                        <div class="mt-6 flex flex-wrap items-center gap-3" data-calendar-container hidden>
                            <a href="#" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full border border-emerald-400 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2rem] text-emerald-200 transition hover:bg-emerald-400/10" data-calendar-google>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                                    <path d="M12 2a10 10 0 100 20 10 10 0 000-20zM6.5 12a5.5 5.5 0 019.32-4.01l-1.48 1.37A3.71 3.71 0 008.3 12h8.2v.46a5.7 5.7 0 01-9.78 4.02l1.62-1.48A3.53 3.53 0 0012 15.54a3.47 3.47 0 002.86-1.54h-3.19V12H17c.04.29.04.58.04.88A5.96 5.96 0 016.5 12z" />
                                </svg>
                                {{ __('Add to Google Calendar') }}
                            </a>
                            <a href="#" class="inline-flex items-center gap-2 rounded-full border border-neutral-700 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2rem] text-neutral-200 transition hover:bg-neutral-800" data-calendar-ics>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                                    <path d="M6 2a2 2 0 00-2 2v16c0 1.1.9 2 2 2h9.99c1.1 0 2-.9 2-2V8.5L13.5 2H6zm5 7V3.5L16.5 9H11z" />
                                </svg>
                                {{ __('Download calendar invite') }}
                            </a>
                        </div>
                    </div>

                    <div class="mt-8 text-xs uppercase tracking-[0.3rem] text-neutral-500">
                        {{ __('Thank you for choosing Royal Coupon Code Restaurant') }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

