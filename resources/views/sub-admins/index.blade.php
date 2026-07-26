<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Sub Admins</h2>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex justify-end">
            @can('create', \App\Models\User::class)
                <a href="{{ route('sub-admins.create') }}"
                    class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                    Add Sub Admin
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Email</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($subAdmins as $subAdmin)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $subAdmin->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $subAdmin->email }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-3">
                                    @can('update', $subAdmin)
                                        <a href="{{ route('sub-admins.edit', $subAdmin) }}" class="text-gray-600 hover:text-gray-900">Edit</a>
                                    @endcan
                                    @can('delete', $subAdmin)
                                        <form method="POST" action="{{ route('sub-admins.destroy', $subAdmin) }}"
                                            onsubmit="return confirm('Delete this sub admin?');">
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
                            <td colspan="3" class="px-4 py-6 text-center text-gray-500">No sub admins yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $subAdmins->links() }}
    </div>
</x-admin-layout>
