@extends('layouts.admin')

@section('title', __('Guests'))
@section('page-title', __('Guest Directory'))

@section('content')
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-white">{{ __('Guest Profiles') }}</h2>
            <p class="mt-1 text-sm text-neutral-500">{{ __('Search, filter, and analyse guests collected through reservations.') }}</p>
        </div>
        @php($canDeleteGuests = auth()->user()->isAdmin())
        @if($canDeleteGuests)
            <a href="{{ route('admin.guests.export') }}" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-emerald-400">{{ __('Export CSV') }}</a>
        @endif
    </div>

    <form method="GET" class="mt-6 flex flex-wrap items-center gap-3 rounded-3xl border border-neutral-800/60 bg-neutral-900/40 px-5 py-4">
        <label class="flex flex-col text-sm text-neutral-400">
            <span class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Search') }}</span>
            <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('Search by name, email, or phone...') }}" class="mt-2 min-w-[240px] rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-2 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
        </label>
        <button type="submit" class="ml-auto rounded-full bg-amber-500 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-amber-400">{{ __('Filter') }}</button>
    </form>

    @if($canDeleteGuests)
        <form id="guests-bulk-form" method="POST" action="{{ route('admin.guests.bulk-destroy') }}" class="mt-6 rounded-3xl border border-neutral-800/60 bg-neutral-900/40">
            @csrf
            @method('DELETE')

            <div class="flex items-center justify-between px-5 py-4 text-xs uppercase tracking-[0.3rem] text-neutral-500">
                <span>{{ __('Bulk actions') }}</span>
                <button type="submit"
                        data-bulk-delete="guests"
                        data-confirm="{{ __('Delete selected guests? Associated reservations will keep their history.') }}"
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
                                <label class="sr-only" for="guests-select-all">{{ __('Select guest') }}</label>
                                <input id="guests-select-all" type="checkbox" class="h-4 w-4 rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500/40" data-bulk-master="guests">
                            </th>
                            <th class="px-6 py-4 text-left">{{ __('Name') }}</th>
                            <th class="px-6 py-4 text-left">{{ __('Contact') }}</th>
                            <th class="px-6 py-4 text-left">{{ __('Location') }}</th>
                            <th class="px-6 py-4 text-left">{{ __('Last Reservation') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('Total Reservations') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-800/30">
                        @forelse($guests as $guest)
                            <tr class="hover:bg-neutral-900/70 transition">
                                <td class="px-5 py-4 align-top">
                                    <input type="checkbox"
                                           name="ids[]"
                                           value="{{ $guest->id }}"
                                           class="h-4 w-4 rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500/40"
                                           data-bulk-item="guests">
                                </td>
                                <td class="px-6 py-4 font-semibold text-white">{{ $guest->full_name }}</td>
                                <td class="px-6 py-4">
                                    <p class="text-neutral-300">{{ $guest->email }}</p>
                                    <p class="text-xs text-neutral-500">{{ $guest->phone }}</p>
                                    @if($guest->company)
                                        <p class="text-xs text-neutral-500">{{ $guest->company }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-neutral-300">{{ $guest->location ?? __('Not specified') }}</td>
                                <td class="px-6 py-4 text-neutral-300">{{ optional($guest->last_reservation_at)->format('d M Y H:i') ?? __('Never') }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-white">{{ $guest->reservations_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-neutral-500">{{ __('No guests found for your criteria.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    @else
        <div class="mt-6 overflow-hidden rounded-3xl border border-neutral-800/60 bg-neutral-900/40">
            <table class="min-w-full divide-y divide-neutral-800/40 text-sm">
                <thead class="bg-neutral-900/70 text-xs uppercase tracking-[0.25rem] text-neutral-500">
                    <tr>
                        <th class="px-6 py-4 text-left">{{ __('Name') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Contact') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Location') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Last Reservation') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Total Reservations') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/30">
                    @forelse($guests as $guest)
                        <tr class="hover:bg-neutral-900/70 transition">
                            <td class="px-6 py-4 font-semibold text-white">{{ $guest->full_name }}</td>
                            <td class="px-6 py-4">
                                <p class="text-neutral-300">{{ $guest->email }}</p>
                                <p class="text-xs text-neutral-500">{{ $guest->phone }}</p>
                                @if($guest->company)
                                    <p class="text-xs text-neutral-500">{{ $guest->company }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-neutral-300">{{ $guest->location ?? __('Not specified') }}</td>
                            <td class="px-6 py-4 text-neutral-300">{{ optional($guest->last_reservation_at)->format('d M Y H:i') ?? __('Never') }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-white">{{ $guest->reservations_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-neutral-500">{{ __('No guests found for your criteria.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <div class="mt-6">{{ $guests->links() }}</div>
@endsection

@if($canDeleteGuests)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('guests-bulk-form');
                if (!form) return;

                const master = form.querySelector('[data-bulk-master="guests"]');
                const items = Array.from(form.querySelectorAll('[data-bulk-item="guests"]'));
                const button = form.querySelector('[data-bulk-delete="guests"]');

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

                items.forEach((checkbox) => checkbox.addEventListener('change', update));

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
@endif
