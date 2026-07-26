<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Income</h2>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex justify-end">
            @can('create', \App\Models\Income::class)
                <a href="{{ route('incomes.create') }}"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    Add Income
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Department</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Amount</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($incomes as $income)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $income->income_date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $income->department?->department_name }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ number_format($income->amount, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    @can('update', $income)
                                        <a href="{{ route('incomes.edit', $income) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                                    @endcan
                                    @can('delete', $income)
                                        <form method="POST" action="{{ route('incomes.destroy', $income) }}"
                                            onsubmit="return confirm('Delete this income?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">No income recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $incomes->links() }}
    </div>
</x-admin-layout>
