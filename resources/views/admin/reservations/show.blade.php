@extends('layouts.admin')

@section('title', __('Reservation Details'))
@section('page-title', __('Reservation #:ref', ['ref' => $reservation->reference]))

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <section class="rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-6">
                <h2 class="text-lg font-semibold text-white">{{ __('Reservation Overview') }}</h2>
                <dl class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-neutral-500 uppercase tracking-[0.2rem] text-xs">{{ __('Status') }}</dt>
                        <dd class="mt-1 text-white font-semibold">{{ $reservation->status_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500 uppercase tracking-[0.2rem] text-xs">{{ __('Date & Time') }}</dt>
                        <dd class="mt-1 text-neutral-200">{{ $reservation->reservation_date?->format('d M Y') }} - {{ $reservation->reservation_time?->format('H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500 uppercase tracking-[0.2rem] text-xs">{{ __('Party Size') }}</dt>
                        <dd class="mt-1 text-neutral-200">{{ $reservation->number_of_people }} - {{ \Illuminate\Support\Str::plural('guest', $reservation->number_of_people) }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500 uppercase tracking-[0.2rem] text-xs">{{ __('Visit Purpose') }}</dt>
                        <dd class="mt-1 text-neutral-200">{{ $reservation->visit_purpose ? (config('reservations.visit_purposes')[$reservation->visit_purpose] ?? $reservation->visit_purpose) : __('Not provided') }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500 uppercase tracking-[0.2rem] text-xs">{{ __('Occasion') }}</dt>
                        <dd class="mt-1 text-neutral-200">{{ $reservation->occasion ?? __('Not specified') }}</dd>
                    </div>
                    <div>
                        <dt class="text-neutral-500 uppercase tracking-[0.2rem] text-xs">{{ __('Assigned Table') }}</dt>
                        <dd class="mt-1 text-neutral-200">{{ $reservation->table?->name ?? __('Not assigned yet') }}</dd>
                    </div>
                </dl>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <h3 class="text-xs uppercase tracking-[0.2rem] text-neutral-500">{{ __('Guest Details') }}</h3>
                        <p class="mt-2 text-white font-semibold">{{ $reservation->guest?->full_name }}</p>
                        <p class="text-neutral-400">{{ $reservation->guest?->email }}</p>
                        <p class="text-neutral-400">{{ $reservation->guest?->phone }}</p>
                    </div>
                    <div>
                        <h3 class="text-xs uppercase tracking-[0.2rem] text-neutral-500">{{ __('Preferences') }}</h3>
                        <p class="mt-2 text-neutral-300">{{ __('Allergies: :data', ['data' => $reservation->allergies ? implode(', ', $reservation->allergies) : __('None listed')]) }}</p>
                        <p class="text-neutral-300">{{ __('Diet: :data', ['data' => $reservation->diets ? implode(', ', $reservation->diets) : __('None listed')]) }}</p>
                    </div>
                </div>

                @if($reservation->message)
                    <div class="mt-6">
                        <h3 class="text-xs uppercase tracking-[0.2rem] text-neutral-500">{{ __('Guest Message') }}</h3>
                        <p class="mt-2 rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-4 text-sm text-neutral-200">{{ $reservation->message }}</p>
                    </div>
                @endif

                @if($reservation->reservation_notes)
                    <div class="mt-4">
                        <h3 class="text-xs uppercase tracking-[0.2rem] text-neutral-500">{{ __('Internal Notes') }}</h3>
                        <p class="mt-2 rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-4 text-sm text-neutral-200">{{ $reservation->reservation_notes }}</p>
                    </div>
                @endif
            </section>

            <section class="rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-6">
                <h2 class="text-lg font-semibold text-white">{{ __('Activity History') }}</h2>
                <ul class="mt-4 space-y-4 text-sm">
                    @forelse($reservation->history as $entry)
                        <li class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-4">
                            <div class="flex justify-between">
                                <p class="font-semibold text-white">{{ \Illuminate\Support\Str::headline($entry->action) }}</p>
                                <span class="text-xs text-neutral-500">{{ $entry->created_at->format('d M Y H:i') }}</span>
                            </div>
                            @if($entry->description)
                                <p class="mt-2 text-neutral-300">{{ $entry->description }}</p>
                            @endif
                            @if($entry->performer)
                                <p class="mt-2 text-xs text-neutral-500">{{ __('Performed by :name', ['name' => $entry->performer->name]) }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="rounded-2xl border border-neutral-800/60 bg-neutral-900/60 p-4 text-neutral-500">{{ __('No activity recorded yet.') }}</li>
                    @endforelse
                </ul>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-6">
                <h2 class="text-lg font-semibold text-white">{{ __('Update Reservation') }}</h2>
                <form method="POST" action="{{ route('admin.reservations.status.update', $reservation) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="status" class="block text-sm font-semibold text-neutral-300">{{ __('Status') }}</label>
                        <select id="status" name="status" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @foreach($statusOptions as $status)
                                <option value="{{ $status->value }}" @selected(old('status', $reservation->status->value) === $status->value)>{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    <div data-cancel-reason class='hidden'>
                        <label for='cancel_reason' class='block text-sm font-semibold text-neutral-300'>{{ __('Cancellation email message') }}</label>
                        <textarea id='cancel_reason' name='cancel_reason' rows='3' class='mt-2 w-full rounded-2xl border border-rose-500/40 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/40' placeholder='{{ __('Let the guest know why their reservation was cancelled') }}'>{{ old('cancel_reason', $reservation->cancel_reason) }}</textarea>
                        <p class='mt-2 text-xs text-neutral-500'>{{ __('This message is included in the cancellation email sent to the guest.') }}</p>
                    </div>
                    </div>
                    <div>
                        <label for="restaurant_table_id" class="block text-sm font-semibold text-neutral-300">{{ __('Assign table') }}</label>
                        <select id="restaurant_table_id" name="restaurant_table_id" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            <option value="">{{ __('No table assigned') }}</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}" @selected(old('restaurant_table_id', $reservation->restaurant_table_id) == $table->id)>
                                    {{ $table->name }} - {{ $table->seats }} {{ \Illuminate\Support\Str::plural('seat', $table->seats) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="reservation_notes" class="block text-sm font-semibold text-neutral-300">{{ __('Internal Notes') }}</label>
                        <textarea id="reservation_notes" name="reservation_notes" rows="3" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30" placeholder="{{ __('Share an update for the team') }}">{{ old('reservation_notes', $reservation->reservation_notes) }}</textarea>
                    </div>
                    <button type="submit" class="inline-flex items-center rounded-full bg-amber-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-amber-400">{{ __('Save Changes') }}</button>
                </form>
            </section>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const status = document.getElementById('status');
            const cancelSection = document.querySelector('[data-cancel-reason]');

            const toggleCancelSection = () => {
                if (!status || !cancelSection) return;
                if (status.value === '{{ \App\Enums\ReservationStatus::Cancelled->value }}') {
                    cancelSection.classList.remove('hidden');
                } else {
                    cancelSection.classList.add('hidden');
                }
            };

            status?.addEventListener('change', toggleCancelSection);
            toggleCancelSection();
        });
    </script>
@endsection




