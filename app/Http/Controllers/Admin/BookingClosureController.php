<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingClosure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingClosureController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        BookingClosure::query()->where('is_active', true)->update(['is_active' => false]);

        BookingClosure::create([
            'starts_at' => Carbon::parse($data['starts_at']),
            'ends_at' => $data['ends_at'] ? Carbon::parse($data['ends_at']) : null,
            'message' => $data['message'],
            'created_by' => Auth::id(),
            'is_active' => true,
        ]);

        return back()->with('success', __('Online reservations have been paused.'));
    }

    public function resume(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'closure_id' => ['required', 'integer', 'exists:booking_closures,id'],
        ]);

        BookingClosure::query()
            ->where('id', $data['closure_id'])
            ->update([
                'is_active' => false,
                'ends_at' => now(),
            ]);

        return back()->with('success', __('Online reservations are now open.'));
    }
}