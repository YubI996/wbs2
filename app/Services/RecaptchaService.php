<?php

namespace App\Services;

use ReCaptcha\ReCaptcha;

class RecaptchaService
{
    protected ReCaptcha $recaptcha;
    
    protected float $minScore = 0.5;
    
    public function __construct()
    {
        $this->recaptcha = new ReCaptcha(config('services.recaptcha.secret'));
    }
    
    /**
     * Verify reCAPTCHA v3 token
     */
    public function verify(string $token, ?string $action = null): bool
    {
        if (!config('services.recaptcha.enabled', true)) {
            return true; // Skip verification if disabled
        }
        
        $response = $this->recaptcha
            ->setExpectedHostname(parse_url(config('app.url'), PHP_URL_HOST))
            ->setScoreThreshold($this->minScore)
            ->verify($token, request()->ip());
        
        if ($action) {
            return $response->isSuccess() && $response->getAction() === $action;
        }
        
        return $response->isSuccess();
    }
    
    /**
     * Get reCAPTCHA site key
     */
    public static function getSiteKey(): ?string
    {
        return config('services.recaptcha.site_key');
    }
    
    /**
     * Check if reCAPTCHA is enabled
     */
    public static function isEnabled(): bool
    {
        return config('services.recaptcha.enabled', true) 
            && config('services.recaptcha.site_key') 
            && config('services.recaptcha.secret');
    }
}
