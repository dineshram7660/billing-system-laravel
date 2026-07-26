@php $editing = $account->exists; @endphp
<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ $editing ? 'Edit' : 'Add' }} Account</h2>
    </x-slot>

    <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ $editing ? route('accounts.update', $account) : route('accounts.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div>
                <x-input-label for="account_name" value="Account Name" />
                <x-text-input id="account_name" name="account_name" type="text" class="mt-1 block w-full"
                    :value="old('account_name', $account->account_name)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('account_name')" />
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>{{ $editing ? 'Save changes' : 'Add account' }}</x-primary-button>
                <a href="{{ route('accounts.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
