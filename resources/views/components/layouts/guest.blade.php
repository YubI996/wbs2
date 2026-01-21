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
        :root {
            /* Soft Blue Palette - Professional & Calming */
            --primary-50: #EBF5FF;   /* Lightest - backgrounds */
            --primary-100: #DBEAFE;  /* Light - hover states */
            --primary-200: #BFDBFE;  /* Medium light */
            --primary-300: #93C5FD;  /* Medium - accents */
            --primary-400: #60A5FA;  /* Main blue */
            --primary-500: #3B82F6;  /* Primary */
            --primary-600: #2563EB;  /* Darker - buttons */
            --primary-700: #1D4ED8;  /* Darkest - headings */
        }
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        /* Gradient dengan palette biru lembut */
        .gradient-primary {
            background: linear-gradient(135deg, #1E40AF 0%, #3B82F6 50%, #1E40AF 100%);
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
            box-shadow: 0 20px 40px rgba(30, 64, 175, 0.2);
        }
        
        /* Custom button colors */
        .btn-primary {
            background-color: #2563EB;
            color: white;
        }
        .btn-primary:hover {
            background-color: #3B82F6;
        }
        
        /* Loading animation */
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        /* Custom focus ring for accessibility */
        .focus-ring:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.4);
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
