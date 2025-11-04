@extends('layouts.admin')

@section('title', __('Settings'))
@section('page-title', __('Settings'))

@section('content')
    <div class="rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-12 text-center text-neutral-400">
        <p class="text-sm uppercase tracking-[0.3rem] text-neutral-500">{{ __('Module in progress') }}</p>
        <h2 class="mt-4 text-2xl font-semibold text-white">{{ __('This section is being finalised.') }}</h2>
        <p class="mt-3 text-sm text-neutral-400">{{ __('Full functionality will be available shortly.') }}</p>
    </div>
@endsection