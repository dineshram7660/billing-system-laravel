<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Dashboard</h2>
    </x-slot>

    <div class="space-y-6">
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            This is the Laravel rebuild, running against a copy of the live database. The modules greyed out in the
            sidebar (marked <span class="rounded-full bg-slate-800 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-slate-300">Soon</span>)
            haven't been ported yet — see the migration roadmap for the phase order.
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
