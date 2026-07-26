@php $editing = $expense->exists; @endphp
<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ $editing ? 'Edit' : 'Add' }} Expense</h2>
    </x-slot>

    <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ $editing ? route('expenses.update', $expense) : route('expenses.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-5">
                <div>
                    <x-input-label for="expenses_date" value="Date" />
                    <x-text-input id="expenses_date" name="expenses_date" type="date" class="mt-1 block w-full"
                        :value="old('expenses_date', $expense->expenses_date?->toDateString())" required />
                    <x-input-error class="mt-2" :messages="$errors->get('expenses_date')" />
                </div>

                <div>
                    <x-input-label for="d_id" value="Department" />
                    <select id="d_id" name="d_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                        <option value="">Select Department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(old('d_id', $expense->d_id) == $department->id)>
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('d_id')" />
                </div>

                <div>
                    <x-input-label for="amount" value="Amount" />
                    <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="mt-1 block w-full"
                        :value="old('amount', $expense->amount)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                </div>

                <div>
                    <x-input-label for="description" value="Description" />
                    <textarea id="description" name="description" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('description', $expense->description) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('description')" />
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>{{ $editing ? 'Save changes' : 'Add expense' }}</x-primary-button>
                <a href="{{ route('expenses.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
