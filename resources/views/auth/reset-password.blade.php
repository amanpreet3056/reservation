@extends('layouts.frontend')

@section('title', __('Set a New Password'))

@section('content')
    <section class="rounded-3xl border border-neutral-800/70 bg-neutral-900/70 px-8 py-12 shadow-2xl">
        <h1 class="text-3xl font-semibold text-white">{{ __('Create a new password') }}</h1>
        <p class="mt-2 text-sm text-neutral-400">
            {{ __('Please choose a strong password for your account.') }}
        </p>

        <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-6">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="block text-sm font-semibold text-neutral-300">{{ __('Email Address') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                @error('email')
                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-neutral-300">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                @error('password')
                    <p class="mt-2 text-sm text-rose-300">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-neutral-300">{{ __('Confirm Password') }}</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </div>

            <button type="submit" class="w-full rounded-full bg-emerald-500 px-5 py-3 text-sm font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-emerald-400">
                {{ __('Reset Password') }}
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500 hover:text-amber-300">
                {{ __('Back to login') }}
            </a>
        </div>
    </section>
@endsection
