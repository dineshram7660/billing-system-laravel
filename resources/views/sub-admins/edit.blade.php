<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Edit Sub Admin — {{ $user->name }}</h2>
    </x-slot>

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="max-w-lg rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-800">Basic information</h3>
            <form method="POST" action="{{ route('sub-admins.update', $user) }}" class="mt-4">
                @csrf
                @method('PUT')

                <div class="space-y-5">
                    <div class="flex gap-4">
                        <div class="w-full">
                            <x-input-label for="first_name" value="First name" />
                            <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full"
                                :value="old('first_name', $user->first_name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                        </div>
                        <div class="w-full">
                            <x-input-label for="last_name" value="Last name" />
                            <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full"
                                :value="old('last_name', $user->last_name)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                        </div>
                    </div>
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email', $user->email)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>
                </div>

                <div class="mt-6">
                    <x-primary-button>Save changes</x-primary-button>
                </div>
            </form>
        </div>

        <div class="max-w-lg rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-800">Change password</h3>
            <form method="POST" action="{{ route('sub-admins.password', $user) }}" class="mt-4">
                @csrf
                @method('PUT')

                <x-input-label for="password" value="New password" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" required />
                <x-input-error class="mt-2" :messages="$errors->get('password')" />

                <div class="mt-6">
                    <x-primary-button>Update password</x-primary-button>
                </div>
            </form>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-800">Permissions</h3>
            <p class="mt-1 text-sm text-gray-500">What this sub admin can see and do.</p>

            <form method="POST" action="{{ route('sub-admins.access', $user) }}" class="mt-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-2 gap-x-6 gap-y-2 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($permissions as $permission)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="sub_access[]" value="{{ $permission->name }}"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                @checked(in_array($permission->name, $grantedPermissions, true))>
                            {{ $permission->name }}
                        </label>
                    @endforeach
                </div>

                <div class="mt-6">
                    <x-primary-button>Save permissions</x-primary-button>
                </div>
            </form>
        </div>

        <a href="{{ route('sub-admins.index') }}" class="inline-block text-sm text-gray-600 hover:text-gray-900">← Back to sub admins</a>
    </div>
</x-admin-layout>
