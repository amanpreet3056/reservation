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
        .actions { margin-top: 24px; display: flex; gap: 12px; flex-wrap: wrap; }
        .btn { padding: 12px 20px; border-radius: 999px; text-decoration: none; font-size: 13px; letter-spacing: 0.2em; text-transform: uppercase; font-weight: 600; display: inline-block; }
        .btn-primary { background: #22d3ee; color: #0b0b0b; }
        .btn-danger { background: #f87171; color: #0b0b0b; }
        .subtle { margin-top: 24px; font-size: 13px; color: #d4d4d4; }
    </style>
</head>
<body>
    <div class="card">
        <h1 style="margin:0 0 16px 0; font-size:28px;">{{ __('Reservation update received') }}</h1>
        <p style="margin:0; font-size:15px; line-height:1.6; color:#d4d4d4;">
            {{ __('We have received your request to change the reservation. Our team will confirm availability and follow up shortly.') }}
        </p>

        <dl class="meta">
            <dt>{{ __('Reference') }}</dt>
            <dd>{{ $reservation->reference }}</dd>
            <dt>{{ __('New schedule') }}</dt>
            <dd>{{ $reservation->reservation_date?->format('d M Y') }} - {{ $reservation->reservation_time?->format('H:i') }}</dd>
            <dt>{{ __('Guests') }}</dt>
            <dd>{{ $reservation->number_of_people }} {{ \Illuminate\Support\Str::plural('guest', $reservation->number_of_people) }}</dd>
            <dt>{{ __('Current status') }}</dt>
            <dd>{{ $reservation->status_label }}</dd>
            @if(!empty($reservation->allergies))
                <dt>{{ __('Allergies noted') }}</dt>
                <dd>{{ implode(', ', $reservation->allergies) }}</dd>
            @endif
        </dl>

        <div class="actions">
            <a href="{{ $manage['update'] }}" class="btn btn-primary">{{ __('Modify again') }}</a>
            <a href="{{ $manage['cancel'] }}" class="btn btn-danger">{{ __('Cancel reservation') }}</a>
        </div>

        <p class="subtle">{{ __('For urgent updates call us at :phone.', ['phone' => $contactPhone]) }}</p>
    </div>
</body>
</html>



