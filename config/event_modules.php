<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Defaults de módulos por evento
    |--------------------------------------------------------------------------
    | Importante: Estos defaults se usan cuando:
    | - Un evento tiene modules = null
    | - O falta alguna llave dentro del JSON
    |
    | Ajuste aquí los defaults y todo el proyecto se alinea (factory, seeders, helper).
    */
    'defaults' => [
        'gallery'                => true,
        'songs'                  => true,
        'rsvp'                   => true,
        'public_attendance_list' => false,
        'dress_code'             => true,
        'gifts'                  => true,
        'guest_photos_upload'    => false,
        'romantic_phrases'       => true,
        'countdown'              => true,
        'map'                    => true,
        'schedule'               => true,

        // Nuevo / futuro
        'story'                  => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Aliases legacy (compatibilidad hacia atrás)
    |--------------------------------------------------------------------------
    | Si existen eventos viejos que guardaron llaves anteriores,
    | aquí se mapean a la llave “canónica”.
    */
    'legacy_aliases' => [
        'playlist_suggestions' => 'songs',
        'playlist_votes'       => 'songs',
    ],
];
