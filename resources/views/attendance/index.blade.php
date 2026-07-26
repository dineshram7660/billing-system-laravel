<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Attendance</h2>
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

            @can('create', \App\Models\Attendance::class)
                <div class="flex gap-3">
                    <a href="{{ route('attendance.create') }}"
                        class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                        Add Attendance
                    </a>
                    <a href="{{ route('attendance.month') }}"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                        Add Attendance (All Month)
                    </a>
                </div>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Employee</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Present</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Overtime</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($attendances as $attendance)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $attendance->employee?->employee_name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $attendance->date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $attendance->attendance ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $attendance->attendance ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-gray-900">{{ $attendance->over_time }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    @can('update', \App\Models\Attendance::class)
                                        <a href="{{ route('attendance.edit', $attendance) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                                    @endcan
                                    @can('delete', \App\Models\Attendance::class)
                                        <form method="POST" action="{{ route('attendance.destroy', $attendance) }}"
                                            onsubmit="return confirm('Delete this attendance record?');">
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
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No attendance records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $attendances->links() }}
    </div>
</x-admin-layout>
