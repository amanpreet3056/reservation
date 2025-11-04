<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\UpdateGuestProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function dashboard(): View
    {
        $guest = auth('guest')->user();

        $upcoming = $guest->reservations()
            ->whereDate('reservation_date', '>=', now()->toDateString())
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->limit(10)
            ->get();

        $history = $guest->reservations()
            ->whereDate('reservation_date', '<', now()->toDateString())
            ->orderByDesc('reservation_date')
            ->orderByDesc('reservation_time')
            ->limit(10)
            ->get();

        return view('guest.dashboard', [
            'guest' => $guest,
            'upcoming' => $upcoming,
            'history' => $history,
        ]);
    }

    public function update(UpdateGuestProfileRequest $request): RedirectResponse
    {
        $guest = $request->user('guest');
        $data = $request->validated();

        $guest->fill(Arr::only($data, [
            'first_name',
            'last_name',
            'email',
            'phone',
            'marketing_opt_in',
        ]));

        if (!empty($data['password'])) {
            $guest->password = Hash::make($data['password']);
        }

        $preferences = $guest->preferences ?? [];
        $preferences['allergies'] = $data['allergies'] ?? [];
        $guest->preferences = $preferences;

        $guest->save();

        return redirect()->route('guest.dashboard')->with('status', __('Profile updated successfully.'));
    }
}
