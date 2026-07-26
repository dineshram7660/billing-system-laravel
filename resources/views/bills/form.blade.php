@php $editing = $bill->exists; @endphp
<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ $editing ? 'Edit' : 'Add' }} Bill</h2>
    </x-slot>

    <form method="POST" action="{{ $editing ? route('bills.update', $bill) : route('bills.store') }}"
        x-data="lineItemForm(@js($items), '{{ route('products.search') }}')">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <x-input-label for="invoice_no" value="Invoice No" />
                        <x-text-input id="invoice_no" name="invoice_no" type="number" class="mt-1 block w-full"
                            :value="old('invoice_no', $bill->invoice_no)" />
                        <x-input-error class="mt-2" :messages="$errors->get('invoice_no')" />
                    </div>
                    <div>
                        <x-input-label for="bill_date" value="Bill Date" />
                        <x-text-input id="bill_date" name="bill_date" type="date" class="mt-1 block w-full"
                            :value="old('bill_date', $bill->bill_date?->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('bill_date')" />
                    </div>
                    <div>
                        <x-input-label for="d_id" value="Department" />
                        <select id="d_id" name="d_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            <option value="">Select Department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected(old('d_id', $bill->d_id) == $department->id)>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('d_id')" />
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <x-input-label for="subject" value="Subject" />
                        <x-text-input id="subject" name="subject" type="text" class="mt-1 block w-full"
                            :value="old('subject', $bill->subject)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('subject')" />
                    </div>

                    <div>
                        <x-input-label for="sir_name" value="Sir Name" />
                        <x-text-input id="sir_name" name="sir_name" type="text" class="mt-1 block w-full"
                            :value="old('sir_name', $bill->sir_name)" />
                        <x-input-error class="mt-2" :messages="$errors->get('sir_name')" />
                    </div>
                    <div>
                        <x-input-label for="ref_no" value="Ref No" />
                        <x-text-input id="ref_no" name="ref_no" type="text" class="mt-1 block w-full"
                            :value="old('ref_no', $bill->ref_no)" />
                        <x-input-error class="mt-2" :messages="$errors->get('ref_no')" />
                    </div>
                    <div>
                        <x-input-label for="ref_date" value="Ref Date" />
                        <x-text-input id="ref_date" name="ref_date" type="date" class="mt-1 block w-full"
                            :value="old('ref_date', $bill->ref_date?->toDateString())" />
                        <x-input-error class="mt-2" :messages="$errors->get('ref_date')" />
                    </div>

                    <div>
                        <x-input-label for="gst_no" value="Customer GST No" />
                        <x-text-input id="gst_no" name="gst_no" type="text" class="mt-1 block w-full"
                            :value="old('gst_no', $bill->gst_no ?? 'GSTIN No. : ')" />
                        <x-input-error class="mt-2" :messages="$errors->get('gst_no')" />
                    </div>
                    <div>
                        <x-input-label for="bill_state" value="Bill State" />
                        <x-text-input id="bill_state" name="bill_state" type="text" class="mt-1 block w-full"
                            :value="old('bill_state', $bill->bill_state ?? 'State : ')" />
                        <x-input-error class="mt-2" :messages="$errors->get('bill_state')" />
                    </div>
                    <div>
                        <x-input-label for="gst_bill" value="GST Bill" />
                        <select id="gst_bill" name="gst_bill"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            <option value="1" @selected((string) old('gst_bill', $bill->gst_bill ?? 1) === '1')>Yes</option>
                            <option value="0" @selected((string) old('gst_bill', $bill->gst_bill ?? 1) === '0')>No</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('gst_bill')" />
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <x-input-label for="address" value="Address" />
                        <textarea id="address" name="address" rows="4"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('address', $bill->address) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('address')" />
                    </div>

                    <div class="sm:col-span-2 lg:col-span-3">
                        <x-input-label for="remark" value="Remark" />
                        <textarea id="remark" name="remark" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">{{ old('remark', $bill->remark) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('remark')" />
                    </div>
                </div>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-gray-500">Line Items</h3>

                <div class="relative mb-4 max-w-md">
                    <x-input-label for="product-search" value="Search product to add" />
                    <input id="product-search" type="text" autocomplete="off" x-model="search" @input.debounce.300ms="searchProducts()"
                        @focusout="setTimeout(() => showResults = false, 150)"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500"
                        placeholder="Product name, service no, or HSN code…">
                    <ul x-show="showResults && results.length > 0" x-cloak
                        class="absolute z-10 mt-1 max-h-56 w-full overflow-auto rounded-md border border-gray-200 bg-white shadow-lg">
                        <template x-for="product in results" :key="product.id">
                            <li @click="pickProduct(product)"
                                class="cursor-pointer px-3 py-2 text-sm hover:bg-gray-100">
                                <span x-text="product.product_name"></span>
                                <span class="text-gray-400" x-text="product.service_no ? ' — ' + product.service_no : ''"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-500">Service No</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500">Product</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500">HSN</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-500">Unit</th>
                                <th class="px-3 py-2 text-right font-medium text-gray-500">Price</th>
                                <th class="px-3 py-2 text-right font-medium text-gray-500">Qty</th>
                                <th class="px-3 py-2 text-right font-medium text-gray-500">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td class="px-2 py-1">
                                        <input type="text" x-model="item.service_no" :name="`items[${index}][service_no]`"
                                            class="block w-24 rounded-md border-gray-300 text-sm">
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="hidden" :name="`items[${index}][product_id]`" x-model="item.product_id">
                                        <input type="text" x-model="item.product_name" :name="`items[${index}][product_name]`" required
                                            class="block w-48 rounded-md border-gray-300 text-sm">
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="text" x-model="item.hsn_code" :name="`items[${index}][hsn_code]`"
                                            class="block w-20 rounded-md border-gray-300 text-sm">
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="text" x-model="item.per_unit" :name="`items[${index}][per_unit]`"
                                            class="block w-16 rounded-md border-gray-300 text-sm">
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="number" step="0.01" min="0" x-model="item.price" @input="recalcTotal(index)"
                                            :name="`items[${index}][price]`" class="block w-24 rounded-md border-gray-300 text-right text-sm">
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="number" step="0.001" min="0" x-model="item.qty" @input="recalcTotal(index)"
                                            :name="`items[${index}][qty]`" class="block w-20 rounded-md border-gray-300 text-right text-sm">
                                    </td>
                                    <td class="px-2 py-1">
                                        <input type="number" step="0.01" min="0" x-model="item.total"
                                            :name="`items[${index}][total]`" class="block w-24 rounded-md border-gray-300 text-right text-sm">
                                    </td>
                                    <td class="px-2 py-1 text-right">
                                        <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800">Remove</button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="items.length === 0" x-cloak>
                                <td colspan="8" class="px-3 py-4 text-center text-gray-500">No line items yet — search above or add a blank row.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <button type="button" @click="addBlank()" class="text-sm text-gray-600 hover:text-gray-900">+ Add blank row</button>
                    <div class="text-sm font-semibold text-gray-900">Subtotal: <span x-text="subtotal"></span></div>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('items')" />
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-widest text-gray-500">Payment</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <x-input-label for="paid" value="Paid" />
                        <select id="paid" name="paid"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500">
                            <option value="0" @selected((string) old('paid', $bill->paid ?? 0) === '0')>No</option>
                            <option value="1" @selected((string) old('paid', $bill->paid ?? 0) === '1')>Yes</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('paid')" />
                    </div>
                    <div>
                        <x-input-label for="paid_amount" value="Paid Amount" />
                        <x-text-input id="paid_amount" name="paid_amount" type="number" step="0.01" min="0" class="mt-1 block w-full"
                            :value="old('paid_amount', $bill->paid_amount)" />
                        <x-input-error class="mt-2" :messages="$errors->get('paid_amount')" />
                    </div>
                    <div>
                        <x-input-label for="paid_date" value="Paid Date" />
                        <x-text-input id="paid_date" name="paid_date" type="date" class="mt-1 block w-full"
                            :value="old('paid_date', $bill->paid_date?->toDateString())" />
                        <x-input-error class="mt-2" :messages="$errors->get('paid_date')" />
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <x-primary-button>{{ $editing ? 'Save changes' : 'Add bill' }}</x-primary-button>
            <a href="{{ route('bills.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
        </div>
    </form>
</x-admin-layout>
