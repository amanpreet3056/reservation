<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    public function getAvailability(Carbon $date, int $partySize): Collection
    {
        $slots = $this->generateSlots($date);

        $reservations = $this->reservationsForDate($date);

        return $slots->map(function (Carbon $slotStart) use ($partySize, $reservations) {
            $availableTables = $this->availableTablesForSlot($slotStart, $partySize, null, $reservations);
            $remainingSeats = $availableTables->sum('seats');

            return [
                'time' => $slotStart->format('H:i'),
                'label' => $slotStart->format('g:i A'),
                'available' => $availableTables->isNotEmpty() && $remainingSeats >= $partySize,
                'available_tables' => $availableTables->count(),
                'remaining_seats' => $remainingSeats,
            ];
        });
    }

    public function assignTable(Reservation $reservation): ?RestaurantTable
    {
        $slotStart = $this->combineDateTime($reservation->reservation_date, $reservation->reservation_time);
        $partySize = (int) $reservation->number_of_people;

        $reservations = $this->reservationsForDate($slotStart->copy()->startOfDay());

        $tables = $this->availableTablesForSlot($slotStart, $partySize, $reservation->id, $reservations);

        if ($tables->isEmpty()) {
            return null;
        }

        return $tables
            ->sort(function (RestaurantTable $a, RestaurantTable $b) {
                $priorityA = $a->priority ?? PHP_INT_MAX;
                $priorityB = $b->priority ?? PHP_INT_MAX;

                $comparison = [$priorityA, $a->seats] <=> [$priorityB, $b->seats];

                return $comparison === 0 ? $a->id <=> $b->id : $comparison;
            })
            ->first();
    }

    protected function availableTablesForSlot(Carbon $slotStart, int $partySize, ?int $ignoreReservationId = null, ?Collection $reservations = null): Collection
    {
        $duration = (int) config('reservations.default_duration_minutes', 120);
        $slotEnd = $slotStart->copy()->addMinutes($duration);

        $tables = RestaurantTable::query()
            ->where('status', 'available')
            ->orderBy('priority')
            ->orderBy('seats')
            ->get();

        if ($tables->isEmpty()) {
            return collect();
        }

        $reservations ??= $this->reservationsForDate($slotStart->copy()->startOfDay());

        $assigned = $reservations->filter(fn (Reservation $reservation) => $reservation->restaurant_table_id !== null);
        $unassigned = $reservations->filter(fn (Reservation $reservation) => $reservation->restaurant_table_id === null);

        $availableTables = $tables->keyBy('id');

        foreach ($assigned as $reservation) {
            if ($ignoreReservationId && $reservation->id === $ignoreReservationId) {
                continue;
            }

            if (!$this->slotsOverlap($slotStart, $slotEnd, $reservation)) {
                continue;
            }

            $availableTables->forget($reservation->restaurant_table_id);
        }

        $remainingSeats = $availableTables->sum('seats');

        foreach ($unassigned as $reservation) {
            if ($ignoreReservationId && $reservation->id === $ignoreReservationId) {
                continue;
            }

            if (!$this->slotsOverlap($slotStart, $slotEnd, $reservation)) {
                continue;
            }

            $remainingSeats -= (int) $reservation->number_of_people;

            if ($remainingSeats < $partySize) {
                return collect();
            }
        }

        return $availableTables->filter(fn (RestaurantTable $table) => $table->seats >= $partySize);
    }

    protected function reservationsForDate(Carbon $date): Collection
    {
        return Reservation::query()
            ->whereDate('reservation_date', $date)
            ->whereIn('status', [
                ReservationStatus::Pending->value,
                ReservationStatus::Confirmed->value,
            ])
            ->get();
    }

    protected function generateSlots(Carbon $date): Collection
    {
        $slots = collect();
        $step = (int) config('reservations.slot_step_minutes', 30);
        $duration = (int) config('reservations.default_duration_minutes', 120);
        $service = config('reservations.service_hours');

        $start = $this->combineDateTime($date, $service['start'] ?? '11:00');
        $end = $this->combineDateTime($date, $service['end'] ?? '23:00');

        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $slots->push($cursor->copy());
            $cursor->addMinutes($step);
        }

        return $slots;
    }

    protected function slotsOverlap(Carbon $slotStart, Carbon $slotEnd, Reservation $reservation): bool
    {
        $duration = (int) config('reservations.default_duration_minutes', 120);
        $reservationStart = $this->combineDateTime($reservation->reservation_date, $reservation->reservation_time);
        $reservationEnd = $reservationStart->copy()->addMinutes($duration);

        return $slotStart->lt($reservationEnd) && $slotEnd->gt($reservationStart);
    }

    protected function combineDateTime($date, $time): Carbon
    {
        if ($date instanceof Carbon) {
            $baseDate = $date->copy();
        } else {
            $baseDate = Carbon::parse($date);
        }

        if ($time instanceof Carbon) {
            return Carbon::parse($baseDate->format('Y-m-d') . ' ' . $time->format('H:i'));
        }

        return Carbon::parse($baseDate->format('Y-m-d') . ' ' . $time);
    }
}
