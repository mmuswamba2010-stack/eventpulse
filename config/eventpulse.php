<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Devise — franc congolais (CDF) par défaut
    |--------------------------------------------------------------------------
    */

    'currency' => [
        'code' => env('EVENTPULSE_CURRENCY', 'CDF'),
        'symbol' => env('EVENTPULSE_CURRENCY_SYMBOL', 'FC'),
        'name' => env('EVENTPULSE_CURRENCY_NAME', 'Franc congolais'),
        'decimals' => (int) env('EVENTPULSE_CURRENCY_DECIMALS', 0),
        'symbol_position' => env('EVENTPULSE_CURRENCY_SYMBOL_POSITION', 'after'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Frais de publication (en francs congolais)
    |--------------------------------------------------------------------------
    */

    'publication_fee' => (float) env('EVENTPULSE_PUBLICATION_FEE', 25000),

    /*
    |--------------------------------------------------------------------------
    | Paiement obligatoire avant publication (organisateur → plateforme)
    |--------------------------------------------------------------------------
    */

    'require_publication_payment' => filter_var(
        env('EVENTPULSE_REQUIRE_PUBLICATION_PAYMENT', false),
        FILTER_VALIDATE_BOOL
    ),

    /*
    |--------------------------------------------------------------------------
    | Placement assis numéroté (organisateur)
    |--------------------------------------------------------------------------
    */

    'enable_seated_placement' => filter_var(
        env('EVENTPULSE_ENABLE_SEATED_PLACEMENT', false),
        FILTER_VALIDATE_BOOL
    ),

    /*
    |--------------------------------------------------------------------------
    | Compte plateforme — frais de publication (Mobile Money)
    |--------------------------------------------------------------------------
    */

    'platform' => [
        'name' => env('EVENTPULSE_PLATFORM_NAME', 'Event Pulse'),

        'mobile_money_provider' => env('EVENTPULSE_MOBILE_PROVIDER', 'orange_money'),
        'mobile_money_phone' => env('EVENTPULSE_MOBILE_PHONE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Téléphone — format RDC (+243)
    |--------------------------------------------------------------------------
    */

    'phone' => [
        'country_code' => env('EVENTPULSE_PHONE_COUNTRY_CODE', '+243'),
        'placeholder' => env('EVENTPULSE_PHONE_PLACEHOLDER', '+243 81 234 5678'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Paiement simulé (démonstration — aucun prélèvement réel)
    |--------------------------------------------------------------------------
    */

    'payment_simulation' => filter_var(
        env('EVENTPULSE_PAYMENT_SIMULATION', true),
        FILTER_VALIDATE_BOOL
    ),

    /*
    |--------------------------------------------------------------------------
    | Modération des organisateurs à l'inscription
    |--------------------------------------------------------------------------
    */

    'organizer_moderation' => filter_var(
        env('EVENTPULSE_ORGANIZER_MODERATION', true),
        FILTER_VALIDATE_BOOL
    ),

    /*
    |--------------------------------------------------------------------------
    | Secret HMAC pour webhooks Mobile Money
    |--------------------------------------------------------------------------
    */

    'webhook_secret' => env('EVENTPULSE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Token pour déclencher schedule:run via URL (tâche planifiée AlwaysData)
    |--------------------------------------------------------------------------
    */

    'cron_token' => env('EVENTPULSE_CRON_TOKEN'),

];
