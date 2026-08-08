@extends('identity.layout')

@section('title', 'Create Account')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-xl font-semibold text-gray-900 mb-6">Create your account</h1>

        @if (Route::has('auth.google'))
            <a href="{{ route('auth.google', array_filter(['redirect' => $returnUrl ?? null])) }}"
                class="mb-4 flex w-full items-center justify-center gap-3 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200">
                <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18A10.96 10.96 0 001 12c0 1.77.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Continue with Google
            </a>

            <div class="mb-4 flex items-center gap-3">
                <div class="h-px flex-1 bg-gray-200"></div>
                <span class="text-xs text-gray-400">or</span>
                <div class="h-px flex-1 bg-gray-200"></div>
            </div>
        @endif

        <form method="POST" action="{{ route('identity.register.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="username"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <button type="submit"
                class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Create account
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Already have an account?
            <a href="{{ route('identity.login') }}" class="text-emerald-600 hover:text-emerald-500 font-medium">
                Sign in
            </a>
        </p>
    </div>
@endsection
