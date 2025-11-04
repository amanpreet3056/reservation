@extends('layouts.admin')

@section('title', __('Reports'))
@section('page-title', __('Reports & Insights'))

@section('content')
    <form method="GET" class="flex flex-wrap items-center gap-4 rounded-3xl border border-neutral-800/60 bg-neutral-900/40 px-5 py-4">
        <label class="text-sm text-neutral-400">
            <span class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Range') }}</span>
            <select name="range" class="mt-2 rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-2 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                <option value="weekly" @selected($range === 'weekly')>{{ __('This week') }}</option>
                <option value="monthly" @selected($range === 'monthly')>{{ __('This month') }}</option>
                <option value="yearly" @selected($range === 'yearly')>{{ __('This year') }}</option>
                <option value="custom" @selected($range === 'custom')>{{ __('Custom') }}</option>
            </select>
        </label>
        @if($range === 'custom')
            <label class="text-sm text-neutral-400">
                <span class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Start date') }}</span>
                <input type="date" name="start_date" value="{{ request('start_date', $start->format('Y-m-d')) }}" class="mt-2 rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-2 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </label>
            <label class="text-sm text-neutral-400">
                <span class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('End date') }}</span>
                <input type="date" name="end_date" value="{{ request('end_date', $end->format('Y-m-d')) }}" class="mt-2 rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-2 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </label>
        @endif
        <button type="submit" class="ml-auto rounded-full bg-amber-500 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-amber-400">{{ __('Update') }}</button>
    </form>

    <p class="mt-4 text-xs uppercase tracking-[0.3rem] text-neutral-500">{{ __('Reporting from :start to :end', ['start' => $start->format('d M Y'), 'end' => $end->format('d M Y')]) }}</p>

    <div class="mt-6 grid gap-6 lg:grid-cols-5">
        <div class="rounded-3xl border border-neutral-800/60 bg-neutral-900/50 p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Total Reservations') }}</p>
            <p class="mt-3 text-3xl font-semibold text-white">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-3xl border border-neutral-800/60 bg-neutral-900/50 p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Confirmed') }}</p>
            <p class="mt-3 text-3xl font-semibold text-emerald-300">{{ $stats['confirmed'] }}</p>
        </div>
        <div class="rounded-3xl border border-neutral-800/60 bg-neutral-900/50 p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Pending') }}</p>
            <p class="mt-3 text-3xl font-semibold text-amber-300">{{ $stats['pending'] }}</p>
        </div>
        <div class="rounded-3xl border border-neutral-800/60 bg-neutral-900/50 p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Cancelled') }}</p>
            <p class="mt-3 text-3xl font-semibold text-rose-300">{{ $stats['cancelled'] }}</p>
        </div>
        <div class="rounded-3xl border border-neutral-800/60 bg-neutral-900/50 p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Guests Served') }}</p>
            <p class="mt-3 text-3xl font-semibold text-white">{{ $stats['guests'] }}</p>
            <p class="mt-1 text-xs text-neutral-500">{{ __('Average party size: :size', ['size' => $stats['average_party']]) }}</p>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <section class="rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-6">
            <h3 class="text-lg font-semibold text-white">{{ __('Reservations by Source') }}</h3>
            <ul class="mt-4 space-y-3 text-sm">
                @forelse($bySource as $source => $total)
                    <li class="flex items-center justify-between rounded-2xl border border-neutral-800/60 bg-neutral-900/60 px-4 py-3">
                        <span class="text-neutral-300">{{ ucfirst(str_replace('_', ' ', $source)) }}</span>
                        <span class="font-semibold text-white">{{ $total }}</span>
                    </li>
                @empty
                    <li class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 px-4 py-6 text-center text-neutral-500">{{ __('No reservations recorded in this period.') }}</li>
                @endforelse
            </ul>
        </section>

        <section class="rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-6">
            <h3 class="text-lg font-semibold text-white">{{ __('Daily Trend') }}</h3>
            <div class="mt-4 space-y-3">
                @forelse($dailyTrend as $date => $count)
                    <div>
                        <div class="flex items-center justify-between text-sm text-neutral-300">
                            <span>{{ \Illuminate\Support\Carbon::parse($date)->format('d M') }}</span>
                            <span class="font-semibold text-white">{{ $count }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-neutral-800">
                            <div class="h-2 rounded-full bg-emerald-500" style="width: {{ min(100, $stats['total'] ? ($count / max($dailyTrend) * 100) : 0) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 px-4 py-6 text-center text-neutral-500">{{ __('No activity for this range.') }}</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection