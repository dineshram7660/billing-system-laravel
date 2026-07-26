@php
    $editing = $salarySlip->exists;
    $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
@endphp
<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ $editing ? 'Edit' : 'Add' }} Salary Slip</h2>
    </x-slot>

    <div class="max-w-2xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ $editing ? route('salary-slips.update', $salarySlip) : route('salary-slips.store') }}"
            x-data="salarySlipForm('{{ route('salary-slips.data') }}', {{ $editing ? 'false' : 'true' }}, @js([
                'employee_id' => old('employee_id', $salarySlip->employee_id),
                'salary_slip_month' => old('salary_slip_month', $salarySlip->salary_slip_month),
                'salary_slip_year' => old('salary_slip_year', $salarySlip->salary_slip_year),
                'day_work' => old('day_work', $salarySlip->day_work),
                'over_time' => old('over_time', $salarySlip->over_time),
            ]))">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="sm:col-span-3">
                    <x-input-label for="employee_id" value="Employee" />
                    <select id="employee_id" name="employee_id" x-model="employeeId" @change="fetchData()"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                        <option value="">Select Employee</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->employee_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('employee_id')" />
                </div>

                <div>
                    <x-input-label for="salary_slip_month" value="Salary Month" />
                    <select id="salary_slip_month" name="salary_slip_month" x-model="month" @change="fetchData()"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                        <option value="">Select Month</option>
                        @foreach ($months as $month)
                            <option value="{{ $month }}">{{ $month }}</option>
                        @endforeach
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('salary_slip_month')" />
                </div>
                <div>
                    <x-input-label for="salary_slip_year" value="Salary Year" />
                    <select id="salary_slip_year" name="salary_slip_year" x-model="year" @change="fetchData()"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                        <option value="">Select Year</option>
                        @for ($y = 2017; $y <= now()->year + 1; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                    <x-input-error class="mt-2" :messages="$errors->get('salary_slip_year')" />
                </div>

                <div>
                    <x-input-label value="Par Day Salary" />
                    <input type="text" readonly x-model="parDayAmount"
                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                </div>
                <div>
                    <x-input-label value="Par Day Extra Salary" />
                    <input type="text" readonly x-model="perDayExtra"
                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                </div>
                <div>
                    <x-input-label value="Outstanding Advance Balance" />
                    <input type="text" readonly x-model="ledgerBalance"
                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                </div>

                <div>
                    <x-input-label for="day_work" value="Work Days" />
                    <x-text-input id="day_work" name="day_work" type="number" step="1" min="0" class="mt-1 block w-full" x-model="dayWork" />
                    <x-input-error class="mt-2" :messages="$errors->get('day_work')" />
                </div>
                <div>
                    <x-input-label for="over_time" value="Overtime Hours" />
                    <x-text-input id="over_time" name="over_time" type="number" step="1" min="0" class="mt-1 block w-full" x-model="overTime" />
                    <x-input-error class="mt-2" :messages="$errors->get('over_time')" />
                </div>
                <div>
                    <x-input-label for="pf_amount" value="PF Amount" />
                    <x-text-input id="pf_amount" name="pf_amount" type="number" step="1" min="0" class="mt-1 block w-full"
                        :value="old('pf_amount', $salarySlip->pf_amount ?? 0)" />
                    <x-input-error class="mt-2" :messages="$errors->get('pf_amount')" />
                </div>

                <div>
                    <x-input-label for="advance_payment" value="Advance Payment Deduction" />
                    <x-text-input id="advance_payment" name="advance_payment" type="number" step="1" min="0" class="mt-1 block w-full"
                        :value="old('advance_payment', $salarySlip->advance_payment ?? 0)" />
                    <x-input-error class="mt-2" :messages="$errors->get('advance_payment')" />
                </div>
                <div>
                    <x-input-label for="professional_tax" value="Professional Tax" />
                    <x-text-input id="professional_tax" name="professional_tax" type="number" step="1" min="0" class="mt-1 block w-full"
                        :value="old('professional_tax', $salarySlip->professional_tax ?? 0)" />
                    <x-input-error class="mt-2" :messages="$errors->get('professional_tax')" />
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>{{ $editing ? 'Save changes' : 'Add salary slip' }}</x-primary-button>
                <a href="{{ route('salary-slips.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
