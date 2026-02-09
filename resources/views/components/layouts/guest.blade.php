<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Whistle Blowing System Kota Bontang - Laporkan dugaan pelanggaran secara aman dan rahasia">
    <title>{{ $title ?? 'WBS Kota Bontang' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <style>
        /* Prevent FOUC - hide elements until Alpine.js initializes */
        [x-cloak] { display: none !important; }

        :root {
            /* Custom OKLCH Color Palette */
            --color-primary: oklch(0.53 0.21 263.57);      /* from-blue-600 replacement */
            --color-primary-light: oklch(0.77 0.1 230.91); /* to-blue-700 replacement */
            --color-primary-hover: oklch(0.60 0.18 260);   /* hover state */
            
            /* Fallback colors for older browsers */
            --color-primary-fallback: #2563EB;
            --color-primary-light-fallback: #60A5FA;
        }
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Gradient dengan OKLCH colors */
        .gradient-primary {
            background: linear-gradient(135deg, oklch(0.53 0.21 263.57) 0%, oklch(0.77 0.1 230.91) 100%);
        }
        
        /* Custom gradient classes */
        .bg-gradient-custom {
            background: linear-gradient(to right, oklch(0.53 0.21 263.57), oklch(0.77 0.1 230.91));
        }
        
        .bg-gradient-custom-br {
            background: linear-gradient(to bottom right, oklch(0.53 0.21 263.57), oklch(0.77 0.1 230.91));
        }
        
        /* Primary color utilities */
        .bg-primary-custom {
            background-color: oklch(0.53 0.21 263.57);
        }
        
        .bg-primary-light-custom {
            background-color: oklch(0.77 0.1 230.91);
        }
        
        .text-primary-custom {
            color: oklch(0.53 0.21 263.57);
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px oklch(0.53 0.21 263.57 / 0.2);
        }
        
        /* Custom button colors with OKLCH */
        .btn-primary {
            background-color: oklch(0.53 0.21 263.57);
            color: white;
        }
        .btn-primary:hover {
            background-color: oklch(0.60 0.18 260);
        }
        
        /* Loading animation */
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        /* Custom focus ring for accessibility */
        .focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 3px oklch(0.53 0.21 263.57 / 0.4);
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 antialiased">
    <!-- Skip to content for accessibility -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded">
        Langsung ke konten utama
    </a>
    
    {{ $slot }}
    
    <!-- Livewire Scripts -->
    @livewireScripts
    
    <script>
        // Global Livewire error handler for CSRF and session issues
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ fail }) => {
                fail(({ status, preventDefault }) => {
                    // Handle CSRF token mismatch / session expired (419)
                    if (status === 419) {
                        preventDefault();
                        
                        if (confirm('Sesi Anda telah berakhir. Halaman akan dimuat ulang untuk memperbarui sesi.')) {
                            window.location.reload();
                        } else {
                            window.location.reload();
                        }
                    }
                });
            });
        });

        // CSRF Token Debugging
        document.addEventListener('DOMContentLoaded', function() {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');

            if (!csrfMeta) {
                console.error('[CSRF] ERROR: CSRF meta tag is missing!');
            } else {
                console.log('[CSRF] Token initialized:', csrfMeta.content.substring(0, 10) + '...');
            }

            console.log('[Livewire] Waiting for initialization...');
        });

        // Enhanced Livewire logging
        document.addEventListener('livewire:init', () => {
            console.log('[Livewire] Successfully initialized');

            Livewire.hook('request', ({ uri, options, payload, respond, succeed, fail }) => {
                console.log('[Livewire] Request:', payload.components?.[0]?.calls?.[0]?.method || 'unknown');

                succeed(({ status }) => {
                    console.log('[Livewire] Success:', status);
                });

                fail(({ status, preventDefault }) => {
                    console.error('[Livewire] Failed:', status);

                    if (status === 419) {
                        preventDefault();
                        console.warn('[CSRF] Token mismatch (419). Reloading...');
                        alert('Sesi Anda telah berakhir. Halaman akan dimuat ulang.');
                        window.location.reload();
                    }
                });
            });
        });

        // Auto-refresh CSRF token every 10 minutes to prevent expiration
        setInterval(function() {
            fetch('/csrf-token', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                throw new Error('CSRF refresh failed with status: ' + response.status);
            })
            .then(data => {
                // Update meta tag with new token
                const metaTag = document.querySelector('meta[name="csrf-token"]');
                if (metaTag && data.token) {
                    metaTag.setAttribute('content', data.token);
                    console.log('[CSRF] Token refreshed at', data.timestamp);
                }
            })
            .catch(error => {
                console.error('[CSRF] Refresh failed:', error);
            });
        }, 600000); // 10 minutes
    </script>

    @stack('scripts')
</body>
</html>
