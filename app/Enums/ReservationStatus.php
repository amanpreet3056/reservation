<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case AwaitingDetails = 'awaiting_details';
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingDetails => __('Awaiting Guest Details'),
            self::Pending => __('Pending Confirmation'),
            self::Confirmed => __('Confirmed'),
            self::Cancelled => __('Cancelled'),
        };
    }
}