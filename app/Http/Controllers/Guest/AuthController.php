<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guest\GuestLoginRequest;
use App\Http\Requests\Guest\GuestRegistrationRequest;
use App\Models\Guest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('guest.auth.register');
    }

    public function register(GuestRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $guest = Guest::query()->firstOrNew(['email' => $data['email']]);

        if ($guest->exists && !empty($guest->password)) {
            return back()->withErrors([
                'email' => __('An account with this email already exists. Please sign in instead.'),
            ])->onlyInput('email');
        }

        $guest->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?? null,
            'marketing_opt_in' => $data['marketing_opt_in'] ?? false,
        ]);
        $guest->password = Hash::make($data['password']);

        $preferences = $guest->preferences ?? [];
        if (!empty($data['allergies'])) {
            $preferences['allergies'] = $data['allergies'];
        }
        $guest->preferences = $preferences;

        $guest->save();

        Auth::guard('guest')->login($guest, true);

        return redirect()->route('guest.dashboard');
    }

    public function showLogin(): View
    {
        return view('guest.auth.login');
    }

    public function login(GuestLoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (Auth::guard('guest')->attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $credentials['remember'] ?? false
        )) {
            $request->session()->regenerate();

            return redirect()->intended(route('guest.dashboard'));
        }

        return back()->withErrors([
            'email' => __('The provided credentials do not match our records.'),
        ])->onlyInput('email');
    }

    public function logout(): RedirectResponse
    {
        Auth::guard('guest')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('reservations.form')->with('status', __('You have been signed out.'));
    }
}
