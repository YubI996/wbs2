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
    | Enable reCAPTCHA
    |--------------------------------------------------------------------------
    |
    | Enable or disable reCAPTCHA validation. Useful for local development.
    |
    */
    'enabled' => env('RECAPTCHA_ENABLED', true),
];
