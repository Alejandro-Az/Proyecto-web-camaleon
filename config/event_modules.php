<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Defaults (canónicos)
    |--------------------------------------------------------------------------
    | Valores por default cuando en DB no viene la llave.
    */
    'defaults' => [
        'public_attendance_list' => false,
        'guest_photos_upload'    => true,
        'romantic_phrases'       => true,
        'dress_code'             => true,
        'countdown'              => true,
        'map'                    => true,
        'schedule'               => true,
        'gallery'                => true,
        'songs'                  => true,
        'rsvp'                   => true,
        'gifts'                  => true,
        'story'                  => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Aliases legacy => canonical
    |--------------------------------------------------------------------------
    */
    'aliases' => [
        // ✅ el que te está fallando en tests:
        'playlist_suggestions' => 'songs',

        // (Opcionales útiles si tu repo trae llaves viejas)
        'playlist'             => 'songs',
        'photo_gallery'        => 'gallery',
        'guest_photos'         => 'guest_photos_upload',
    ],
    'legacy_aliases' => [
        'playlist_suggestions' => 'songs',
    ],
];
