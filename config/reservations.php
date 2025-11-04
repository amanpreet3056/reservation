<?php

return [
    'visit_purposes' => [
        'business' => 'Business',
        'casual_visit' => 'Book a Table',
        'special_occasion' => 'Event Booking',
    ],

    'occasions' => [
        'event' => 'Event',
        'general' => 'General',
        'business' => 'Business',
        'party' => 'Party',
    ],

    'sources' => [
        'online' => 'Online',
        'walkin' => 'Walk-in',
        'upcoming' => 'Upcoming Reservation',
    ],

    'allergies' => [
        'Gluten',
        'Sesame',
        'Nuts',
        'Crustacean',
        'Eggs',
        'Fish',
        'Mustard',
        'Lactose',
        'Celery',
        'Peanuts',
        'Shellfish',
        'Soy',
        'Lupins',
        'Sulphite',
    ],

    'diets' => [
        'Gluten-free',
        'Halal',
        'Kosher',
        'Lactose-free',
        'Vegan',
        'Vegetarian',
    ],

    'slot_step_minutes' => 30,
    'default_duration_minutes' => 120,
    'service_hours' => [
        'start' => '11:00',
        'end' => '23:00',
    ],

    'reminder_hours' => [24, 3],
    'cancellation_cutoff_hours' => 24,
    'calendar' => [
        'default_duration_minutes' => 120,
    ],
];
