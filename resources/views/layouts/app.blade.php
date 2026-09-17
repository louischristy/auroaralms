<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $branding['platform_name'] ?? 'Auroara LMS')</title>
    <link rel="icon" href="{{ $branding['favicon_path'] ?? '/images/favicon.ico' }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --color-primary: {{ $branding['primary_color'] ?? '#2B4C7E' }};
            --color-secondary: {{ $branding['secondary_color'] ?? '#3A7BD5' }};
            --color-accent: {{ $branding['accent_color'] ?? '#5BC0EB' }};
            --color-neutral: {{ $branding['neutral_color'] ?? '#A8A9AD' }};
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside class="hidden lg:flex lg:flex-shrink-0">
            <div class="flex flex-col w-64 bg-white border-r border-gray-200">
                @include('layouts.partials.sidebar')
            </div>
        </aside>

        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 lg:hidden" @click="sidebarOpen = false">
            <div class="absolute inset-0 bg-gray-600 opacity-75"></div>
        </div>
        <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
             class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl lg:hidden">
            @include('layouts.partials.sidebar')
        </div>

        {{-- Main content --}}
        <div class="flex flex-col flex-1 overflow-hidden">
            {{-- Top bar --}}
            <header class="flex items-center justify-between h-16 px-4 sm:px-6 bg-white border-b border-gray-200">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="flex-1"></div>

                {{-- Notification bell --}}
                @auth
                @php $unreadCount = Auth::user()->unreadNotifications()->count(); @endphp
                <a href="{{ route('notifications.index') }}" class="relative p-2 text-gray-500 hover:text-gray-700 mr-2" title="Notifications">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($unreadCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                    @endif
                </a>
                @endauth

                {{-- User menu --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-700 hover:text-gray-900">
                        <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-xs font-bold">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <span class="hidden sm:block">{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false"
                         x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 py-1 z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Sign Out</button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                {{-- Flash messages --}}
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                         class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <button @click="show = false" class="text-green-600 hover:text-green-800">&times;</button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif
                @if(session('info'))
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg">
                        {{ session('info') }}
                    </div>
                @endif

                @yield('content')
            </main>

            {{-- Footer --}}
            @if($branding['show_powered_by'] ?? true)
                <footer class="px-6 py-3 text-center text-xs text-gray-400 border-t border-gray-100">
                    {{ $branding['powered_by_text'] ?? 'Powered by Auroara Technologies' }}
                </footer>
            @endif
        </div>
    </div>
</body>
</html>
