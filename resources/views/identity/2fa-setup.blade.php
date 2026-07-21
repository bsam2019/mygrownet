@extends('identity.layout')

@section('title', 'Two-Factor Authentication')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-xl font-semibold text-gray-900 mb-6">Two-Factor Authentication</h1>

        <p class="text-sm text-gray-500 mb-6">
            Two-factor authentication adds an additional layer of security to your account. This feature is not yet fully implemented. Please check back later.
        </p>

        <a href="{{ route('workspace') }}"
            class="block w-full text-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
            Go to workspace
        </a>
    </div>
@endsection
