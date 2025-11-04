@extends('layouts.admin')

@section('title', __('Edit User'))
@section('page-title', __('Edit User'))

@section('content')
    <section class="max-w-2xl rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-8">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-semibold text-neutral-300">{{ __('Full name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </div>
            <div>
                <label for="email" class="block text-sm font-semibold text-neutral-300">{{ __('Email address') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="phone" class="block text-sm font-semibold text-neutral-300">{{ __('Phone (optional)') }}</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
                <div>
                    <label for="role" class="block text-sm font-semibold text-neutral-300">{{ __('Role') }}</label>
                    <select id="role" name="role" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                        <option value="manager" @selected(old('role', $user->role) === 'manager')>{{ __('Manager') }}</option>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>{{ __('Admin') }}</option>
                    </select>
                </div>
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold text-neutral-300">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30" placeholder="{{ __('Leave blank to keep current password') }}">
            </div>
            <div>
                <label class="inline-flex items-center gap-2 text-sm text-neutral-300">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active)) class="h-4 w-4 border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500/40">
                    {{ __('Active account') }}
                </label>
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="rounded-full border border-neutral-700 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-300 transition hover:border-neutral-500 hover:text-white">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-full bg-amber-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-amber-400">{{ __('Update User') }}</button>
            </div>
        </form>
    </section>
@endsection