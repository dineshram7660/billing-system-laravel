<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Add Sub Admin</h2>
    </x-slot>

    <div class="max-w-lg rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <p class="mb-4 text-sm text-gray-500">
            The new sub admin's temporary password will be their first name — share it with them and have them
            change it after they log in for the first time.
        </p>

        <form method="POST" action="{{ route('sub-admins.store') }}">
            @csrf

            <div class="space-y-5">
                <div class="flex gap-4">
                    <div class="w-full">
                        <x-input-label for="first_name" value="First name" />
                        <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full"
                            :value="old('first_name')" required autofocus />
                        <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                    </div>
                    <div class="w-full">
                        <x-input-label for="last_name" value="Last name" />
                        <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full"
                            :value="old('last_name')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                    </div>
                </div>

                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                        :value="old('email')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>Add sub admin</x-primary-button>
                <a href="{{ route('sub-admins.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
