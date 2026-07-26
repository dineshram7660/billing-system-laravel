@php $editing = $employee->exists; @endphp
<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ $editing ? 'Edit' : 'Add' }} Employee</h2>
    </x-slot>

    <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ $editing ? route('employees.update', $employee) : route('employees.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-5">
                <div>
                    <x-input-label for="employee_name" value="Employee name" />
                    <x-text-input id="employee_name" name="employee_name" type="text" class="mt-1 block w-full"
                        :value="old('employee_name', $employee->employee_name)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('employee_name')" />
                </div>

                <div>
                    <x-input-label for="designation_id" value="Designation" />
                    <select id="designation_id" name="designation_id"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select designation</option>
                        @foreach ($designations as $designation)
                            <option value="{{ $designation->id }}" @selected(old('designation_id', $employee->designation_id) == $designation->id)>
                                {{ $designation->designation_name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('designation_id')" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="mobile_number" value="Mobile number" />
                        <x-text-input id="mobile_number" name="mobile_number" type="text" class="mt-1 block w-full"
                            :value="old('mobile_number', $employee->mobile_number)" />
                        <x-input-error class="mt-2" :messages="$errors->get('mobile_number')" />
                    </div>
                    <div>
                        <x-input-label for="card_number" value="Card number" />
                        <x-text-input id="card_number" name="card_number" type="text" class="mt-1 block w-full"
                            :value="old('card_number', $employee->card_number)" />
                        <x-input-error class="mt-2" :messages="$errors->get('card_number')" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="pf_number" value="PF number" />
                        <x-text-input id="pf_number" name="pf_number" type="text" class="mt-1 block w-full"
                            :value="old('pf_number', $employee->pf_number)" />
                        <x-input-error class="mt-2" :messages="$errors->get('pf_number')" />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="1" @selected(old('status', $employee->status ?? 1) == 1)>Active</option>
                            <option value="0" @selected(old('status', $employee->status ?? 1) == 0)>Inactive</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>{{ $editing ? 'Save changes' : 'Add employee' }}</x-primary-button>
                <a href="{{ route('employees.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                @can('viewAny', \App\Models\SalaryDetail::class)
                    @if ($editing)
                        <a href="{{ route('employees.salary-details.index', $employee) }}" class="ms-auto text-sm text-gray-600 hover:text-gray-900">Pay Rates &rarr;</a>
                    @endif
                @endcan
            </div>
        </form>
    </div>
</x-admin-layout>
