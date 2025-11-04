@extends('layouts.admin')

@section('title', __('Add Table'))
@section('page-title', __('Add Table'))

@section('content')
    <section class="max-w-2xl rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-8">
        <form method="POST" action="{{ route('admin.tables.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="name" class="block text-sm font-semibold text-neutral-300">{{ __('Table Name') }}</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="seats" class="block text-sm font-semibold text-neutral-300">{{ __('Seats') }}</label>
                    <input id="seats" name="seats" type="number" min="1" max="20" value="{{ old('seats', 2) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                </div>
                <div>
                    <label for="priority" class="block text-sm font-semibold text-neutral-300">{{ __('Priority') }}</label>
                    <input id="priority" name="priority" type="number" min="1" max="10" value="{{ old('priority', 1) }}" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                    <p class="mt-2 text-xs text-neutral-500">{{ __('Lower numbers are seated first.') }}</p>
                </div>
            </div>
            <div>
                <label for="area_name" class="block text-sm font-semibold text-neutral-300">{{ __('Area Name') }}</label>
                <input id="area_name" name="area_name" type="text" value="{{ old('area_name') }}" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30" placeholder="{{ __('Main hall, terrace, private room...') }}">
            </div>
            <div>
                <label for="status" class="block text-sm font-semibold text-neutral-300">{{ __('Status') }}</label>
                <select id="status" name="status" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                    <option value="available" @selected(old('status') === 'available')>{{ __('Available') }}</option>
                    <option value="unavailable" @selected(old('status') === 'unavailable')>{{ __('Unavailable') }}</option>
                </select>
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.tables.index') }}" class="rounded-full border border-neutral-700 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-300 transition hover:border-neutral-500 hover:text-white">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-full bg-amber-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-amber-400">{{ __('Save Table') }}</button>
            </div>
        </form>
    </section>
@endsection