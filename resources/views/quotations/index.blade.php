<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Quotations</h2>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-wrap items-end justify-between gap-3">
            <form method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="search" value="Search" />
                    <x-text-input id="search" name="search" type="text" class="mt-1 w-56"
                        value="{{ request('search') }}" placeholder="Subject, to, particulars…" />
                </div>
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    Filter
                </button>
            </form>

            @can('create', \App\Models\Quotation::class)
                <a href="{{ route('quotations.create') }}"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    Add Quotation
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Subject</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Unit</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Total</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($quotations as $quotation)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $quotation->subject }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $quotation->unit }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $quotation->bill_date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ number_format($quotation->total, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    @can('print', $quotation)
                                        <a href="{{ route('quotations.print', $quotation) }}" target="_blank" class="text-gray-600 hover:text-gray-900">Print</a>
                                        <a href="{{ route('quotations.pdf', $quotation) }}" class="text-gray-600 hover:text-gray-900">PDF</a>
                                    @endcan
                                    @can('update', $quotation)
                                        <a href="{{ route('quotations.edit', $quotation) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                                    @endcan
                                    @can('delete', $quotation)
                                        <form method="POST" action="{{ route('quotations.destroy', $quotation) }}"
                                            onsubmit="return confirm('Delete this quotation?');">
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
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No quotations yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $quotations->links() }}
    </div>
</x-admin-layout>
