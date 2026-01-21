<?php

namespace App\Livewire\Auth;

use App\Models\Role;
use App\Services\RecaptchaService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.guest')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;
    public ?string $recaptchaToken = null;
    
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
    
    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
        ];
    }
    
    public function login(): void
    {
        $this->validate();
        
        // Rate limiting
        $throttleKey = strtolower($this->email) . '|' . request()->ip();
        
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }
        
        // Verify reCAPTCHA if enabled
        if (RecaptchaService::isEnabled()) {
            if (!$this->recaptchaToken) {
                throw ValidationException::withMessages([
                    'email' => 'Verifikasi reCAPTCHA diperlukan. Silakan refresh halaman.',
                ]);
            }
            
            $recaptcha = new RecaptchaService();
            if (!$recaptcha->verify($this->recaptchaToken, 'login')) {
                throw ValidationException::withMessages([
                    'email' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.',
                ]);
            }
        }
        
        // Attempt login
        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey, 300); // 5 minutes
            
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }
        
        RateLimiter::clear($throttleKey);
        
        session()->regenerate();
        
        // Redirect based on role
        $this->redirectBasedOnRole();
    }
    
    protected function redirectBasedOnRole(): void
    {
        $user = Auth::user();
        
        $redirectUrl = match($user->role_id) {
            Role::INSPEKTUR => '/inspektur',
            Role::VERIFIKATOR => '/verifikator',
            Role::ADMIN => '/admin',
            default => '/',
        };
        
        $this->redirect($redirectUrl, navigate: true);
    }
    
    public function render()
    {
        return view('livewire.auth.login', [
            'recaptchaSiteKey' => RecaptchaService::getSiteKey(),
            'recaptchaEnabled' => RecaptchaService::isEnabled(),
        ])->title('Login - WBS Kota Bontang');
    }
}
