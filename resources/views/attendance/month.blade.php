@php
    $months = ['' => 'Select', 1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
@endphp
<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Add Attendance (All Month)</h2>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" class="flex items-end gap-3">
            <div>
                <x-input-label for="year_filter" value="Year" />
                <select id="year_filter" name="year" onchange="this.form.submit()"
                    class="mt-1 rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="month_filter" value="Month" />
                <select id="month_filter" name="month" onchange="this.form.submit()"
                    class="mt-1 rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">
                    @foreach ($months as $num => $label)
                        <option value="{{ $num }}" @selected($month === (int) $num)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        @if ($month > 0)
            <form method="POST" action="{{ route('attendance.month.store') }}">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">

                <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="sticky left-0 bg-gray-50 px-3 py-2 text-left font-medium text-gray-500">Employee</th>
                                @foreach ($days as $day)
                                    <th class="px-2 py-2 text-center font-medium text-gray-500">{{ \Illuminate\Support\Carbon::parse($day)->format('d') }}</th>
                                @endforeach
                                <th class="px-3 py-2 text-right font-medium text-gray-500">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($employees as $employee)
                                <tr>
                                    <td class="sticky left-0 bg-white px-3 py-2 font-medium text-gray-900" rowspan="2">{{ $employee->employee_name }}</td>
                                    @foreach ($days as $day)
                                        <td class="px-2 py-2 text-center">
                                            <input type="hidden" name="attendance[{{ $employee->id }}][{{ $day }}]" value="0">
                                            <input type="checkbox" name="attendance[{{ $employee->id }}][{{ $day }}]" value="1"
                                                @checked($grid[$employee->id][$day]['attendance'] ?? false)
                                                class="rounded border-gray-300 text-gray-800 shadow-sm focus:ring-gray-500">
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-2 text-right font-semibold text-gray-900">
                                        {{ collect($grid[$employee->id] ?? [])->sum('attendance') }}
                                    </td>
                                </tr>
                                <tr>
                                    @foreach ($days as $day)
                                        <td class="px-1 py-1">
                                            <input type="number" step="1" min="0" name="over_time[{{ $employee->id }}][{{ $day }}]"
                                                value="{{ $grid[$employee->id][$day]['over_time'] ?? 0 }}"
                                                class="block w-12 rounded-md border-gray-300 text-xs">
                                        </td>
                                    @endforeach
                                    <td class="px-3 py-1 text-right text-gray-500">
                                        {{ collect($grid[$employee->id] ?? [])->sum('over_time') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($days) + 2 }}" class="px-4 py-6 text-center text-gray-500">No eligible employees.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <x-primary-button>Save</x-primary-button>
                </div>
            </form>
        @else
            <p class="text-sm text-gray-500">Select a year and month to load the attendance grid.</p>
        @endif
    </div>
</x-admin-layout>
