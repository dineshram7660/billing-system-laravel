<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">GST Report</h2>
    </x-slot>

    <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('gst-report.show') }}" target="_blank">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-input-label for="start_date" value="Start Date" />
                    <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full"
                        value="{{ now()->toDateString() }}" required />
                </div>
                <div>
                    <x-input-label for="end_date" value="End Date" />
                    <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full"
                        value="{{ now()->toDateString() }}" required />
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>View Report</x-primary-button>
                <button type="submit" formaction="{{ route('gst-report.pdf') }}" formtarget="_self"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                    Download PDF
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>
