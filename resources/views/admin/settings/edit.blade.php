@extends('layouts.admin')

@section('title', __('Settings'))
@section('page-title', __('Settings'))

@section('content')
    <section class="max-w-3xl rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-8">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf
            <div>
                <label for="restaurant_name" class="block text-sm font-semibold text-neutral-300">{{ __('Restaurant name') }}</label>
                <input id="restaurant_name" name="restaurant_name" type="text" value="{{ old('restaurant_name', $settings['restaurant_name']) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </div>
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="contact_email" class="block text-sm font-semibold text-neutral-300">{{ __('Primary contact email') }}</label>
                    <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $settings['contact_email']) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
                <div>
                    <label for="contact_phone" class="block text-sm font-semibold text-neutral-300">{{ __('Reservation hotline') }}</label>
                    <input id="contact_phone" name="contact_phone" type="text" value="{{ old('contact_phone', $settings['contact_phone']) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                    <p class="mt-2 text-xs text-neutral-500">{{ __('This phone number appears in booking closure notices.') }}</p>
                </div>
            </div>
            <div>
                <label for="notification_emails" class="block text-sm font-semibold text-neutral-300">{{ __('Notification emails') }}</label>
                <textarea id="notification_emails" name="notification_emails" rows="3" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30" placeholder="{{ __('Separate multiple emails with commas') }}">{{ old('notification_emails', $settings['notification_emails']) }}</textarea>
            </div>
            <div class="flex items-center justify-end gap-3">
                <button type="submit" class="rounded-full bg-amber-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-amber-400">{{ __('Save Settings') }}</button>
            </div>
        </form>
    </section>
@endsection