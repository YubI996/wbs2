<div class="min-h-screen flex items-center justify-center p-4" style="background: linear-gradient(to bottom right, oklch(0.53 0.21 263.57), oklch(0.60 0.18 250), oklch(0.77 0.1 230.91));">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
            </pattern>
            <rect width="100%" height="100%" fill="url(#grid)"/>
        </svg>
    </div>

    <div class="relative z-10 w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                <div class="w-14 h-14 bg-white rounded-xl flex items-center justify-center shadow-lg">
                    <img src="{{ asset('img/logo-wbs.png') }}" 
                         alt="Logo WBS" 
                         class="h-12 md:h-14 w-auto drop-shadow-lg">
                </div>
                <div class="text-left">
                    <h1 class="text-2xl font-bold text-white">WBS</h1>
                    <p class="text-blue-200 text-sm">Kota Bontang</p>
                </div>
            </a>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Masuk</h2>
                <p class="text-gray-500 text-sm mt-1">Masuk ke dashboard pengelola</p>
            </div>

            <form wire:submit="login" class="space-y-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" wire:model="email"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                           placeholder="email@bontangkota.go.id"
                           autocomplete="email">
                    @error('email')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" wire:model="password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                           placeholder="••••••••"
                           autocomplete="current-password">
                    @error('password')
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="remember"
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-600">Ingat saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" style="margin-top: 1.75rem;"
                        class="w-full btn-primary inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold transition"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-75">
                    <span wire:loading.remove wire:target="login">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </span>
                    <span wire:loading wire:target="login">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span wire:loading.remove wire:target="login">Masuk</span>
                    <span wire:loading wire:target="login">Memproses...</span>
                </button>
            </form>

            @if($recaptchaEnabled && $recaptchaSiteKey)
            <div class="mt-6 pt-4 border-t border-gray-100 text-center text-xs text-gray-400">
                Dilindungi oleh reCAPTCHA
            </div>
            @endif

            <!-- Back to Home -->
            <div class="mt-6 pt-4 {{ !($recaptchaEnabled && $recaptchaSiteKey) ? 'border-t border-gray-100' : '' }} text-center">
                <a href="{{ route('home') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
                    ← Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-blue-200 text-sm">
            <p>&copy; {{ date('Y') }} WBS Kota Bontang</p>
        </div>
    </div>

    @if($recaptchaEnabled && $recaptchaSiteKey)
    <!-- reCAPTCHA v3 Script -->
    <script src="https://www.google.com/recaptcha/api.js?render={{ $recaptchaSiteKey }}"></script>
    <script>
        function executeRecaptcha() {
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ $recaptchaSiteKey }}', {action: 'login'}).then(function(token) {
                    @this.set('recaptchaToken', token);
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            executeRecaptcha();

            // Refresh token every 90 seconds (tokens expire after 2 minutes)
            setInterval(executeRecaptcha, 90000);
        });

        // Listen for refresh event from Livewire
        document.addEventListener('livewire:init', function() {
            Livewire.on('refresh-recaptcha', function() {
                executeRecaptcha();
            });
        });
    </script>
    @endif

    @push('scripts')
    <script>
        // Prevent form submission until Livewire is ready
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('form[wire\\:submit="login"]');

            if (loginForm) {
                let livewireReady = false;

                document.addEventListener('livewire:init', function() {
                    livewireReady = true;
                    console.log('[Login] Form ready for submission');
                });

                loginForm.addEventListener('submit', function(e) {
                    if (!livewireReady) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.warn('[Login] Submission blocked - Livewire not ready');

                        setTimeout(() => {
                            if (livewireReady) {
                                loginForm.requestSubmit();
                            }
                        }, 100);

                        return false;
                    }

                    console.log('[Login] Submitting...');
                });
            }
        });
    </script>
    @endpush
</div>
