@php $editing = $estimate->exists; @endphp
<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ $editing ? 'Edit' : 'Add' }} Estimate</h2>
    </x-slot>

    <form method="POST" action="{{ $editing ? route('estimates.update', $estimate) : route('estimates.store') }}"
        x-data="lineItemForm(@js($items), '{{ route('products.search') }}')">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <x-input-label for="bill_date" value="Date" />
                        <x-text-input id="bill_date" name="bill_date" type="date" class="mt-1 block w-full"
                            :value="old('bill_date', $estimate->bill_date?->toDateString())" required />
                        <x-input-error class="mt-2" :messages="$errors->get('bill_date')" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-input-label for="subject" value="Subject" />
                        <x-text-input id="subject" name="subject" type="text" class="mt-1 block w-full"
                            :value="old('subject', $estimate->subject)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('subject')" />
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
        </div>

        <div class="mt-6 flex items-center gap-3">
            <x-primary-button>{{ $editing ? 'Save changes' : 'Add estimate' }}</x-primary-button>
            <a href="{{ route('estimates.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
        </div>
    </form>
</x-admin-layout>
