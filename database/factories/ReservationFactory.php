<?php

namespace Database\Factories;

use App\Enums\ReservationStatus;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $date = now()->addDays(fake()->numberBetween(0, 14))->startOfDay();
        $time = now()->setTime(fake()->numberBetween(12, 22), Arr::random([0, 15, 30, 45]))->format('H:i:s');

        return [
            'guest_id' => Guest::factory(),
            'restaurant_table_id' => RestaurantTable::factory(),
            'number_of_people' => fake()->numberBetween(1, 12),
            'reservation_date' => $date,
            'reservation_time' => $time,
            'visit_purpose' => fake()->randomElement(['business', 'casual_visit', 'special_occasion']),
            'occasion' => fake()->randomElement(['Event', 'General', 'Business', 'Party']),
            'source' => fake()->randomElement(['online', 'walkin', 'upcoming']),
            'message' => fake()->optional()->sentence(),
            'reservation_notes' => fake()->optional()->sentence(),
            'allergies' => fake()->randomElements(
                [
                    'Gluten', 'Sesame', 'Nuts', 'Crustacean', 'Eggs', 'Fish', 'Mustard', 'Lactose',
                    'Celery', 'Peanuts', 'Shellfish', 'Soy', 'Lupins', 'Sulphite',
                ],
                fake()->numberBetween(0, 3)
            ),
            'diets' => fake()->randomElements(
                ['Gluten-free', 'Halal', 'Kosher', 'Lactose-free', 'Vegan', 'Vegetarian'],
                fake()->numberBetween(0, 2)
            ),
            'status' => fake()->randomElement([
                ReservationStatus::Pending->value,
                ReservationStatus::Confirmed->value,
                ReservationStatus::Cancelled->value,
            ]),
            'created_via_frontend' => fake()->boolean(),
            'created_by' => null,
            'step_one_completed_at' => now(),
            'details_completed_at' => now(),
            'confirmed_at' => null,
            'cancelled_at' => null,
            'cancel_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => ReservationStatus::Pending->value]);
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => ReservationStatus::Confirmed->value, 'confirmed_at' => now()]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => ReservationStatus::Cancelled->value, 'cancelled_at' => now()]);
    }
}