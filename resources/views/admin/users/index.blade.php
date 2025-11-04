@extends('layouts.admin')

@section('title', __('Users'))
@section('page-title', __('User Management'))

@section('content')
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-white">{{ __('Team Access') }}</h2>
            <p class="mt-1 text-sm text-neutral-500">{{ __('Invite managers and administrators to manage reservations securely.') }}</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-emerald-400">{{ __('Add User') }}</a>
    </div>

    @php($currentUserId = auth()->id())

    <form id="users-bulk-form" method="POST" action="{{ route('admin.users.bulk-destroy') }}" class="mt-6 rounded-3xl border border-neutral-800/60 bg-neutral-900/40">
        @csrf
        @method('DELETE')

        <div class="flex items-center justify-between px-5 py-4 text-xs uppercase tracking-[0.3rem] text-neutral-500">
            <span>{{ __('Bulk actions') }}</span>
            <button type="submit"
                    data-bulk-delete="users"
                    data-confirm="{{ __('Delete selected users? Active sessions will be terminated.') }}"
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
                            <label class="sr-only" for="users-select-all">{{ __('Select user') }}</label>
                            <input id="users-select-all" type="checkbox" class="h-4 w-4 rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500/40" data-bulk-master="users">
                        </th>
                        <th class="px-6 py-4 text-left">{{ __('Name') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Email') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Role') }}</th>
                        <th class="px-6 py-4 text-left">{{ __('Status') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-800/30">
                    @forelse($users as $user)
                        <tr class="hover:bg-neutral-900/70 transition">
                            <td class="px-5 py-4 align-top">
                                <input type="checkbox"
                                       name="ids[]"
                                       value="{{ $user->id }}"
                                       class="h-4 w-4 rounded border-neutral-700 bg-neutral-900 text-amber-500 focus:ring-amber-500/40"
                                       data-bulk-item="users"
                                       @if($user->id === $currentUserId) disabled @endif>
                            </td>
                            <td class="px-6 py-4 font-semibold text-white">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-neutral-300">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-neutral-300">{{ ucfirst($user->role) }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.2rem] @class([
                                    'bg-emerald-500/10 text-emerald-300 border border-emerald-500/40' => $user->is_active,
                                    'bg-neutral-800 text-neutral-300 border border-neutral-700' => !$user->is_active,
                                ])">{{ $user->is_active ? __('Active') : __('Inactive') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center rounded-full border border-neutral-700 px-3 py-2 text-[0.65rem] font-semibold uppercase tracking-[0.25rem] text-neutral-300 transition hover:border-neutral-500 hover:text-white">{{ __('Edit') }}</a>
                                    @if(auth()->id() !== $user->id)
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('{{ __('Are you sure you want to delete this user?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-full border border-rose-500/60 px-3 py-2 text-[0.65rem] font-semibold uppercase tracking-[0.25rem] text-rose-300 transition hover:bg-rose-500/10">{{ __('Delete') }}</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-neutral-500">{{ __('No users found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    <div class="mt-6">{{ $users->links() }}</div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('users-bulk-form');
            if (!form) return;

            const master = form.querySelector('[data-bulk-master="users"]');
            const items = Array.from(form.querySelectorAll('[data-bulk-item="users"]'));
            const button = form.querySelector('[data-bulk-delete="users"]');

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
