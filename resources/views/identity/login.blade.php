@extends('identity.layout')

@section('title', 'Sign In')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-xl font-semibold text-gray-900 mb-6">Sign in to your account</h1>

        <form method="POST" action="{{ route('identity.login.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required autocomplete="current-password"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span class="ml-2">Remember me</span>
                </label>

                @if (Route::has('identity.password.request'))
                    <a href="{{ route('identity.password.request') }}" class="text-sm text-emerald-600 hover:text-emerald-500">
                        Forgot password?
                    </a>
                @endif
            </div>

            <button type="submit"
                class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Sign in
            </button>
        </form>

        @if (Route::has('identity.register'))
            <p class="mt-6 text-center text-sm text-gray-500">
                Don't have an account?
                <a href="{{ route('identity.register') }}" class="text-emerald-600 hover:text-emerald-500 font-medium">
                    Create one
                </a>
            </p>
        @endif
    </div>
@endsection
