<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Manage\UpdateReservationRequest;
use App\Models\Reservation;
use App\Models\Setting;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ManageReservationController extends Controller
{
    public function __construct(private readonly ReservationService $reservationService)
    {
    }

    public function edit(Request $request, string $reference, string $token): View
    {
        $reservation = $this->resolveReservation($reference, $token);

        abort_if($reservation->status->value === 'cancelled', 410, __('This reservation has been cancelled.'));

        return view('frontend.reservations.manage.update', [
            'reservation' => $reservation,
            'contactPhone' => Setting::getValue('booking.contact_phone', '9814203056'),
        ]);
    }

    public function update(UpdateReservationRequest $request, string $reference, string $token): RedirectResponse
    {
        $reservation = $this->resolveReservation($reference, $token);

        $reservation = $this->reservationService->rescheduleReservation($reservation, $request->validated(), [
            'performed_by' => null,
            'reservation_notes' => $reservation->reservation_notes,
            'origin' => 'guest',
        ]);

        return redirect()
            ->route('reservations.manage.updated', [$reservation->reference, $reservation->manage_token])
            ->with('status', __('Your reservation change has been submitted. Our team will confirm shortly.'));
    }

    public function updated(string $reference, string $token): View
    {
        $reservation = $this->resolveReservation($reference, $token);

        return view('frontend.reservations.manage.updated', [
            'reservation' => $reservation,
            'contactPhone' => Setting::getValue('booking.contact_phone', '9814203056'),
        ]);
    }

    public function cancel(string $reference, string $token): View
    {
        $reservation = $this->resolveReservation($reference, $token);

        if ($reservation->status->value === 'cancelled') {
            return $this->cancelled($reference, $token);
        }

        return view('frontend.reservations.manage.cancel', [
            'reservation' => $reservation,
            'contactPhone' => Setting::getValue('booking.contact_phone', '9814203056'),
        ]);
    }

    public function cancelled(string $reference, string $token): View
    {
        $reservation = $this->resolveReservation($reference, $token);

        return view('frontend.reservations.manage.cancelled', [
            'reservation' => $reservation,
            'contactPhone' => Setting::getValue('booking.contact_phone', '9814203056'),
        ]);
    }

    public function destroy(string $reference, string $token): RedirectResponse
    {
        $reservation = $this->resolveReservation($reference, $token);

        if ($reservation->status->value === 'cancelled') {
            return redirect()->route('reservations.manage.cancelled', [$reservation->reference, $reservation->manage_token]);
        }

        $this->reservationService->cancelReservation($reservation, [
            'origin' => 'guest',
        ]);

        return redirect()->route('reservations.manage.cancelled', [$reservation->reference, $reservation->manage_token])
            ->with('status', __('Your reservation has been cancelled.'));
    }

    public function calendar(string $reference, string $token): Response
    {
        $reservation = $this->resolveReservation($reference, $token);

        $ics = $this->generateCalendarFeed($reservation);

        return response($ics, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => sprintf('attachment; filename="reservation-%s.ics"', $reservation->reference),
        ]);
    }

    protected function resolveReservation(string $reference, string $token): Reservation
    {
        return Reservation::query()
            ->where('reference', $reference)
            ->where('manage_token', $token)
            ->with(['guest', 'table'])
            ->firstOrFail();
    }

    protected function generateCalendarFeed(Reservation $reservation): string
    {
        $start = $reservation->startAt()?->utc();
        $end = $reservation->endAt()?->utc() ?? $start?->copy()->addMinutes(120);

        if (!$start || !$end) {
            $start = now()->utc();
            $end = $start->copy()->addHour();
        }

        $uidHost = parse_url(config('app.url', request()->getSchemeAndHttpHost()), PHP_URL_HOST) ?? 'reservations.local';
        $uid = sprintf('%s@%s', $reservation->reference, $uidHost);

        $summary = __('Reservation at :restaurant', ['restaurant' => config('app.name', 'Our Restaurant')]);
        $location = Setting::getValue('restaurant.address', '');
        $description = collect([
            __('Reference: :ref', ['ref' => $reservation->reference]),
            __('Guests: :count', ['count' => $reservation->number_of_people]),
            $reservation->message,
        ])->filter()->implode('\n');

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Reservation Suite//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:' . $start->format('Ymd\THis\Z'),
            'DTEND:' . $end->format('Ymd\THis\Z'),
            'SUMMARY:' . $this->escapeIcsText($summary),
            'DESCRIPTION:' . $this->escapeIcsText($description),
            'LOCATION:' . $this->escapeIcsText($location),
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines) . "\r\n";
    }

    protected function escapeIcsText(?string $value): string
    {
        $value ??= '';

        return str_replace(
            ['\\', ',', ';', "\n", "\r"],
            ['\\\\', '\,', '\;', '\n', ''],
            $value
        );
    }
}
