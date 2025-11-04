@extends('layouts.frontend')

@section('title', __('Admin Sign In'))

@section('content')
    <section class="rounded-3xl border border-neutral-800/70 bg-neutral-900/70 px-8 py-12 shadow-2xl">
        <h1 class="text-3xl font-semibold text-white">{{ __('Welcome back') }}</h1>
        <p class="mt-2 text-sm text-neutral-400">{{ __('Access the reservation control panel using your administrator credentials.') }}</p>

        @if (session('status'))
            <div class="mt-6 rounded-2xl border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-6">
            @csrf
            <div>
                <label for="email" class="block text-sm font-semibold text-neutral-300">{{ __('Email Address') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autofocus required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold text-neutral-300">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <label class="inline-flex items-center gap-2 text-sm text-neutral-400">
                    <input type="checkbox" name="remember" class="rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500/40">
                    {{ __('Remember me') }}
                </label>
                <div class="flex items-center gap-3">
                    <a href="{{ route('password.request') }}" class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500 hover:text-amber-300">{{ __('Forgot password?') }}</a>
                    <a href="{{ route('reservations.form') }}" class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500 hover:text-amber-300">{{ __('Back to site') }}</a>
                </div>
            </div>
            <button type="submit" class="w-full rounded-full bg-amber-500 px-5 py-3 text-sm font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-amber-400">{{ __('Sign In') }}</button>
        </form>

        @if ($errors->any())
            <div class="mt-6 rounded-2xl border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <ul class="space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
@endsection
