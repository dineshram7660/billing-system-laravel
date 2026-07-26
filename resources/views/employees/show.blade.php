<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Employee Ledger — {{ $employee->employee_name }}</h2>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex items-center justify-between">
            <a href="{{ route('employees.index') }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to employees</a>
            <div class="text-sm font-semibold text-gray-900">Balance: {{ number_format($balance, 2) }}</div>
        </div>

        @can('create', \App\Models\EmployeeDetail::class)
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-gray-500">Add Transaction</h3>
                <form method="POST" action="{{ route('employees.details.store', $employee) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-5">
                    @csrf
                    <div>
                        <x-input-label for="date" value="Date" />
                        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', now()->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('date')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="description" value="Description" />
                        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>
                    <div>
                        <x-input-label for="type" value="Type" />
                        <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            <option value="Debit">Debit</option>
                            <option value="Credit">Credit</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('type')" />
                    </div>
                    <div>
                        <x-input-label for="amount" value="Amount" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('amount')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('amount')" />
                    </div>
                    <div class="sm:col-span-5">
                        <x-primary-button>Add Transaction</x-primary-button>
                    </div>
                </form>
            </div>
        @endcan

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Description</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Debit</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Credit</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($details as $detail)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $detail->date?->format('d-m-Y') }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $detail->description }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ $detail->type === 'Debit' ? number_format($detail->amount, 2) : '-' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ $detail->type === 'Credit' ? number_format($detail->amount, 2) : '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('delete', $detail)
                                    <form method="POST" action="{{ route('employees.details.destroy', [$employee, $detail]) }}"
                                        onsubmit="return confirm('Delete this transaction?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No transactions yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $details->links() }}
    </div>
</x-admin-layout>
