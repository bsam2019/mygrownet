@extends('identity.layout')

@section('title', 'Verify Email')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-xl font-semibold text-gray-900 mb-2">Verify your email address</h1>
        <p class="text-sm text-gray-500 mb-6">
            Thanks for signing up! Before getting started, could you verify your email address by clicking the link we just emailed to you? If you didn't receive the email, we'll gladly send you another.
        </p>

        @if (session('status') === 'verification-link-sent')
            <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                A new verification link has been sent to the email address you provided during registration.
            </div>
        @endif

        <form method="POST" action="{{ route('identity.verification.send') }}" class="space-y-4">
            @csrf
            <button type="submit"
                class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Resend verification email
            </button>
        </form>

        <form method="POST" action="{{ route('identity.logout') }}" class="mt-4">
            @csrf
            <button type="submit"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                Sign out
            </button>
        </form>
    </div>
@endsection
