<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Email Estimate — {{ $estimate->subject }}</h2>
    </x-slot>

    <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('estimates.mail.store', $estimate) }}">
            @csrf

            <div class="space-y-5">
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />
                </div>

                <div>
                    <x-input-label for="client_name" value="Client Name" />
                    <x-text-input id="client_name" name="client_name" type="text" class="mt-1 block w-full" :value="old('client_name')" required />
                    <x-input-error class="mt-2" :messages="$errors->get('client_name')" />
                </div>
            </div>

            <p class="mt-4 text-sm text-gray-500">The estimate's PDF and Excel line-item sheet will be attached automatically.</p>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>Send</x-primary-button>
                <a href="{{ route('estimates.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
