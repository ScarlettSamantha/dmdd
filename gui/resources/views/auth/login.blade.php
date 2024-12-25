@extends('layout')

@section('title', __('login.title'))

@section('content')
<div class="min-h-screen flex items-center justify-center content-background login-container">
    <div class="w-full max-w-md bg-white border-2 border-dark-purple rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6 text-center text-dark-purple login-header">{{ __('login.heading') }}</h2>
        @include('partials.flash-messages')
        @include('partials.notifications')
        <div class="login-text">
            <p class="text-sm text-gray-600">{{ __('login.text') }}</p>
        </div>

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">{{ __('login.email_label') }}</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    class="w-full mt-1 p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-dark-purple focus:border-dark-purple @error('email') border-red-500 @enderror"
                    required
                />
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">{{ __('login.password_label') }}</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="w-full mt-1 p-2 border border-gray-300 rounded-lg shadow-sm focus:ring-dark-purple focus:border-dark-purple @error('password') border-red-500 @enderror"
                    required
                />
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full bg-dark-purple text-white py-2 px-4 rounded-lg hover:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-dark-purple"
            >
                {{ __('login.submit_button') }}
            </button>
        </form>
    </div>
</div>
@endsection
