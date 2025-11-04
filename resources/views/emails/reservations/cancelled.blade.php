@php($reservation = $reservation->loadMissing(['guest', 'table']))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #0b0b0b; color: #f5f5f5; margin: 0; padding: 24px; }
        .card { background: #121212; border: 1px solid #1f1f1f; border-radius: 16px; padding: 32px; max-width: 640px; margin: 0 auto; }
        .meta { margin-top: 24px; border: 1px solid #1f1f1f; border-radius: 12px; padding: 16px; background: #181818; }
        .meta dt { font-size: 11px; letter-spacing: 0.3em; text-transform: uppercase; color: #a3a3a3; margin-top: 12px; }
        .meta dd { margin: 4px 0 0 0; font-size: 14px; color: #f9fafb; }
        .subtle { margin-top: 24px; font-size: 13px; color: #d4d4d4; }
    </style>
</head>
<body>
    <div class="card">
        <h1 style="margin:0 0 16px 0; font-size:28px;">{{ __('Reservation cancelled') }}</h1>
        <p style="margin:0; font-size:15px; line-height:1.6; color:#d4d4d4;">
            {{ __('Your reservation has been cancelled successfully. We hope to welcome you another time.') }}
        </p>

        <dl class="meta">
            <dt>{{ __('Reference') }}</dt>
            <dd>{{ $reservation->reference }}</dd>
            <dt>{{ __('Original schedule') }}</dt>
            <dd>{{ $reservation->reservation_date?->format('d M Y') }} - {{ $reservation->reservation_time?->format('H:i') }}</dd>
            <dt>{{ __('Guests') }}</dt>
            <dd>{{ $reservation->number_of_people }} {{ \Illuminate\Support\Str::plural('guest', $reservation->number_of_people) }}</dd>
            @if(!empty($reservation->allergies))
                <dt>{{ __('Allergies we noted') }}</dt>
                <dd>{{ implode(', ', $reservation->allergies) }}</dd>
            @endif
        </dl>

        <p class="subtle">{{ __('If you cancelled by mistake, please call us at :phone and we will do our best to assist you.', ['phone' => $contactPhone]) }}</p>
        @if($reservation->cancel_reason)
            <p class="subtle" style="margin-top:8px;">{{ __('Message from our team: :message', ['message' => $reservation->cancel_reason]) }}</p>
        @endif
        <p class="subtle" style="margin-top:8px;">{{ __('Thank you from the Royal Coupon Code team.') }}</p>
    </div>
</body>
</html>

