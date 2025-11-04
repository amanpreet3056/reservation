<div data-booking-modal class="hidden">
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm px-4">
        <div class="relative w-full max-w-xl rounded-3xl border border-neutral-800/60 bg-neutral-950 p-8 shadow-2xl">
            <button type="button" data-close-booking class="absolute right-4 top-4 text-neutral-500 transition hover:text-white">
                <span class="sr-only">{{ __('Close') }}</span>
                &times;
            </button>

            <div data-mode-section="pause">
                <h2 class="text-2xl font-semibold text-white">{{ __('Pause Online Reservations') }}</h2>
                <p class="mt-2 text-sm text-neutral-400">{{ __('Let guests know when reservations are unavailable and provide a custom note that will appear on the booking form.') }}</p>

                <form method="POST" action="{{ route('admin.booking-closures.store') }}" class="mt-6 space-y-5">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-neutral-300" for="closure_starts_at">{{ __('Start Time') }}</label>
                        <input id="closure_starts_at" name="starts_at" type="datetime-local" value="{{ now()->format('Y-m-d\TH:i') }}" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-neutral-300" for="closure_ends_at">{{ __('End Time (optional)') }}</label>
                        <input id="closure_ends_at" name="ends_at" type="datetime-local" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                        <p class="mt-2 text-xs text-neutral-500">{{ __('Leave blank to pause indefinitely until manually resumed.') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-neutral-300" for="closure_message">{{ __('Guest Message') }}</label>
                        <textarea id="closure_message" name="message" rows="3" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30" placeholder="{{ __('Share why reservations are paused...') }}"></textarea>
                        <p class="mt-2 text-xs text-neutral-500">{{ __('Guests will also see “Kindly Call on 9814203056 for more information.” appended automatically.') }}</p>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <button type="button" data-close-booking class="inline-flex items-center rounded-full border border-neutral-700 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-300 transition hover:border-neutral-500 hover:text-white">{{ __('Cancel') }}</button>
                        <button type="submit" class="inline-flex items-center rounded-full bg-amber-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-amber-400">{{ __('Pause Now') }}</button>
                    </div>
                </form>
            </div>

            <div data-mode-section="resume" class="hidden">
                <h2 class="text-2xl font-semibold text-white">{{ __('Resume Online Reservations') }}</h2>
                <p class="mt-2 text-sm text-neutral-400">{{ __('Confirm to immediately reopen online reservations for guests.') }}</p>

                <form method="POST" action="{{ route('admin.booking-closures.resume') }}" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="closure_id" data-closure-field>
                    <div class="rounded-2xl border border-emerald-500/40 bg-emerald-500/10 p-4 text-sm text-emerald-200">
                        {{ __('Guests will once again see the standard booking form.') }}
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <button type="button" data-close-booking class="inline-flex items-center rounded-full border border-neutral-700 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-300 transition hover:border-neutral-500 hover:text-white">{{ __('Not Now') }}</button>
                        <button type="submit" class="inline-flex items-center rounded-full bg-emerald-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-emerald-400">{{ __('Resume Booking') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>