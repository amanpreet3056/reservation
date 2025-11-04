@php($reservation = $reservation->loadMissing(['guest', 'table']))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #0b0b0b; color: #f5f5f5; margin: 0; padding: 24px; }
        .card { background: #121212; border: 1px solid #1f1f1f; border-radius: 16px; padding: 32px; max-width: 640px; margin: 0 auto; }
        .actions { margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap; }
        .btn { padding: 12px 20px; border-radius: 999px; text-decoration: none; font-size: 13px; letter-spacing: 0.2em; text-transform: uppercase; font-weight: 600; display: inline-block; }
        .btn-primary { background: #22d3ee; color: #0b0b0b; }
        .btn-danger { background: #f87171; color: #0b0b0b; }
        .meta { margin-top: 24px; border: 1px solid #1f1f1f; border-radius: 12px; padding: 16px; background: #181818; }
        .meta dt { font-size: 11px; letter-spacing: 0.3em; text-transform: uppercase; color: #a3a3a3; margin-top: 12px; }
        .meta dd { margin: 4px 0 0 0; font-size: 14px; color: #f9fafb; }
        .subtle { margin-top: 24px; font-size: 13px; color: #d4d4d4; }
    </style>
</head>
<body>
    <div class="card">
        <h1 style="margin:0 0 16px 0; font-size:28px;">{{ __('Reservation request received') }}</h1>
        <p style="margin:0; font-size:15px; line-height:1.6; color:#d4d4d4;">
            {{ __('Thank you for choosing Royal Coupon Code. Our reservations team will review your request and confirm shortly.') }}
        </p>

        <dl class="meta">
            <dt>{{ __('Reference') }}</dt>
            <dd>{{ $reservation->reference }}</dd>
            <dt>{{ __('Party') }}</dt>
            <dd>{{ $reservation->number_of_people }} {{ \Illuminate\Support\Str::plural('guest', $reservation->number_of_people) }}</dd>
            <dt>{{ __('Requested schedule') }}</dt>
            <dd>{{ $reservation->reservation_date?->format('d M Y') }} - {{ $reservation->reservation_time?->format('H:i') }}</dd>
            <dt>{{ __('Visit purpose') }}</dt>
            <dd>{{ $reservation->visit_purpose ? (config('reservations.visit_purposes')[$reservation->visit_purpose] ?? $reservation->visit_purpose) : __('Not specified') }}</dd>
            @if(!empty($reservation->allergies))
                <dt>{{ __('Allergies noted') }}</dt>
                <dd>{{ implode(', ', $reservation->allergies) }}</dd>
            @endif
        </dl>

        <div class="actions">
            <a href="{{ $manage['update'] }}" class="btn btn-primary">{{ __('Update reservation') }}</a>
            <a href="{{ $manage['cancel'] }}" class="btn btn-danger">{{ __('Cancel reservation') }}</a>
        </div>

        <p class="subtle">{{ __('Need immediate assistance? Call us at :phone.', ['phone' => $contactPhone]) }}</p>
        <p class="subtle" style="margin-top:8px;">{{ __('We look forward to welcoming you soon!') }}</p>
    </div>
</body>
</html>




