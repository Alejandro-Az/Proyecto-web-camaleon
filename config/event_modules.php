<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Defaults de módulos
    |--------------------------------------------------------------------------
    | Estos defaults se aplican cuando en DB falta una llave.
    | Event::modulesWithDefaults() y Event::normalizeModulesForStorage()
    | los usan para evitar "llaves faltantes" y deuda técnica.
    */
    'defaults' => [
        'gallery'                => true,
        'songs'                  => true,
        'rsvp'                   => true,
        'public_attendance_list' => false,
        'dress_code'             => true,
        'gifts'                  => true,
        'guest_photos_upload'    => true,
        'romantic_phrases'       => true,
        'countdown'              => true,
        'map'                    => true,
        'schedule'               => true,

        // ✅ NUEVO: Historia / Sobre...
        'story'                  => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Aliases legacy → llave canónica
    |--------------------------------------------------------------------------
    | Si en algún punto usted cambia nombres de llaves, esto evita romper eventos viejos.
    | "canónica gana sobre legacy".
    */
    'legacy_aliases' => [
        // playlist legacy (si existía)
        'playlist_suggestions' => 'songs',
        'playlist_votes'       => 'songs',

        // ✅ NUEVO: historia legacy
        'history'  => 'story',
        'about'    => 'story',
        'about_us' => 'story',
    ],
];
