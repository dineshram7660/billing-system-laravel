<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Rojmed &mdash; {{ \Illuminate\Support\Carbon::parse($date)->format('d-m-Y') }}</h2>
    </x-slot>

    <div class="space-y-4">
        <div class="flex items-center justify-between gap-4">
            <form method="GET" action="{{ route('rojmed.index') }}" class="flex items-center gap-2">
                <x-input-label for="date" value="Date" class="sr-only" />
                <x-text-input id="date" name="date" type="date" class="block" :value="$date" onchange="this.form.submit()" />
            </form>

            @can('create', \App\Models\AccountDetail::class)
                <a href="{{ route('accounts.index') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    Add a transaction from an account&rsquo;s ledger &rarr;
                </a>
            @endcan
        </div>

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
                    <tr class="bg-gray-50 font-medium">
                        <td class="px-4 py-3 text-gray-900"></td>
                        <td class="px-4 py-3 text-gray-900">Balance b/f</td>
                        <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ $openingDebit > 0 ? number_format($openingDebit, 2) : '-' }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ $openingCredit > 0 ? number_format($openingCredit, 2) : '-' }}</td>
                        <td class="px-4 py-3"></td>
                    </tr>

                    @forelse ($entries as $entry)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $entry->date?->format('d-m-Y') }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $entry->account?->account_name }} &mdash; {{ $entry->description }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ $entry->type === 'Debit' ? number_format($entry->amount, 2) : '-' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ $entry->type === 'Credit' ? number_format($entry->amount, 2) : '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('delete', $entry)
                                    <form method="POST" action="{{ route('accounts.details.destroy', [$entry->account, $entry]) }}"
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
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No transactions on this date.</td>
                        </tr>
                    @endforelse

                    <tr class="bg-gray-50 font-medium">
                        <td class="px-4 py-3 text-gray-900"></td>
                        <td class="px-4 py-3 text-gray-900">Total</td>
                        <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ $closingDebit > 0 ? number_format($closingDebit, 2) : '-' }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ $closingCredit > 0 ? number_format($closingCredit, 2) : '-' }}</td>
                        <td class="px-4 py-3"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
