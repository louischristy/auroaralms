<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Certificate Verification — Auroara LMS')</title>
    @vite(['resources/css/app.css'])
    <style>
        :root {
            --color-primary: #6366F1;
            --color-secondary: #8B5CF6;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased min-h-screen flex flex-col">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2 text-indigo-600 font-bold text-lg">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
                Auroara LMS
            </a>
            <span class="text-sm text-gray-500">Certificate Verification</span>
        </div>
    </header>

    <main class="flex-1 max-w-3xl mx-auto w-full px-4 py-8">
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white">
        <div class="max-w-3xl mx-auto px-4 py-4 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} Auroara LMS. Certificate verification is provided for authentication purposes.
        </div>
    </footer>
</body>
</html>
