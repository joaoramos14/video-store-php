<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Credenciais de serviços externos usados pelo app.
    |
    */

    'tmdb' => [
        'key' => env('TMDB_API_KEY'),
        // false só em desenvolvimento, se o certificado da empresa bloquear o SSL
        'verify' => env('TMDB_VERIFY_SSL', true),
    ],
];
