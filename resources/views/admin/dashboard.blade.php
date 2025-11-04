@extends('layouts.admin')

@section('title', __('Dashboard'))
@section('page-title', __('Today'))

@section('content')
    <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-6 space-y-6">
            <section>
                <header class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-white">{{ __('Pending Reservations') }}</h2>
                        <p class="text-sm text-neutral-500">{{ __('All reservations awaiting confirmation.') }}</p>
                    </div>
                    <a href="{{ route('admin.reservations.index') }}" class="text-xs uppercase tracking-[0.3rem] text-neutral-500 hover:text-amber-300">{{ __('Manage') }}</a>
                </header>

                <div class="mt-6 rounded-2xl border border-neutral-800/60 bg-neutral-900/60">
                    <div class="divide-y divide-neutral-800/60">
                        @forelse ($pendingReservations as $reservation)
                            <div class="flex flex-wrap items-center justify-between gap-4 p-6 hover:bg-neutral-900/80 transition">
                                <div class="min-w-0">
                                    <p class="text-base font-semibold text-white truncate">
                                        {{ $reservation->guest?->full_name ?? __('Guest') }}
                                        <span class="text-neutral-400">&bull; {{ $reservation->number_of_people }} {{ \Illuminate\Support\Str::plural(__('guest'), $reservation->number_of_people) }}</span>
                                    </p>
                                    <p class="mt-1 text-sm text-neutral-400">
                                        {{ optional($reservation->reservation_date)->format('D • M j') ?? __('Date TBD') }}
                                        &bull; {{ $reservation->reservation_time?->format('g:i A') ?? __('Time TBD') }}
                                        @if($reservation->table?->name)
                                            &bull; {{ $reservation->table->name }}
                                        @endif
                                    </p>
                                    @if($reservation->occasion)
                                        <p class="mt-1 text-xs uppercase tracking-[0.2rem] text-neutral-600">{{ $reservation->occasion }}</p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-4">
                                    <form method="POST" action="{{ route('admin.reservations.status.update', $reservation) }}" class="inline-flex items-center" data-status-form>
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="reservation_notes" value="{{ $reservation->reservation_notes }}">
                                        <input type="hidden" name="restaurant_table_id" value="{{ $reservation->restaurant_table_id }}">
                                        <input type="hidden" name="cancel_reason" value="">
                                        <select
                                            name="status"
                                            class="appearance-none rounded-full border border-neutral-700 bg-neutral-900 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-200 transition hover:border-neutral-500 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                                            data-status-select
                                            data-current-status="{{ $reservation->status->value }}"
                                        >
                                            @foreach (App\Enums\ReservationStatus::cases() as $statusOption)
                                                @continue($statusOption === App\Enums\ReservationStatus::AwaitingDetails)
                                                <option value="{{ $statusOption->value }}" @selected($reservation->status === $statusOption)>
                                                    {{ $statusOption->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </form>
                                    <a href="{{ route('admin.reservations.show', $reservation) }}" class="text-sm font-semibold text-amber-300 transition hover:text-amber-200">
                                        {{ __('View') }}
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center text-neutral-500">
                                {{ __('No pending reservations found.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>

            <section>
                <header class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Today & Upcoming') }}</h3>
                        <p class="text-xs text-neutral-600">
                            {{ __('Awaiting confirmation today: :today • Upcoming: :upcoming', ['today' => $pendingTodayCount, 'upcoming' => $pendingUpcomingCount]) }}
                        </p>
                    </div>
                </header>

                <div class="mt-4 rounded-2xl border border-neutral-800/60 bg-neutral-900/60">
                    <div class="divide-y divide-neutral-800/60">
                        @forelse ($upcomingReservations as $reservation)
                            <div class="flex flex-wrap items-center justify-between gap-4 p-5 hover:bg-neutral-900/80 transition">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-white truncate">
                                        {{ $reservation->guest?->full_name ?? __('Guest') }}
                                        <span class="text-neutral-400">&bull; {{ $reservation->number_of_people }} {{ \Illuminate\Support\Str::plural(__('guest'), $reservation->number_of_people) }}</span>
                                    </p>
                                    <p class="mt-1 text-xs text-neutral-400">
                                        {{ optional($reservation->reservation_date)->format('D • M j') ?? __('Date TBD') }}
                                        &bull; {{ $reservation->reservation_time?->format('g:i A') ?? __('Time TBD') }}
                                    </p>
                                </div>
                                <a href="{{ route('admin.reservations.show', $reservation) }}" class="text-xs font-semibold text-amber-300 transition hover:text-amber-200">
                                    {{ __('Open details') }}
                                </a>
                            </div>
                        @empty
                            <div class="p-6 text-center text-neutral-500">
                                {{ __('No upcoming pending reservations.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>

        <div class="rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-6">
            <header class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-white">{{ __('Reservation Summary') }}</h2>
                    <p class="text-sm text-neutral-500">{{ __('Live overview across all reservations') }}</p>
                </div>
                <div class="text-right text-sm text-neutral-400">
                    <p>{{ __('Seated guests: :count', ['count' => $totals['seated']]) }}</p>
                    <p>{{ __('Pending confirmations: :count', ['count' => $totals['pending']]) }}</p>
                </div>
            </header>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-5">
                    <p class="text-xs uppercase tracking-[0.3rem] text-neutral-500">{{ __('Total Reservations') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ $totals['total'] }}</p>
                </div>
                <div class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-5">
                    <p class="text-xs uppercase tracking-[0.3rem] text-neutral-500">{{ __('Pending') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-amber-300">{{ $totals['pending'] }}</p>
                </div>
                <div class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-5">
                    <p class="text-xs uppercase tracking-[0.3rem] text-neutral-500">{{ __('Confirmed') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-emerald-300">{{ $totals['confirmed'] }}</p>
                </div>
                <div class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-5">
                    <p class="text-xs uppercase tracking-[0.3rem] text-neutral-500">{{ __('Cancelled') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-rose-300">{{ $totals['cancelled'] }}</p>
                </div>
                <div class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-5 sm:col-span-2">
                    <p class="text-xs uppercase tracking-[0.3rem] text-neutral-500">{{ __('Average Party Size') }}</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ number_format($totals['average_party_size'], 1) }}</p>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-status-select]').forEach((select) => {
                select.addEventListener('change', (event) => {
                    const target = event.currentTarget;
                    const form = target.closest('[data-status-form]');
                    if (!form) {
                        return;
                    }

                    const previous = target.dataset.currentStatus;
                    const selected = target.value;
                    const cancelReasonInput = form.querySelector('input[name="cancel_reason"]');

                    if (selected === 'cancelled') {
                        const reason = window.prompt('{{ __('Please provide a cancellation reason (optional).') }}', '');

                        if (reason === null) {
                            target.value = previous;
                            return;
                        }

                        if (cancelReasonInput) {
                            cancelReasonInput.value = reason.trim() !== '' ? reason.trim() : '{{ __('Cancelled via dashboard quick action.') }}';
                        }
                    } else if (cancelReasonInput) {
                        cancelReasonInput.value = '';
                    }

                    target.dataset.currentStatus = selected;
                    form.submit();
                });
            });
        });
    </script>
@endpush
