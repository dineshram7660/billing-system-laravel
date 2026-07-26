<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Pay Rates — {{ $employee->employee_name }}</h2>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <a href="{{ route('employees.edit', $employee) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to {{ $employee->employee_name }}</a>

        @can('create', \App\Models\SalaryDetail::class)
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-gray-500">Add Pay Rate</h3>
                <form method="POST" action="{{ route('employees.salary-details.store', $employee) }}" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                    @csrf
                    <div>
                        <x-input-label for="par_day_amount" value="Par Day Salary" />
                        <x-text-input id="par_day_amount" name="par_day_amount" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('par_day_amount')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('par_day_amount')" />
                    </div>
                    <div>
                        <x-input-label for="per_day_extra" value="Par Day Extra" />
                        <x-text-input id="per_day_extra" name="per_day_extra" type="number" step="0.01" min="0" class="mt-1 block w-full" :value="old('per_day_extra')" required />
                        <x-input-error class="mt-2" :messages="$errors->get('per_day_extra')" />
                    </div>
                    <div>
                        <x-input-label for="date" value="Effective From" />
                        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full" :value="old('date', now()->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('date')" />
                    </div>
                    <div class="flex items-end">
                        <x-primary-button>Add Rate</x-primary-button>
                    </div>
                </form>
            </div>
        @endcan

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Effective From</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Par Day Salary</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Par Day Extra</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($salaryDetails as $salaryDetail)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $salaryDetail->date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ number_format($salaryDetail->par_day_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ number_format($salaryDetail->per_day_extra, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('delete', $salaryDetail)
                                    <form method="POST" action="{{ route('employees.salary-details.destroy', [$employee, $salaryDetail]) }}"
                                        onsubmit="return confirm('Delete this pay rate?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">No pay rates recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
