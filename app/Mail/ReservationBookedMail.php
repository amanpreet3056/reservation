<?php

namespace App\Mail;

use App\Models\Reservation;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationBookedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation)
    {
    }

    public function build(): self
    {
        return $this->subject(__('Reservation request received'))
            ->view('emails.reservations.booked', [
                'reservation' => $this->reservation,
                'manage' => $this->reservation->manageUrls(),
                'contactPhone' => Setting::getValue('booking.contact_phone', '9814203056'),
            ]);
    }
}
