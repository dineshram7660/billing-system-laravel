<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Inquiries</h2>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <x-input-label for="search" value="Search" />
                <x-text-input id="search" name="search" type="text" class="mt-1 w-56"
                    value="{{ request('search') }}" placeholder="Name, email, subject…" />
            </div>
            <button type="submit"
                class="inline-flex items-center rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">
                Filter
            </button>
        </form>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Email</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Subject</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Message</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($inquiries as $inquiry)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ trim("{$inquiry->fname} {$inquiry->lname}") }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $inquiry->email }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $inquiry->subject }}</td>
                            <td class="px-4 py-3 max-w-xs truncate text-gray-500">{{ $inquiry->message }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $inquiry->date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('delete', $inquiry)
                                    <form method="POST" action="{{ route('inquiries.destroy', $inquiry) }}"
                                        onsubmit="return confirm('Delete this inquiry?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">No inquiries yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $inquiries->links() }}
    </div>
</x-admin-layout>
