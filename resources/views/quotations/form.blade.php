@php $editing = $quotation->exists; @endphp
<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ $editing ? 'Edit' : 'Add' }} Quotation</h2>
    </x-slot>

    <div class="max-w-2xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ $editing ? route('quotations.update', $quotation) : route('quotations.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-5">
                <div>
                    <x-input-label for="quotation_to" value="To" />
                    <textarea id="quotation_to" name="quotation_to" rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('quotation_to', $quotation->quotation_to) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('quotation_to')" />
                </div>

                <div>
                    <x-input-label for="subject" value="Subject" />
                    <x-text-input id="subject" name="subject" type="text" class="mt-1 block w-full"
                        :value="old('subject', $quotation->subject)" required />
                    <x-input-error class="mt-2" :messages="$errors->get('subject')" />
                </div>

                <div>
                    <x-input-label for="particulars" value="Particulars" />
                    <textarea id="particulars" name="particulars" rows="5"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('particulars', $quotation->particulars) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('particulars')" />
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <x-input-label for="unit" value="Unit" />
                        <x-text-input id="unit" name="unit" type="text" class="mt-1 block w-full"
                            :value="old('unit', $quotation->unit)" />
                        <x-input-error class="mt-2" :messages="$errors->get('unit')" />
                    </div>
                    <div>
                        <x-input-label for="total" value="Rate / Total" />
                        <x-text-input id="total" name="total" type="number" step="0.01" min="0" class="mt-1 block w-full"
                            :value="old('total', $quotation->total)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('total')" />
                    </div>
                    <div>
                        <x-input-label for="bill_date" value="Date" />
                        <x-text-input id="bill_date" name="bill_date" type="date" class="mt-1 block w-full"
                            :value="old('bill_date', $quotation->bill_date?->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('bill_date')" />
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>{{ $editing ? 'Save changes' : 'Add quotation' }}</x-primary-button>
                <a href="{{ route('quotations.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
