@extends('identity.layout')

@section('title', 'Forgot Password')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-xl font-semibold text-gray-900 mb-2">Forgot your password?</h1>
        <p class="text-sm text-gray-500 mb-6">Enter your email address and we'll send you a password reset link.</p>

        <form method="POST" action="{{ route('identity.password.email') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>

            <button type="submit"
                class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Send reset link
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            <a href="{{ route('identity.login') }}" class="text-emerald-600 hover:text-emerald-500 font-medium">
                Back to sign in
            </a>
        </p>
    </div>
@endsection
