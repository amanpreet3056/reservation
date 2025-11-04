<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'guest_id',
        'restaurant_table_id',
        'number_of_people',
        'reservation_date',
        'reservation_time',
        'visit_purpose',
        'occasion',
        'source',
        'message',
        'reservation_notes',
        'allergies',
        'diets',
        'status',
        'created_via_frontend',
        'created_by',
        'step_one_completed_at',
        'details_completed_at',
        'confirmed_at',
        'cancelled_at',
        'cancel_reason',
        'manage_token',
        'last_notified_at',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'allergies' => 'array',
        'diets' => 'array',
        'status' => ReservationStatus::class,
        'created_via_frontend' => 'boolean',
        'step_one_completed_at' => 'datetime',
        'details_completed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_notified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $reservation) {
            $reservation->reference ??= Str::upper(Str::random(8));
            $reservation->manage_token ??= (string) Str::uuid();
        });
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class, 'restaurant_table_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function history(): HasMany
    {
        return $this->hasMany(ReservationHistory::class)->latest();
    }

    protected function statusLabel(): Attribute
    {
        return Attribute::get(function () {
            $status = $this->status;

            return $status instanceof ReservationStatus
                ? $status->label()
                : (string) $status;
        });
    }

    protected function reservationTime(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::createFromFormat('H:i:s', $value) : null,
            set: fn ($value) => $value instanceof Carbon
                ? $value->format('H:i:s')
                : ($value ?: null),
        );
    }

    protected function reservationDateTime(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->reservation_date) {
                return null;
            }

            $time = $this->reservation_time instanceof Carbon
                ? $this->reservation_time->format('H:i:s')
                : ($this->reservation_time ?: '00:00:00');

            return $this->reservation_date->copy()->setTime(...explode(':', $time));
        });
    }

    public function startAt(): ?Carbon
    {
        return $this->reservation_date_time;
    }

    public function endAt(): ?Carbon
    {
        $start = $this->startAt();
        if (!$start) {
            return null;
        }

        $duration = (int) config('reservations.default_duration_minutes', 120);

        return $start->copy()->addMinutes($duration);
    }

    public function cancellationDeadline(): ?Carbon
    {
        $start = $this->startAt();
        if (!$start) {
            return null;
        }

        $cutoff = (int) config('reservations.cancellation_cutoff_hours', 24);

        return $start->copy()->subHours($cutoff);
    }

    public function reminders(): array
    {
        $start = $this->startAt();
        if (!$start) {
            return [];
        }

        $hours = (array) config('reservations.reminder_hours', []);

        return collect($hours)
            ->map(fn (int $hour) => $start->copy()->subHours($hour))
            ->filter(fn (Carbon $when) => $when->isFuture())
            ->values()
            ->all();
    }

    public function timeline(): array
    {
        $events = [];

        $requested = $this->created_at ?? now();
        $events[] = [
            'label' => __('Reservation submitted'),
            'timestamp' => $requested->toIso8601String(),
        ];

        if ($deadline = $this->cancellationDeadline()) {
            $events[] = [
                'label' => __('Free cancellation until'),
                'timestamp' => $deadline->toIso8601String(),
            ];
        }

        foreach ($this->reminders() as $target) {
            $events[] = [
                'label' => __('Reminder scheduled'),
                'timestamp' => $target->toIso8601String(),
            ];
        }

        if ($start = $this->startAt()) {
            $events[] = [
                'label' => __('Arrival'),
                'timestamp' => $start->toIso8601String(),
            ];
        }

        return $events;
    }

    public function googleCalendarLink(): ?string
    {
        $start = $this->startAt();
        $end = $this->endAt();

        if (!$start || !$end) {
            return null;
        }

        $params = [
            'action' => 'TEMPLATE',
            'text' => rawurlencode(__('Reservation at :name', ['name' => config('app.name', 'Our Restaurant')])),
            'dates' => sprintf('%s/%s', $start->copy()->utc()->format('Ymd\THis\Z'), $end->copy()->utc()->format('Ymd\THis\Z')),
            'details' => rawurlencode($this->message ?? __('Reservation reference: :ref', ['ref' => $this->reference])),
            'location' => rawurlencode(Setting::getValue('restaurant.address', '')),
        ];

        return 'https://www.google.com/calendar/render?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    public function scopeStatus($query, ReservationStatus|string $status)
    {
        return $query->where('status', $status instanceof ReservationStatus ? $status->value : $status);
    }

    public function manageUrls(): array
    {
        return [
            'update' => route('reservations.manage.update', [$this->reference, $this->manage_token]),
            'cancel' => route('reservations.manage.cancel', [$this->reference, $this->manage_token]),
            'calendar' => route('reservations.manage.calendar', [$this->reference, $this->manage_token]),
        ];
    }
}