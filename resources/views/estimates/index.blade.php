<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Estimates</h2>
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
                        value="{{ request('search') }}" placeholder="Subject…" />
                </div>
                <div>
                    <x-input-label for="from_date" value="From" />
                    <x-text-input id="from_date" name="from_date" type="date" class="mt-1" value="{{ request('from_date') }}" />
                </div>
                <div>
                    <x-input-label for="to_date" value="To" />
                    <x-text-input id="to_date" name="to_date" type="date" class="mt-1" value="{{ request('to_date') }}" />
                </div>
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    Filter
                </button>
            </form>

            @can('create', \App\Models\Estimate::class)
                <a href="{{ route('estimates.create') }}"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    Add Estimate
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Subject</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Total</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($estimates as $estimate)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $estimate->subject }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $estimate->bill_date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ number_format($estimate->total, 2) }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    @can('print', $estimate)
                                        <a href="{{ route('estimates.print', $estimate) }}" target="_blank" class="text-gray-600 hover:text-gray-900">Print</a>
                                    @endcan
                                    @can('update', $estimate)
                                        <a href="{{ route('estimates.edit', $estimate) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                                    @endcan
                                    @can('delete', $estimate)
                                        <form method="POST" action="{{ route('estimates.destroy', $estimate) }}"
                                            onsubmit="return confirm('Delete this estimate?');">
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
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">No estimates yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $estimates->links() }}
    </div>
</x-admin-layout>
