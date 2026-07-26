<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Salary Slips</h2>
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
                        value="{{ request('search') }}" placeholder="Employee name…" />
                </div>
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    Filter
                </button>
            </form>

            @can('create', \App\Models\SalarySlip::class)
                <a href="{{ route('salary-slips.create') }}"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    Add Salary Slip
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Employee</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Month</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Year</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Days Worked</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Overtime</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($salarySlips as $salarySlip)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $salarySlip->employee?->employee_name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $salarySlip->salary_slip_month }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $salarySlip->salary_slip_year }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ $salarySlip->day_work }}</td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ $salarySlip->over_time }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    @can('print', $salarySlip)
                                        <a href="{{ route('salary-slips.print', $salarySlip) }}" target="_blank" class="text-gray-600 hover:text-gray-900">Print</a>
                                    @endcan
                                    @can('update', $salarySlip)
                                        <a href="{{ route('salary-slips.edit', $salarySlip) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                                    @endcan
                                    @can('delete', $salarySlip)
                                        <form method="POST" action="{{ route('salary-slips.destroy', $salarySlip) }}"
                                            onsubmit="return confirm('Delete this salary slip?');">
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
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">No salary slips yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $salarySlips->links() }}
    </div>
</x-admin-layout>
