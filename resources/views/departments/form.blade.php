@php $editing = $department->exists; @endphp
<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ $editing ? 'Edit' : 'Add' }} Department</h2>
    </x-slot>

    <div class="max-w-lg rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ $editing ? route('departments.update', $department) : route('departments.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div>
                <x-input-label for="department_name" value="Department name" />
                <x-text-input id="department_name" name="department_name" type="text" class="mt-1 block w-full"
                    :value="old('department_name', $department->department_name)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('department_name')" />
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>{{ $editing ? 'Save changes' : 'Add department' }}</x-primary-button>
                <a href="{{ route('departments.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
