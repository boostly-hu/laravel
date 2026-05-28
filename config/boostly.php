<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Boostly app URL
    |--------------------------------------------------------------------------
    |
    | A Boostly példány alap-URL-je, ahonnan a snippet.js betöltődik
    | (pl. https://app.boostly.io). Záró perjel nélkül vagy azzal is jó.
    |
    */

    'url' => env('BOOSTLY_URL', 'https://app.boostly.io'),

    /*
    |--------------------------------------------------------------------------
    | Site token
    |--------------------------------------------------------------------------
    |
    | Az adott oldalhoz tartozó publikus token (a Boostly adminban az oldal
    | beállításainál található). Ezt teszi a @boostlySnippet direktíva a
    | <script> tagbe.
    |
    */

    'site_token' => env('BOOSTLY_SITE_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Webhook secret
    |--------------------------------------------------------------------------
    |
    | A Boostly webhook-végpont titkos kulcsa. Ezzel ellenőrizzük az
    | X-Boostly-Signature fejlécet (HMAC-SHA256). KÖTELEZŐ a webhook
    | használatához — e nélkül minden beérkező kérés 403-at kap.
    |
    */

    'webhook_secret' => env('BOOSTLY_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Webhook route
    |--------------------------------------------------------------------------
    |
    | enabled    — regisztrálja-e a csomag a beépített webhook-route-ot
    | path       — a route útvonala (alapból POST /boostly/webhook)
    | middleware — EXTRA middleware-ek (az aláírás-ellenőrzés mindig fut,
    |              ezen felül adhatsz pl. 'throttle:60,1'-et)
    |
    */

    'webhook' => [
        'enabled' => env('BOOSTLY_WEBHOOK_ENABLED', true),
        'path' => env('BOOSTLY_WEBHOOK_PATH', 'boostly/webhook'),
        'middleware' => [],
    ],

];
