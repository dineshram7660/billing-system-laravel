<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Departments</h2>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex justify-end">
            @can('create', \App\Models\Department::class)
                <a href="{{ route('departments.create') }}"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    Add Department
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Department name</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($departments as $department)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $department->department_name }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    @can('update', $department)
                                        <a href="{{ route('departments.edit', $department) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                                    @endcan
                                    @can('delete', $department)
                                        <form method="POST" action="{{ route('departments.destroy', $department) }}"
                                            onsubmit="return confirm('Delete this department?');">
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
                            <td colspan="2" class="px-4 py-6 text-center text-gray-500">No departments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $departments->links() }}
    </div>
</x-admin-layout>
