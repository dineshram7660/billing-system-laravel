<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Sent Emails</h2>
    </x-slot>

    <div class="space-y-4">
        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Client Name</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Email</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Type</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($emailSends as $emailSend)
                        <tr>
                            <td class="px-4 py-3 text-gray-900">{{ $emailSend->client_name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $emailSend->email }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $emailSend->type }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $emailSend->date?->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">No emails sent yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $emailSends->links() }}
    </div>
</x-admin-layout>
