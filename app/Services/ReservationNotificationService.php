<?php

namespace App\Services;

use App\Mail\ReservationBookedMail;
use App\Mail\ReservationCancelledMail;
use App\Mail\ReservationUpdatedMail;
use App\Models\Reservation;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;

class ReservationNotificationService
{
    public function sendBooked(Reservation $reservation): void
    {
        $this->sendToGuest($reservation, new ReservationBookedMail($reservation));
        $this->sendToAdmins(new ReservationBookedMail($reservation));
    }

    public function sendUpdated(Reservation $reservation): void
    {
        $this->sendToGuest($reservation, new ReservationUpdatedMail($reservation));
        $this->sendToAdmins(new ReservationUpdatedMail($reservation));
    }

    public function sendCancelled(Reservation $reservation): void
    {
        $this->sendToGuest($reservation, new ReservationCancelledMail($reservation));
        $this->sendToAdmins(new ReservationCancelledMail($reservation));
    }

    protected function sendToGuest(Reservation $reservation, $mailable): void
    {
        if ($reservation->guest?->email) {
            Mail::to($reservation->guest->email)->send($mailable);
        }
    }

    protected function sendToAdmins($mailable): void
    {
        $override = config('mail.test_admin_address');
        if (!empty($override)) {
            Mail::to($override)->send($mailable);
            return;
        }

        $emails = Setting::getValue('booking.notification_emails', []);

        if (empty($emails)) {
            $fallback = Setting::getValue('restaurant.contact_email') ?? config('mail.from.address');
            if ($fallback) {
                $emails = [$fallback];
            }
        }

        foreach ($emails as $email) {
            Mail::to($email)->send($mailable);
        }
    }
}
