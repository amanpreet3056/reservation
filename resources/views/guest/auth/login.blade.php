@extends('layouts.frontend')

@section('title', __('Guest Sign In'))

@section('content')
    <section class="rounded-3xl border border-neutral-800/60 bg-neutral-900/70 px-8 py-12 shadow-2xl">
        <h1 class="text-3xl font-semibold text-white">{{ __('Welcome back') }}</h1>
        <p class="mt-2 text-sm text-neutral-400">{{ __('Sign in to review upcoming reservations, update your preferences, and book faster.') }}</p>

        <form method="POST" action="{{ route('guest.login.submit') }}" class="mt-8 space-y-6">
            @csrf
            <div>
                <label for="email" class="block text-sm font-semibold text-neutral-300">{{ __('Email address') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold text-neutral-300">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </div>
            <label class="flex items-center gap-2 text-sm text-neutral-300">
                <input type="checkbox" name="remember" value="1" class="h-5 w-5 rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500">
                {{ __('Remember me') }}
            </label>
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('guest.register') }}" class="text-sm text-neutral-400 hover:text-neutral-100">{{ __('Need an account? Register') }}</a>
                <button type="submit" class="rounded-full bg-emerald-500 px-6 py-3 text-sm font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-emerald-400 focus:outline-none focus:ring-4 focus:ring-emerald-500/40">{{ __('Sign in') }}</button>
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
