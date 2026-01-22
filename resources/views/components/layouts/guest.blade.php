<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Whistle Blowing System Kota Bontang - Laporkan dugaan pelanggaran secara aman dan rahasia">
    <title>{{ $title ?? 'WBS Kota Bontang' }}</title>
    
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
</head>
<body class="bg-gray-50 antialiased">
    <!-- Skip to content for accessibility -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded">
        Langsung ke konten utama
    </a>
    
    {{ $slot }}
    
    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>
