<?php

namespace App\Filament\Pages\Auth;

use App\Models\Role;
use App\Services\RecaptchaService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public ?string $recaptchaToken = null;
    
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }
    
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label('Email')
            ->email()
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label('Password')
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->autocomplete('current-password')
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getRememberFormComponent(): Component
    {
        return Checkbox::make('remember')
            ->label('Ingat saya');
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        // Verify reCAPTCHA if enabled
        if (RecaptchaService::isEnabled()) {
            $token = request()->input('recaptcha_token');
            
            if ($token) {
                $recaptcha = new RecaptchaService();
                if (!$recaptcha->verify($token, 'login')) {
                    throw ValidationException::withMessages([
                        'data.email' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.',
                    ]);
                }
            }
        }

        if (!Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Filament::auth()->user();
        
        // Check if user can access this panel
        if (!$user->canAccessPanel(Filament::getCurrentPanel())) {
            Filament::auth()->logout();
            
            // Redirect to appropriate panel
            $redirectUrl = match($user->role_id) {
                Role::INSPEKTUR => '/inspektur',
                Role::VERIFIKATOR => '/verifikator',
                Role::ADMIN => '/admin',
                default => '/',
            };
            
            $this->redirect($redirectUrl);
            return null;
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
    
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => 'Email atau password salah.',
        ]);
    }
}
