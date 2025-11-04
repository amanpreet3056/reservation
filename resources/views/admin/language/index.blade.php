@extends('layouts.admin')

@section('title', __('Language Preferences'))
@section('page-title', __('Language Preferences'))

@section('content')
    <section class="max-w-2xl rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-8">
        <h2 class="text-xl font-semibold text-white">{{ __('Choose your interface language') }}</h2>
        <p class="mt-2 text-sm text-neutral-400">{{ __('Switch between English and Dutch. Your selection is stored for future visits.') }}</p>

        <form method="POST" action="{{ route('admin.language.update') }}" class="mt-6 space-y-4">
            @csrf
            <div class="space-y-3">
                @foreach($availableLocales as $code => $label)
                    <label class="flex items-center justify-between rounded-2xl border border-neutral-800 bg-neutral-900/40 px-4 py-3 text-sm text-neutral-200 transition hover:border-amber-400">
                        <div class="flex flex-col">
                            <span class="font-semibold text-white">{{ $label }}</span>
                            <span class="text-xs text-neutral-500">{{ strtoupper($code) }}</span>
                        </div>
                        <input type="radio" name="locale" value="{{ $code }}" @checked($currentLocale === $code) class="h-4 w-4 border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500/40">
                    </label>
                @endforeach
            </div>
            <button type="submit" class="rounded-full bg-amber-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-amber-400">{{ __('Save language') }}</button>
        </form>
    </section>
@endsection