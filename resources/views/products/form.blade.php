@php $editing = $product->exists; @endphp
<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">{{ $editing ? 'Edit' : 'Add' }} Product</h2>
    </x-slot>

    <div class="max-w-xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ $editing ? route('products.update', $product) : route('products.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="space-y-5">
                <div>
                    <x-input-label for="product_name" value="Product name" />
                    <x-text-input id="product_name" name="product_name" type="text" class="mt-1 block w-full"
                        :value="old('product_name', $product->product_name)" required autofocus />
                    <x-input-error class="mt-2" :messages="$errors->get('product_name')" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="service_no" value="Service No" />
                        <x-text-input id="service_no" name="service_no" type="text" class="mt-1 block w-full"
                            :value="old('service_no', $product->service_no)" />
                        <x-input-error class="mt-2" :messages="$errors->get('service_no')" />
                    </div>
                    <div>
                        <x-input-label for="hsn_code" value="HSN Code" />
                        <x-text-input id="hsn_code" name="hsn_code" type="text" class="mt-1 block w-full"
                            :value="old('hsn_code', $product->hsn_code)" />
                        <x-input-error class="mt-2" :messages="$errors->get('hsn_code')" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="price" value="Price" />
                        <x-text-input id="price" name="price" type="number" step="0.01" min="0" class="mt-1 block w-full"
                            :value="old('price', $product->price)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('price')" />
                    </div>
                    <div>
                        <x-input-label for="per_unit" value="Per unit" />
                        <x-text-input id="per_unit" name="per_unit" type="text" class="mt-1 block w-full"
                            :value="old('per_unit', $product->per_unit)" />
                        <x-input-error class="mt-2" :messages="$errors->get('per_unit')" />
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>{{ $editing ? 'Save changes' : 'Add product' }}</x-primary-button>
                <a href="{{ route('products.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
