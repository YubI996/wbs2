<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    protected string $secretKey;
    protected string $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    public function __construct()
    {
        $this->secretKey = config('recaptcha.secret_key', '');
    }

    /**
     * Verify reCAPTCHA v2 token
     */
    public function verify(?string $token, ?string $remoteIp = null): bool
    {
        if (!self::isEnabled()) {
            return true; // Skip verification if disabled
        }

        if (empty($token)) {
            Log::warning('reCAPTCHA verification failed: No token provided');
            return false;
        }

        try {
            $response = Http::asForm()->post($this->verifyUrl, [
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => $remoteIp ?? request()->ip(),
            ]);

            $result = $response->json();

            if ($result['success'] ?? false) {
                return true;
            }

            if (isset($result['error-codes'])) {
                Log::warning('reCAPTCHA verification failed', [
                    'error_codes' => $result['error-codes'],
                ]);
            }

            return false;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get reCAPTCHA site key
     */
    public static function getSiteKey(): ?string
    {
        return config('recaptcha.site_key');
    }

    /**
     * Check if reCAPTCHA is enabled
     */
    public static function isEnabled(): bool
    {
        return config('recaptcha.enabled', true)
            && !empty(config('recaptcha.site_key'))
            && !empty(config('recaptcha.secret_key'));
    }
}
