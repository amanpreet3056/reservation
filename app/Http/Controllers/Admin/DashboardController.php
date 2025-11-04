<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\BookingClosure;
use App\Models\Reservation;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();

        $totals = [
            'total' => Reservation::count(),
            'average_party_size' => Reservation::count() > 0
                ? round(Reservation::avg('number_of_people'), 1)
                : 0,
            'confirmed' => Reservation::where('status', ReservationStatus::Confirmed->value)->count(),
            'cancelled' => Reservation::where('status', ReservationStatus::Cancelled->value)->count(),
            'pending' => Reservation::where('status', ReservationStatus::Pending->value)->count(),
            'seated' => Reservation::where('status', ReservationStatus::Confirmed->value)->sum('number_of_people'),
        ];

        $activeClosure = BookingClosure::active()->latest('starts_at')->first();

        $pendingReservations = Reservation::query()
            ->where('status', ReservationStatus::Pending->value)
            ->with(['guest', 'table'])
            ->orderByDesc('reservation_date')
            ->orderByDesc('reservation_time')
            ->limit(20)
            ->get();

        $upcomingReservations = Reservation::query()
            ->where('status', ReservationStatus::Pending->value)
            ->where(function ($query) use ($today, $now) {
                $query->whereDate('reservation_date', '>', $today)
                    ->orWhere(function ($subQuery) use ($today, $now) {
                        $subQuery->whereDate('reservation_date', $today)
                            ->where(function ($timeQuery) use ($now) {
                                $timeQuery->whereNull('reservation_time')
                                    ->orWhereTime('reservation_time', '>=', $now->format('H:i:s'));
                            });
                    });
            })
            ->with(['guest', 'table'])
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->limit(15)
            ->get();

        $pendingTodayCount = Reservation::query()
            ->where('status', ReservationStatus::Pending->value)
            ->whereDate('reservation_date', $today)
            ->where(function ($query) use ($now) {
                $query->whereNull('reservation_time')
                    ->orWhereTime('reservation_time', '>=', $now->format('H:i:s'));
            })
            ->count();

        $pendingUpcomingCount = Reservation::query()
            ->where('status', ReservationStatus::Pending->value)
            ->where(function ($query) use ($today, $now) {
                $query->whereDate('reservation_date', '>', $today)
                    ->orWhere(function ($subQuery) use ($today, $now) {
                        $subQuery->whereDate('reservation_date', $today)
                            ->where(function ($timeQuery) use ($now) {
                                $timeQuery->whereNull('reservation_time')
                                    ->orWhereTime('reservation_time', '>=', $now->format('H:i:s'));
                            });
                    });
            })
            ->count();

        return view('admin.dashboard', [
            'totals' => $totals,
            'activeClosure' => $activeClosure,
            'pendingReservations' => $pendingReservations,
            'upcomingReservations' => $upcomingReservations,
            'today' => $today,
            'pendingTodayCount' => $pendingTodayCount,
            'pendingUpcomingCount' => $pendingUpcomingCount,
        ]);
    }
}
