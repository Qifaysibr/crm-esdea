@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Account Settings') }}
        </h2>
        <p class="text-sm text-gray-500 mt-1">Manage your account information and password.</p>
    </div>

    <div class="max-w-4xl space-y-6">
        <!-- Profile Information -->
        <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
            <div class="max-w-xl">
                <section>
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Profile Information') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __("Update your account's profile information and email address.") }}
                        </p>
                    </header>

                    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
                        @csrf
                        @method('patch')

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input id="name" name="name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
                            @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input id="email" name="email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('email', $user->email) }}" required autocomplete="username" />
                            @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input id="phone" name="phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" value="{{ old('phone', $user->phone) }}" autocomplete="tel" />
                            @error('phone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Save') }}
                            </button>

                            @if (session('status') === 'profile-updated')
                                <p
                                    x-data="{ show: true }"
                                    x-show="show"
                                    x-transition
                                    x-init="setTimeout(() => show = false, 2000)"
                                    class="text-sm text-gray-600"
                                >{{ __('Saved.') }}</p>
                            @endif
                        </div>
                    </form>
                </section>
            </div>
        </div>

        <!-- Role & Store (Read-only) -->
        <div class="p-4 sm:p-8 bg-gray-50 border border-gray-200 shadow sm:rounded-lg">
            <div class="max-w-xl">
                <header>
                    <h2 class="text-lg font-medium text-gray-900">
                        {{ __('Authorized Status') }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __("Your current role and assigned store in the system.") }}
                    </p>
                </header>

                <div class="mt-6 grid grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded border border-gray-200">
                        <p class="text-xs font-bold text-gray-400 uppercase">Current Role</p>
                        <p class="mt-1 text-sm font-bold text-indigo-600">{{ $user->roles->first()->display_name ?? 'Regular User' }}</p>
                    </div>
                    <div class="bg-white p-4 rounded border border-gray-200">
                        <p class="text-xs font-bold text-gray-400 uppercase">Assigned Store</p>
                        <p class="mt-1 text-sm font-bold text-gray-700">{{ $user->store ?? 'Global / All Access' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
