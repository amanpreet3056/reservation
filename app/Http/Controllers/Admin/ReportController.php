<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $range = $request->string('range')->toString() ?: 'weekly';
        $startDate = $request->date('start_date');
        $endDate = $request->date('end_date');

        [$start, $end] = $this->resolveRange($range, $startDate, $endDate);

        $baseQuery = Reservation::query()
            ->whereBetween('reservation_date', [$start->toDateString(), $end->toDateString()]);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'confirmed' => (clone $baseQuery)->where('status', ReservationStatus::Confirmed->value)->count(),
            'pending' => (clone $baseQuery)->where('status', ReservationStatus::Pending->value)->count(),
            'cancelled' => (clone $baseQuery)->where('status', ReservationStatus::Cancelled->value)->count(),
            'guests' => (clone $baseQuery)->sum('number_of_people'),
        ];

        $stats['average_party'] = $stats['total'] > 0 ? round($stats['guests'] / $stats['total'], 2) : 0;

        $bySource = (clone $baseQuery)
            ->selectRaw('source, COUNT(*) as total')
            ->groupBy('source')
            ->pluck('total', 'source')
            ->toArray();

        $dailyTrend = (clone $baseQuery)
            ->selectRaw('reservation_date, COUNT(*) as total')
            ->groupBy('reservation_date')
            ->orderBy('reservation_date')
            ->pluck('total', 'reservation_date')
            ->toArray();

        return view('admin.reports.index', [
            'range' => $range,
            'start' => $start,
            'end' => $end,
            'stats' => $stats,
            'bySource' => $bySource,
            'dailyTrend' => $dailyTrend,
        ]);
    }

    protected function resolveRange(string $range, ?Carbon $start, ?Carbon $end): array
    {
        $now = Carbon::today();

        return match ($range) {
            'monthly' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'yearly' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'custom' => [
                $start ? Carbon::parse($start) : $now->copy()->subWeeks(2),
                $end ? Carbon::parse($end) : $now,
            ],
            default => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
        };
    }
}