<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Dashboard</h2>
    </x-slot>

    <div class="space-y-6">
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            This is the Laravel rebuild, running against a copy of the live database.
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach ([
                ['label' => 'Bills', 'value' => \App\Models\Bill::count()],
                ['label' => 'Estimates', 'value' => \App\Models\Estimate::count()],
                ['label' => 'Employees', 'value' => \App\Models\Employee::where('status', 1)->count()],
                ['label' => 'Departments', 'value' => \App\Models\Department::count()],
            ] as $stat)
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-2xl font-semibold tabular-nums text-gray-900">{{ number_format($stat['value']) }}</p>
                    <p class="mt-1 text-sm text-gray-500">{{ $stat['label'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-800">Department Overview</h3>

            <form method="GET" class="mt-4 flex flex-wrap items-end gap-3">
                <div>
                    <x-input-label for="department_id" value="Department" />
                    <select id="department_id" name="department_id" onchange="this.form.submit()"
                        class="mt-1 rounded-md border-gray-300 text-sm focus:border-gray-500 focus:ring-gray-500">
                        <option value="">Select Department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="from_date" value="From" />
                    <x-text-input id="from_date" name="from_date" type="date" class="mt-1" value="{{ $fromDate }}" />
                </div>
                <div>
                    <x-input-label for="to_date" value="To" />
                    <x-text-input id="to_date" name="to_date" type="date" class="mt-1" value="{{ $toDate }}" />
                </div>
                <button type="submit"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    View
                </button>
            </form>

            @if ($overview)
                <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-md border border-gray-200 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500">Income</dt>
                        <dd class="mt-1 text-xl font-semibold tabular-nums text-green-700">{{ number_format($overview['income'], 2) }}</dd>
                    </div>
                    <div class="rounded-md border border-gray-200 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500">Expense (incl. billed work)</dt>
                        <dd class="mt-1 text-xl font-semibold tabular-nums text-red-700">{{ number_format($overview['expense'], 2) }}</dd>
                    </div>
                    <div class="rounded-md border border-gray-200 p-4">
                        <dt class="text-xs uppercase tracking-wide text-gray-500">Net</dt>
                        <dd class="mt-1 text-xl font-semibold tabular-nums {{ $overview['total'] >= 0 ? 'text-gray-900' : 'text-red-700' }}">{{ number_format($overview['total'], 2) }}</dd>
                    </div>
                </dl>
            @else
                <p class="mt-4 text-sm text-gray-500">Select a department to see its income/expense overview.</p>
            @endif
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-800">Signed in as</h3>
            <dl class="mt-3 grid grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                <div class="flex justify-between border-b border-gray-100 py-2 sm:border-0 sm:py-0">
                    <dt class="text-gray-500">Name</dt>
                    <dd class="font-medium text-gray-900">{{ auth()->user()->name }}</dd>
                </div>
                <div class="flex justify-between border-b border-gray-100 py-2 sm:border-0 sm:py-0">
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium text-gray-900">{{ auth()->user()->email }}</dd>
                </div>
                <div class="flex justify-between border-b border-gray-100 py-2 sm:border-0 sm:py-0">
                    <dt class="text-gray-500">Role</dt>
                    <dd class="font-medium text-gray-900">{{ auth()->user()->isFullAdmin() ? 'Full admin' : 'Sub admin' }}</dd>
                </div>
                @unless (auth()->user()->isFullAdmin())
                    <div class="flex justify-between border-b border-gray-100 py-2 sm:border-0 sm:py-0">
                        <dt class="text-gray-500">Permissions</dt>
                        <dd class="font-medium text-gray-900">{{ implode(', ', auth()->user()->permissions()) ?: '—' }}</dd>
                    </div>
                @endunless
            </dl>
        </div>
    </div>
</x-admin-layout>
