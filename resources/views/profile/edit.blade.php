@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Profile Settings</h1>
                <p class="text-sm text-gray-500 mt-1">Manage your account settings and preferences</p>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="p-6">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
