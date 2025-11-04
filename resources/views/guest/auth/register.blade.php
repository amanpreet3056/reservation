@extends('layouts.frontend')

@section('title', __('Create Guest Account'))

@section('content')
    <section class="rounded-3xl border border-neutral-800/60 bg-neutral-900/70 px-8 py-12 shadow-2xl">
        <h1 class="text-3xl font-semibold text-white">{{ __('Create your guest account') }}</h1>
        <p class="mt-2 text-sm text-neutral-400">{{ __('Save your details, track reservations, and check out faster next time.') }}</p>

        <form method="POST" action="{{ route('guest.register.submit') }}" class="mt-8 space-y-6">
            @csrf
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="first_name" class="block text-sm font-semibold text-neutral-300">{{ __('First name') }}</label>
                    <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}" required autofocus class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-semibold text-neutral-300">{{ __('Last name') }}</label>
                    <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="email" class="block text-sm font-semibold text-neutral-300">{{ __('Email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-semibold text-neutral-300">{{ __('Phone') }}</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="password" class="block text-sm font-semibold text-neutral-300">{{ __('Password') }}</label>
                    <input id="password" name="password" type="password" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-neutral-300">{{ __('Confirm password') }}</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
            </div>

            <div>
                <label class="flex items-center gap-3 text-sm text-neutral-300">
                    <input type="checkbox" name="marketing_opt_in" value="1" class="h-5 w-5 rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500" @checked((bool) old('marketing_opt_in', false))>
                    <span>{{ __('Send me occasional updates about seasonal menus and events.') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('guest.login') }}" class="text-sm text-neutral-400 hover:text-neutral-100">{{ __('Already have an account? Sign in') }}</a>
                <button type="submit" class="rounded-full bg-emerald-500 px-6 py-3 text-sm font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/40">{{ __('Create account') }}</button>
            </div>
        </form>

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
