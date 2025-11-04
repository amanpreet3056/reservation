@extends('layouts.admin')

@section('title', __('Tables'))
@section('page-title', __('Restaurant Tables'))

@section('content')
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-white">{{ __('Seating Map') }}</h2>
            <p class="mt-1 text-sm text-neutral-500">{{ __('Organise restaurant tables, seating capacity, and service priority.') }}</p>
        </div>
        <a href="{{ route('admin.tables.create') }}" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-emerald-400">{{ __('Add Table') }}</a>
    </div>

    <form method="GET" class="mt-6 flex flex-wrap items-center gap-3 rounded-3xl border border-neutral-800/60 bg-neutral-900/40 px-5 py-4">
        <label class="flex flex-col text-sm text-neutral-400">
            <span class="text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-500">{{ __('Search') }}</span>
            <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('Search by name or area...') }}" class="mt-2 min-w-[220px] rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-2 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
        </label>
        <button type="submit" class="ml-auto rounded-full bg-amber-500 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-amber-400">{{ __('Filter') }}</button>
    </form>

    <form id="tables-bulk-form" method="POST" action="{{ route('admin.tables.bulk-destroy') }}" class="mt-6 rounded-3xl border border-neutral-800/60 bg-neutral-900/40">
        @csrf
        @method('DELETE')

        <div class="flex items-center justify-between px-5 py-4 text-xs uppercase tracking-[0.3rem] text-neutral-500">
            <span>{{ __('Bulk actions') }}</span>
            <button type="submit"
                    data-bulk-delete="tables"
                    data-confirm="{{ __('Delete selected tables? Tables with upcoming reservations will be skipped.') }}"
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
                            <label class="sr-only" for="tables-select-all">{{ __('Select table') }}</label>
                            <input id="tables-select-all" type="checkbox" class="h-4 w-4 rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500/40" data-bulk-master="tables">
                        </th>
                        <th class="px-6 py-4 text-left">{{ __('Table') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Seats') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Area') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Priority') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Status') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/30">
                    @forelse($tables as $table)
                        <tr class="hover:bg-neutral-900/70 transition">
                            <td class="px-5 py-4 align-top">
                                <input type="checkbox"
                                       name="ids[]"
                                       value="{{ $table->id }}"
                                       class="h-4 w-4 rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500/40"
                                       data-bulk-item="tables">
                            </td>
                            <td class="px-6 py-4 font-semibold text-white">{{ $table->name }}</td>
                            <td class="px-6 py-4 text-neutral-300">{{ $table->seats }}</td>
                            <td class="px-6 py-4 text-neutral-300">{{ $table->area_name ?? __('Not specified') }}</td>
                            <td class="px-6 py-4 text-neutral-300">{{ $table->priority }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2rem] @class([
                                    'bg-emerald-500/10 text-emerald-300 border border-emerald-500/40' => $table->status === 'available',
                                    'bg-rose-500/10 text-rose-300 border border-rose-500/40' => $table->status === 'unavailable',
                                ])">{{ ucfirst($table->status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.tables.edit', $table) }}" class="inline-flex items-center rounded-full border border-neutral-700 px-3 py-2 text-[0.65rem] font-semibold uppercase tracking-[0.25rem] text-neutral-300 transition hover:border-neutral-500 hover:text-white">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('admin.tables.destroy', $table) }}" onsubmit="return confirm('{{ __('Are you sure you want to remove this table?') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-full border border-rose-500/60 px-3 py-2 text-[0.65rem] font-semibold uppercase tracking-[0.25rem] text-rose-300 transition hover:bg-rose-500/10">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-neutral-500">{{ __('No tables configured yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('tables-bulk-form');
            if (!form) return;

            const master = form.querySelector('[data-bulk-master="tables"]');
            const items = Array.from(form.querySelectorAll('[data-bulk-item="tables"]'));
            const button = form.querySelector('[data-bulk-delete="tables"]');

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

    <div class="mt-6">{{ $tables->links() }}</div>
@endsection
