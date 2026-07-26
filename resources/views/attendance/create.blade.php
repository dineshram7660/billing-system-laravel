<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Add Attendance</h2>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" class="flex items-end gap-3">
            <div>
                <x-input-label for="date_filter" value="Date" />
                <x-text-input id="date_filter" name="date" type="date" class="mt-1" value="{{ $date }}" onchange="this.form.submit()" />
            </div>
        </form>

        <form method="POST" action="{{ route('attendance.store') }}">
            @csrf
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Employee</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Present</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Overtime</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($employees as $employee)
                            @php $record = $existing->get($employee->id) @endphp
                            <tr>
                                <td class="px-4 py-3 text-gray-900">{{ $employee->employee_name }}</td>
                                <td class="px-4 py-3">
                                    <input type="hidden" name="attendance[{{ $employee->id }}]" value="0">
                                    <input type="checkbox" name="attendance[{{ $employee->id }}]" value="1"
                                        @checked(old("attendance.{$employee->id}", $record?->attendance))
                                        class="rounded border-gray-300 text-gray-800 shadow-sm focus:ring-gray-500">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" step="1" min="0" name="over_time[{{ $employee->id }}]"
                                        value="{{ old('over_time.'.$employee->id, $record?->over_time ?? 0) }}"
                                        class="block w-24 rounded-md border-gray-300 text-sm">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500">No eligible employees.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <x-primary-button>Save</x-primary-button>
            </div>
        </form>
    </div>
</x-admin-layout>
