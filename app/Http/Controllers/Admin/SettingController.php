<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => [
                'restaurant_name' => Setting::getValue('restaurant.name', 'Royal Coupon Code'),
                'contact_email' => Setting::getValue('restaurant.contact_email', config('mail.from.address')),
                'contact_phone' => Setting::getValue('booking.contact_phone', '9814203056'),
                'notification_emails' => implode(', ', Setting::getValue('booking.notification_emails', [])),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'restaurant_name' => ['required', 'string', 'max:120'],
            'contact_email' => ['required', 'email'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'notification_emails' => ['nullable', 'string'],
        ]);

        Setting::setValue('restaurant.name', $data['restaurant_name']);
        Setting::setValue('restaurant.contact_email', $data['contact_email']);
        Setting::setValue('booking.contact_phone', $data['contact_phone']);

        $emails = array_filter(array_map('trim', explode(',', $data['notification_emails'] ?? '')));
        Setting::setValue('booking.notification_emails', $emails);

        return back()->with('success', __('Settings updated successfully.'));
    }
}