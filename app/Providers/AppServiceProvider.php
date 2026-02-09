<?php

namespace App\Providers;

use App\Http\Responses\LogoutResponse;
use App\Models\Aduan;
use App\Policies\AduanPolicy;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind custom logout response untuk redirect ke /login
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Aduan::class, AduanPolicy::class);
    }
}
