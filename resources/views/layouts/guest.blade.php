<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Auroara LMS')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-primary: #2B4C7E;
            --color-secondary: #3A7BD5;
            --color-accent: #5BC0EB;
            --color-neutral: #A8A9AD;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <img src="/images/auroara-logo.png" alt="Auroara LMS" class="h-12 mx-auto mb-4">
                <h1 class="text-2xl font-bold" style="color: var(--color-primary)">Auroara LMS</h1>
                <p class="text-sm text-gray-500 mt-1">Cybersecurity Awareness Training Platform</p>
            </div>

            <div class="card p-6 sm:p-8">
                @yield('content')
            </div>

            <p class="text-center text-xs text-gray-400 mt-6">
                &copy; {{ date('Y') }} Auroara Technologies Sdn Bhd. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
