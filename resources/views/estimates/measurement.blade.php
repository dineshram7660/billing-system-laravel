@php $entity = $estimate; $subjectLabel = $estimate->subject; @endphp
<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Measurement Sheet — {{ $subjectLabel }}</h2>
    </x-slot>

    <div x-data="measurementForm(@js($groups))">
        @if (session('status'))
            <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-4 flex justify-end gap-3">
            @can('printMeasurement', $entity)
                <a href="{{ route('estimates.measurement.print', $estimate) }}" target="_blank"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                    Print
                </a>
                <a href="{{ route('estimates.measurement.pdf', $estimate) }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                    Download PDF
                </a>
            @endcan
        </div>

        <form method="POST" action="{{ route('estimates.measurement.update', $estimate) }}">
            @csrf
            @method('PUT')

            <template x-for="(group, groupIndex) in groups" :key="groupIndex">
                <div class="mb-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-sm font-semibold uppercase tracking-widest text-gray-500">Product Group</h3>
                        <button type="button" @click="removeGroup(groupIndex)" class="text-sm text-red-600 hover:text-red-800">Remove Group</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500">Service No</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500">Description</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500">No.</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500">Length</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500">Breath</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500">Unit</th>
                                    <th class="px-2 py-2 text-left font-medium text-gray-500">Quantity</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="(line, lineIndex) in group.lines" :key="lineIndex">
                                    <tr>
                                        <td class="px-1 py-1">
                                            <input type="text" x-model="line.service_no" :name="`groups[${groupIndex}][lines][${lineIndex}][service_no]`" class="block w-24 rounded-md border-gray-300 text-sm">
                                        </td>
                                        <td class="px-1 py-1">
                                            <input type="text" x-model="line.description" :name="`groups[${groupIndex}][lines][${lineIndex}][description]`" class="block w-48 rounded-md border-gray-300 text-sm">
                                        </td>
                                        <td class="px-1 py-1">
                                            <input type="text" x-model="line.no" @input="recalcLine(groupIndex, lineIndex)" :name="`groups[${groupIndex}][lines][${lineIndex}][no]`" class="block w-16 rounded-md border-gray-300 text-sm">
                                        </td>
                                        <td class="px-1 py-1">
                                            <input type="text" x-model="line.length" @input="recalcLine(groupIndex, lineIndex)" :name="`groups[${groupIndex}][lines][${lineIndex}][length]`" class="block w-16 rounded-md border-gray-300 text-sm">
                                        </td>
                                        <td class="px-1 py-1">
                                            <input type="text" x-model="line.breath" @input="recalcLine(groupIndex, lineIndex)" :name="`groups[${groupIndex}][lines][${lineIndex}][breath]`" class="block w-16 rounded-md border-gray-300 text-sm">
                                        </td>
                                        <td class="px-1 py-1">
                                            <input type="text" x-model="line.unit" @input="recalcLine(groupIndex, lineIndex)" :name="`groups[${groupIndex}][lines][${lineIndex}][unit]`" class="block w-16 rounded-md border-gray-300 text-sm">
                                        </td>
                                        <td class="px-1 py-1">
                                            <input type="text" x-model="line.quantity" :name="`groups[${groupIndex}][lines][${lineIndex}][quantity]`" class="block w-20 rounded-md border-gray-300 text-sm">
                                        </td>
                                        <td class="px-1 py-1 text-right">
                                            <button type="button" @click="removeLine(groupIndex, lineIndex)" class="text-red-600 hover:text-red-800">&times;</button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 flex flex-wrap items-end gap-3">
                        <button type="button" @click="addLine(groupIndex)" class="text-sm text-gray-600 hover:text-gray-900">+ Add Line</button>

                        <div class="ms-auto flex items-end gap-3">
                            <div>
                                <x-input-label value="Total" />
                                <input type="text" x-model="group.total" :name="`groups[${groupIndex}][total]`" class="mt-1 block w-24 rounded-md border-gray-300 text-sm">
                            </div>
                            <div>
                                <x-input-label value="Total (words)" />
                                <input type="text" x-model="group.total_text" :name="`groups[${groupIndex}][total_text]`" class="mt-1 block w-32 rounded-md border-gray-300 text-sm">
                            </div>
                            <div>
                                <x-input-label value="Unit" />
                                <input type="text" x-model="group.total_unit" :name="`groups[${groupIndex}][total_unit]`" class="mt-1 block w-24 rounded-md border-gray-300 text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <div class="mb-6">
                <button type="button" @click="addGroup()"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">
                    + Add Product Group
                </button>
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>Save</x-primary-button>
                <a href="{{ route('estimates.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
