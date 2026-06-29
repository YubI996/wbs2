<?php

return [
    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Site Key
    |--------------------------------------------------------------------------
    |
    | This is the site key provided by Google reCAPTCHA.
    |
    */
    'site_key' => env('RECAPTCHA_SITE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Secret Key
    |--------------------------------------------------------------------------
    |
    | This is the secret key provided by Google reCAPTCHA.
    |
    */
    'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | reCAPTCHA Verify URL
    |--------------------------------------------------------------------------
    |
    | The URL to verify the reCAPTCHA response.
    |
    */
    'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',

    /*
    |--------------------------------------------------------------------------
    | Minimum Score (reCAPTCHA v3)
    |--------------------------------------------------------------------------
    |
    | reCAPTCHA v3 returns a score between 0.0 and 1.0. Requests scoring below
    | this threshold are rejected. Lowering it reduces false rejections of
    | legitimate users on shared/NAT/VPN networks, at the cost of letting
    | slightly more suspicious traffic through. Tunable via env without a deploy.
    |
    */
    'min_score' => (float) env('RECAPTCHA_MIN_SCORE', 0.3),

    /*
    |--------------------------------------------------------------------------
    | Enable reCAPTCHA
    |--------------------------------------------------------------------------
    |
    | Enable or disable reCAPTCHA validation. Useful for local development.
    |
    */
    'enabled' => env('RECAPTCHA_ENABLED', true),
];
