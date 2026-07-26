<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Change Password</h2>
    </x-slot>

    <div class="space-y-6">
        <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            @include('profile.partials.update-password-form')
        </div>
    </div>
</x-admin-layout>
