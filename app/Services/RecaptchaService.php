<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    protected string $secretKey;
    protected string $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';
    protected float $minScore = 0.3;

    public function __construct()
    {
        $this->secretKey = config('recaptcha.secret_key', '');
        $this->minScore = (float) config('recaptcha.min_score', 0.3);
    }

    /**
     * Verify reCAPTCHA v3 token
     *
     * @param string|null $token The reCAPTCHA token
     * @param string|null $expectedAction The expected action name (optional)
     * @param string|null $remoteIp The user's IP address (optional)
     * @return bool
     */
    public function verify(?string $token, ?string $expectedAction = null, ?string $remoteIp = null): bool
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

            // Check success
            if (!($result['success'] ?? false)) {
                if (isset($result['error-codes'])) {
                    Log::warning('reCAPTCHA verification failed', [
                        'error_codes' => $result['error-codes'],
                    ]);
                }
                return false;
            }

            // Check score for v3 (score is between 0.0 and 1.0)
            $score = $result['score'] ?? 0;
            if ($score < $this->minScore) {
                Log::warning('reCAPTCHA score too low', [
                    'score' => $score,
                    'min_score' => $this->minScore,
                ]);
                return false;
            }

            // Optionally verify the action
            if ($expectedAction && isset($result['action'])) {
                if ($result['action'] !== $expectedAction) {
                    Log::warning('reCAPTCHA action mismatch', [
                        'expected' => $expectedAction,
                        'actual' => $result['action'],
                    ]);
                    return false;
                }
            }

            return true;
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

    /**
     * Set minimum score threshold
     */
    public function setMinScore(float $score): self
    {
        $this->minScore = max(0.0, min(1.0, $score));
        return $this;
    }
}
