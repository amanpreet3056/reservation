@extends('layouts.admin')

@section('title', __('Add Reservation'))
@section('page-title', __('Add Reservation'))

@section('content')
    <section class="rounded-3xl border border-neutral-800/60 bg-neutral-900/40 p-8">
        <form method="POST" action="{{ route('admin.reservations.store') }}" class="space-y-8">
            @csrf
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-white">{{ __('Schedule') }}</h2>
                    <label class="block">
                        <span class="text-sm font-semibold text-neutral-300">{{ __('Date') }}</span>
                        <input type="date" name="reservation_date" value="{{ old('reservation_date', now()->format('Y-m-d')) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-neutral-300">{{ __('Time') }}</span>
                        <input type="time" name="reservation_time" value="{{ old('reservation_time', now()->format('H:i')) }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30" step="900">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-neutral-300">{{ __('Table') }}</span>
                        <select name="restaurant_table_id" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            <option value="">{{ __('No table assigned yet') }}</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}" @selected(old('restaurant_table_id') == $table->id)>{{ $table->name }} · {{ $table->seats }} {{ \Illuminate\Support\Str::plural('seat', $table->seats) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-neutral-300">{{ __('Number of people') }}</span>
                        <select name="number_of_people" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" @selected(old('number_of_people', 2) == $i)>{{ $i }}</option>
                            @endfor
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-neutral-300">{{ __('Source') }}</span>
                        <select name="source" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @foreach($sources as $value => $label)
                                <option value="{{ $value }}" @selected(old('source') == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-neutral-300">{{ __('Occasion') }}</span>
                        <select name="occasion" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            <option value="">{{ __('Select') }}</option>
                            @foreach($occasions as $value => $label)
                                <option value="{{ $value }}" @selected(old('occasion') == $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-neutral-300">{{ __('Reservation notes (optional)') }}</span>
                        <textarea name="reservation_notes" rows="3" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">{{ old('reservation_notes') }}</textarea>
                    </label>
                </div>
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-white">{{ __('Guest details') }}</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-semibold text-neutral-300">{{ __('First name') }}</span>
                            <input type="text" name="first_name" value="{{ old('first_name') }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                        </label>
                        <label class="block">
                            <span class="text-sm font-semibold text-neutral-300">{{ __('Last name') }}</span>
                            <input type="text" name="last_name" value="{{ old('last_name') }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                        </label>
                    </div>
                    <label class="block">
                        <span class="text-sm font-semibold text-neutral-300">{{ __('Email (optional)') }}</span>
                        <input type="email" name="email" value="{{ old('email') }}" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-neutral-300">{{ __('Phone number') }}</span>
                        <input type="text" name="phone" value="{{ old('phone') }}" required class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-neutral-300">{{ __('Allergies') }}</span>
                        <select name="allergies[]" multiple size="5" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @foreach($allergies as $allergy)
                                <option value="{{ $allergy }}" @selected(collect(old('allergies', []))->contains($allergy))>{{ $allergy }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-neutral-500">{{ __('Hold CTRL or CMD to select multiple options.') }}</p>
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold text-neutral-300">{{ __('Dietary notes') }}</span>
                        <select name="diets[]" multiple size="5" class="mt-2 w-full rounded-2xl border border-neutral-800 bg-neutral-900 px-4 py-3 text-neutral-100 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/30">
                            @foreach($diets as $diet)
                                <option value="{{ $diet }}" @selected(collect(old('diets', []))->contains($diet))>{{ $diet }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.reservations.index') }}" class="rounded-full border border-neutral-700 px-4 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-300 transition hover:border-neutral-500 hover:text-white">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-full bg-emerald-500 px-5 py-2 text-xs font-semibold uppercase tracking-[0.3rem] text-neutral-900 transition hover:bg-emerald-400">{{ __('Save Reservation') }}</button>
            </div>
        </form>
    </section>
@endsection