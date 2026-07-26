<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Bills</h2>
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
                        value="{{ request('search') }}" placeholder="Invoice, subject, sir name, ref no…" />
                </div>
                <div>
                    <x-input-label for="year" value="Fiscal year" />
                    <select id="year" name="year" onchange="this.form.submit()"
                        class="mt-1 rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">
                        @foreach ($years as $y)
                            <option value="{{ $y }}" @selected($year === $y)>{{ $y }}–{{ $y + 1 }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    Filter
                </button>
            </form>

            @can('create', \App\Models\Bill::class)
                <a href="{{ route('bills.create') }}"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    Add Bill
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Invoice No</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Subject</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Department</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Bill Date</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Total</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Paid</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($bills as $bill)
                        <tr>
                            <td class="px-4 py-3 tabular-nums text-gray-900">{{ $bill->invoice_no }}</td>
                            <td class="px-4 py-3 text-gray-900">{{ $bill->subject }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $bill->department?->department_name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $bill->bill_date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ number_format($bill->total, 2) }}</td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $bill->paid ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $bill->paid ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    @can('print', $bill)
                                        <a href="{{ route('bills.print', $bill) }}" target="_blank" class="text-gray-600 hover:text-gray-900">Print</a>
                                        <a href="{{ route('bills.pdf', $bill) }}" class="text-gray-600 hover:text-gray-900">PDF</a>
                                    @endcan
                                    @can('update', $bill)
                                        <a href="{{ route('bills.edit', $bill) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                                        <a href="{{ route('bills.photos.edit', $bill) }}" class="text-gray-600 hover:text-gray-900">Photos</a>
                                    @endcan
                                    @can('delete', $bill)
                                        <form method="POST" action="{{ route('bills.destroy', $bill) }}"
                                            onsubmit="return confirm('Delete this bill?');">
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
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">No bills for this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $bills->links() }}
    </div>
</x-admin-layout>
