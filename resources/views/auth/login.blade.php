@extends('layouts.guest')

@section('title', 'Sign In — Auroara LMS')

@section('content')
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Sign in to your account</h2>

    @if(session('status'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div x-data="loginForm()" x-cloak>
        <form method="POST" action="{{ route('login') }}" class="space-y-5" @submit="handleSubmit($event)">
            @csrf

            <div>
                <label for="email" class="label">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       class="input @error('email') border-red-500 @enderror"
                       x-model="email"
                       @blur="checkSso()">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- SSO buttons (shown when detected) --}}
            <template x-if="ssoProviders.length > 0">
                <div class="space-y-3">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                        <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-gray-400">Sign in with</span></div>
                    </div>
                    <template x-for="p in ssoProviders" :key="p.provider">
                        <a :href="p.url"
                           class="flex items-center justify-center gap-3 w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                            <template x-if="p.provider === 'google'">
                                <svg class="w-5 h-5" viewBox="0 0 24 24">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/>
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                </svg>
                            </template>
                            <template x-if="p.provider === 'microsoft'">
                                <svg class="w-5 h-5" viewBox="0 0 21 21">
                                    <rect x="1" y="1" width="9" height="9" fill="#F25022"/>
                                    <rect x="11" y="1" width="9" height="9" fill="#7FBA00"/>
                                    <rect x="1" y="11" width="9" height="9" fill="#00A4EF"/>
                                    <rect x="11" y="11" width="9" height="9" fill="#FFB900"/>
                                </svg>
                            </template>
                            <span x-text="'Continue with ' + p.label"></span>
                        </a>
                    </template>
                </div>
            </template>

            {{-- Password field (hidden when force_sso) --}}
            <div x-show="!forceSso">
                <template x-if="ssoProviders.length > 0">
                    <div class="relative my-4">
                        <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                        <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-gray-400">Or with password</span></div>
                    </div>
                </template>

                <div>
                    <label for="password" class="label">Password</label>
                    <input type="password" id="password" name="password"
                           class="input @error('password') border-red-500 @enderror"
                           :required="!forceSso">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between mt-4">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-primary focus:ring-primary">
                        Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm text-secondary hover:text-primary">Forgot password?</a>
                </div>

                <button type="submit" class="btn-primary w-full mt-5">Sign In</button>
            </div>

            {{-- Force SSO message --}}
            <div x-show="forceSso" class="text-center text-sm text-gray-500 mt-2">
                Your organization requires SSO sign-in. Use the button above.
            </div>
        </form>
    </div>

    <script>
    function loginForm() {
        return {
            email: '{{ old("email") }}',
            ssoProviders: [],
            forceSso: false,
            checking: false,
            lastChecked: '',

            async checkSso() {
                if (!this.email || !this.email.includes('@') || this.email === this.lastChecked) return;
                this.lastChecked = this.email;

                try {
                    const res = await fetch('{{ route("login.check-sso") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ email: this.email }),
                    });
                    const data = await res.json();

                    if (data.sso) {
                        this.ssoProviders = data.providers;
                        this.forceSso = data.force_sso;
                    } else {
                        this.ssoProviders = [];
                        this.forceSso = false;
                    }
                } catch (e) {
                    console.error('SSO check failed', e);
                }
            },

            handleSubmit(event) {
                if (this.forceSso) {
                    event.preventDefault();
                }
            }
        }
    }
    </script>
@endsection
