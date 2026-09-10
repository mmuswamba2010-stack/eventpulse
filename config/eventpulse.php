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
    | Équivalent USD (affichage secondaire — ex. chiffre d'affaires)
    |--------------------------------------------------------------------------
    */

    'usd' => [
        'enabled' => filter_var(env('EVENTPULSE_USD_ENABLED', true), FILTER_VALIDATE_BOOL),
        'symbol' => env('EVENTPULSE_USD_SYMBOL', '$'),
        'decimals' => (int) env('EVENTPULSE_USD_DECIMALS', 2),
        'symbol_position' => env('EVENTPULSE_USD_SYMBOL_POSITION', 'before'),
        // Nombre de francs congolais pour 1 USD (ex. 2250 → 10 $ = 22 500 FC)
        'cdf_per_usd' => (float) env('EVENTPULSE_CDF_PER_USD', 2250),
    ],

    /*
    |--------------------------------------------------------------------------
    | Catalogue public — affichage prix (une seule devise, sans double ligne)
    | auto = USD arrondi si ≥ 1 $, sinon FC · usd = toujours $ · cdf = toujours FC
    |--------------------------------------------------------------------------
    */

    'catalog' => [
        'price_currency' => env('EVENTPULSE_CATALOG_PRICE_CURRENCY', 'auto'),
        'featured_count' => (int) env('EVENTPULSE_CATALOG_FEATURED_COUNT', 4),
    ],

    /*
    |--------------------------------------------------------------------------
    | Frais de publication (en francs congolais)
    |--------------------------------------------------------------------------
    */

    'publication_fee' => (float) env('EVENTPULSE_PUBLICATION_FEE', 22500),

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
    | Plateforme — frais de publication (intégrateur à venir)
    |--------------------------------------------------------------------------
    */

    'platform' => [
        'name' => env('EVENTPULSE_PLATFORM_NAME', 'Event Pulse'),
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
        env('EVENTPULSE_ORGANIZER_MODERATION', false),
        FILTER_VALIDATE_BOOL
    ),

    /*
    |--------------------------------------------------------------------------
    | Secret HMAC pour webhooks paiement (intégrateur)
    |--------------------------------------------------------------------------
    */

    'webhook_secret' => env('EVENTPULSE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Token pour déclencher schedule:run via URL (tâche planifiée AlwaysData)
    |--------------------------------------------------------------------------
    */

    'cron_token' => env('EVENTPULSE_CRON_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Google Search Console — code de vérification (balise meta)
    |--------------------------------------------------------------------------
    */

    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION'),

    /*
    |--------------------------------------------------------------------------
    | Conditions organisateur — version des CGU / confidentialité
    |--------------------------------------------------------------------------
    */

    'organizer_terms_version' => env('EVENTPULSE_ORGANIZER_TERMS_VERSION', '1.0'),

    /*
    |--------------------------------------------------------------------------
    | Images événements — compression catalogue / mobile
    |--------------------------------------------------------------------------
    */

    'event_image_max_width' => (int) env('EVENTPULSE_EVENT_IMAGE_MAX_WIDTH', 1200),
    'event_image_thumb_width' => (int) env('EVENTPULSE_EVENT_IMAGE_THUMB_WIDTH', 640),
    'event_image_quality' => (int) env('EVENTPULSE_EVENT_IMAGE_QUALITY', 82),
    'event_image_thumb_quality' => (int) env('EVENTPULSE_EVENT_IMAGE_THUMB_QUALITY', 78),

];
