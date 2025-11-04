@extends('layouts.admin')

@section('title', __('Reservations'))
@section('page-title', __('Reservations'))

@section('content')
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-white">{{ __('Manage Reservations') }}</h2>
            <p class="mt-1 text-sm text-neutral-500">{{ __('Monitor, confirm, and organize every table booking from this unified dashboard.') }}</p>
        </div>
        <a href="{{ route('admin.reservations.create') }}" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-emerald-400">
            {{ __('Add Reservation') }}
        </a>
    </div>

    <div class="mt-8 space-y-4">
        <div class="flex flex-wrap items-center gap-2">
            @php
                $filters = [
                    'all' => __('All'),
                    'confirmed' => __('Confirmed'),
                    'pending' => __('Pending'),
                    'upcoming' => __('Upcoming'),
                    'cancelled' => __('Cancelled'),
                ];
            @endphp
            @foreach($filters as $key => $label)
                <a href="{{ route('admin.reservations.index', array_merge(request()->only('date'), ['status' => $key === 'all' ? null : $key])) }}"
                   class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] transition @if($filterActive === $key) border-amber-400 bg-amber-500/10 text-amber-300 @else border-neutral-800 text-neutral-400 hover:border-neutral-600 hover:text-white @endif">
                    <span>{{ $label }}</span>
                    <span class="rounded-full bg-neutral-800 px-2 py-0.5 text-[0.6rem] font-semibold text-neutral-300">{{ $totals[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <form method="GET" class="flex flex-wrap items-center gap-4 rounded-3xl border border-neutral-800/60 bg-neutral-900/40 px-5 py-4">
            <div>
                <label for="filter-date" class="block text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Filter by date') }}</label>
                <input id="filter-date" type="date" name="date" value="{{ $selectedDate }}" class="mt-2 rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-2 text-sm text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="inline-flex items-center rounded-full bg-amber-500 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-amber-400">{{ __('Apply') }}</button>
                <a href="{{ route('admin.reservations.index') }}" class="inline-flex items-center rounded-full border border-neutral-700 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-300 transition hover:border-neutral-500 hover:text-white">{{ __('Reset') }}</a>
            </div>
        </form>
    </div>

    <form id="reservations-bulk-form" method="POST" action="{{ route('admin.reservations.bulk-destroy') }}" class="mt-6 rounded-3xl border border-neutral-800/60 bg-neutral-900/40">
        @csrf
        @method('DELETE')

        <div class="flex items-center justify-between px-5 py-4 text-xs uppercase tracking-[0.3rem] text-neutral-500">
            <span>{{ __('Bulk actions') }}</span>
            <button type="submit"
                    data-bulk-delete="reservations"
                    data-confirm="{{ __('Delete selected reservations? This action cannot be undone.') }}"
                    class="inline-flex items-center rounded-full border border-rose-500/60 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-rose-300 transition hover:bg-rose-500/10 disabled:cursor-not-allowed disabled:opacity-50"
                    disabled>
                {{ __('Delete selected') }}
            </button>
        </div>

        <div class="overflow-hidden border-t border-neutral-800/60">
            <table class="min-w-full divide-y divide-neutral-800/40 text-sm">
                <thead class="bg-neutral-900/70 text-xs uppercase tracking-[0.25rem] text-neutral-500">
                    <tr>
                        <th class="px-5 py-4 text-left">
                            <label class="sr-only" for="reservations-select-all">{{ __('Select reservation') }}</label>
                            <input id="reservations-select-all" type="checkbox" class="h-4 w-4 rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500/40" data-bulk-master="reservations">
                        </th>
                        <th class="px-6 py-4 text-left">{{ __('Date') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Time') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Guest') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Party') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Status') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Source') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Table') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/30">
                    @forelse($reservations as $reservation)
                        <tr class="hover:bg-neutral-900/70 transition">
                            <td class="px-5 py-4 align-top">
                                <input type="checkbox"
                                       name="ids[]"
                                       value="{{ $reservation->id }}"
                                       class="h-4 w-4 rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500/40"
                                       data-bulk-item="reservations">
                            </td>
                            <td class="px-6 py-4 text-neutral-300">{{ $reservation->reservation_date?->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-neutral-300">{{ $reservation->reservation_time?->format('H:i') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-white">{{ $reservation->guest?->full_name ?? __('Guest') }}</span>
                                    <span class="text-xs text-neutral-500">{{ $reservation->guest?->email }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-neutral-300">{{ $reservation->number_of_people }} {{ \Illuminate\Support\Str::plural('guest', $reservation->number_of_people) }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2rem]
                                    @class([
                                        'bg-emerald-500/10 text-emerald-300 border border-emerald-500/40' => $reservation->status === App\Enums\ReservationStatus::Confirmed,
                                        'bg-amber-500/10 text-amber-300 border border-amber-500/40' => $reservation->status === App\Enums\ReservationStatus::Pending,
                                        'bg-rose-500/10 text-rose-300 border border-rose-500/40' => $reservation->status === App\Enums\ReservationStatus::Cancelled,
                                        'bg-neutral-800 text-neutral-300 border border-neutral-700' => $reservation->status === App\Enums\ReservationStatus::AwaitingDetails,
                                    ])">
                                    {{ $reservation->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-neutral-300">{{ ucfirst($reservation->source) }}</td>
                            <td class="px-6 py-4 text-neutral-300">{{ $reservation->table?->name ?? __('Not assigned') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @if($reservation->status === App\Enums\ReservationStatus::Pending)
                                        <form method="POST" action="{{ route('admin.reservations.status.update', $reservation) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ App\Enums\ReservationStatus::Confirmed->value }}">
                                            <input type="hidden" name="reservation_notes" value="">
                                            <button type="submit" class="inline-flex items-center rounded-full bg-emerald-500 px-3 py-2 text-[0.65rem] font-semibold uppercase tracking-[0.25rem] text-neutral-900 transition hover:bg-emerald-400">{{ __('Confirm') }}</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('admin.reservations.show', $reservation) }}" class="inline-flex items-center rounded-full border border-neutral-700 px-3 py-2 text-[0.65rem] font-semibold uppercase tracking-[0.25rem] text-neutral-300 transition hover:border-neutral-500 hover:text-white">{{ __('View') }}</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-10 text-center text-neutral-500">{{ __('No reservations matched your filters.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('reservations-bulk-form');
            if (!form) return;

            const master = form.querySelector('[data-bulk-master="reservations"]');
            const items = Array.from(form.querySelectorAll('[data-bulk-item="reservations"]'));
            const button = form.querySelector('[data-bulk-delete="reservations"]');

            const update = () => {
                const eligible = items.filter((checkbox) => !checkbox.disabled);
                const checked = eligible.filter((checkbox) => checkbox.checked);

                if (button) {
                    button.disabled = checked.length === 0;
                }

                if (master) {
                    master.indeterminate = checked.length > 0 && checked.length < eligible.length;
                    master.checked = eligible.length > 0 && checked.length === eligible.length;
                }
            };

            if (master) {
                master.addEventListener('change', () => {
                    items.forEach((checkbox) => {
                        if (!checkbox.disabled) {
                            checkbox.checked = master.checked;
                        }
                    });
                    update();
                });
            }

            items.forEach((checkbox) => {
                checkbox.addEventListener('change', update);
            });

            if (button) {
                button.addEventListener('click', (event) => {
                    const message = button.dataset.confirm;
                    if (message && !window.confirm(message)) {
                        event.preventDefault();
                    }
                });
            }

            update();
        });
    </script>
@endpush

    <div class="mt-6">{{ $reservations->links() }}</div>
@endsection
