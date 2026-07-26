<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Edit Attendance — {{ $attendance->employee?->employee_name }}</h2>
    </x-slot>

    <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('attendance.update', $attendance) }}">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <x-input-label for="date" value="Date" />
                    <x-text-input id="date" name="date" type="date" class="mt-1 block w-full"
                        :value="old('date', $attendance->date?->toDateString())" required />
                    <x-input-error class="mt-2" :messages="$errors->get('date')" />
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="attendance" value="0">
                    <input id="attendance" name="attendance" type="checkbox" value="1" @checked(old('attendance', $attendance->attendance))
                        class="rounded border-gray-300 text-gray-800 shadow-sm focus:ring-gray-500">
                    <x-input-label for="attendance" value="Present" class="!mb-0" />
                </div>

                <div>
                    <x-input-label for="over_time" value="Overtime" />
                    <x-text-input id="over_time" name="over_time" type="number" step="1" min="0" class="mt-1 block w-full"
                        :value="old('over_time', $attendance->over_time)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('over_time')" />
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>Save changes</x-primary-button>
                <a href="{{ route('attendance.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
