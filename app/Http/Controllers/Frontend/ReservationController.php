<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Models\BookingClosure;
use App\Models\Setting;
use App\Services\AvailabilityService;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservationService,
        private readonly AvailabilityService $availabilityService,
    ) {}

    public function create(Request $request): View
    {
        $closure = BookingClosure::active()->latest('starts_at')->first();

        return view('frontend.reservations.form', [
            'visitPurposes' => config('reservations.visit_purposes'),
            'closure' => $closure,
            'contactPhone' => Setting::getValue('booking.contact_phone', '9814203056'),
            'iframe' => $request->boolean('iframe'),
        ]);
    }

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $request->ensureBookingIsAvailable();

        $data = $request->validated();

        $reservation = $this->reservationService->startReservation($data);

        $message = $data['message'] ?? null;

        if ($data['marketing_opt_in'] ?? false) {
            $optInNote = __('Guest opted in to receive seasonal menu and event updates.');
            $message = $message ? "{$message}\n\n{$optInNote}" : $optInNote;
        }

        $completedReservation = $this->reservationService->completeReservationDetails($reservation, [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'company' => $data['company'] ?? null,
            'event' => $data['event'] ?? null,
            'visit_purpose' => $data['service_type'],
            'allergies' => $data['allergies'] ?? [],
            'message' => $message,
            'marketing_opt_in' => $data['marketing_opt_in'] ?? false,
            'guest_id' => $request->user('guest')?->id,
        ]);

        $completedReservation->loadMissing(['guest']);
        $manageLinks = $completedReservation->manageUrls();

        return response()->json([
            'reservation_id' => $completedReservation->id,
            'reference' => $completedReservation->reference,
            'status' => $completedReservation->status->value,
            'message' => __('Your reservation is sent to the restaurant. We will confirm in a few moments. Thanks!'),
            'timeline' => $completedReservation->timeline(),
            'calendar' => [
                'google' => $completedReservation->googleCalendarLink(),
                'ics' => $manageLinks['calendar'] ?? null,
                'filename' => Str::slug(__('reservation-:ref', ['ref' => $completedReservation->reference])) . '.ics',
            ],
            'manage_urls' => $manageLinks,
        ]);
    }

    public function availability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'party_size' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $date = Carbon::parse($data['date'])->startOfDay();
        $partySize = (int) $data['party_size'];

        $slots = $this->availabilityService->getAvailability($date, $partySize);

        return response()->json([
            'slots' => $slots,
            'has_available' => $slots->contains(fn ($slot) => $slot['available']),
        ]);
    }
}
