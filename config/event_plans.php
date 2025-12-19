<?php

return [
    // Ajusta esto a tu estrategia comercial:
    'plans' => [
        'standard' => [
            'modules' => [
                'countdown',
                'dress_code',
                'schedule',
                'map',
                'gallery',
                'rsvp',
            ],
        ],

        'premium' => [
            'modules' => [
                // premium = todo (incluye lo standard)
                'countdown',
                'dress_code',
                'schedule',
                'map',
                'gallery',
                'rsvp',

                'public_attendance_list',
                'songs',
                'gifts',
                'guest_photos_upload',
                'romantic_phrases',
                'story',
            ],
        ],
    ],

    // fallback si un evento trae un plan raro
    'default_plan' => 'standard',
];
