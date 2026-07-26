<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-lg font-semibold text-gray-800">Photos — Bill #{{ $bill->invoice_no }}</h2>
    </x-slot>

    <div class="max-w-2xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        @if (session('status'))
            <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('bills.photos.update', $bill) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if (count($photos) > 0)
                <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                    @foreach ($photos as $photo)
                        <div class="space-y-1">
                            <a href="{{ Storage::disk('public')->url($photo) }}" target="_blank">
                                <img src="{{ Storage::disk('public')->url($photo) }}" class="h-24 w-24 rounded-md border border-gray-200 object-cover">
                            </a>
                            <label class="flex items-center gap-1 text-xs text-gray-600">
                                <input type="checkbox" name="keep[]" value="{{ $photo }}" checked
                                    class="rounded border-gray-300 text-gray-800 shadow-sm focus:ring-gray-500">
                                Keep
                            </label>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mb-6 text-sm text-gray-500">No photos uploaded yet.</p>
            @endif

            <div>
                <x-input-label for="photos" value="Add Photos" />
                <input id="photos" name="photos[]" type="file" multiple accept="image/png,image/jpeg,image/gif"
                    class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-gray-800 file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:text-white hover:file:bg-gray-700">
                <x-input-error class="mt-2" :messages="$errors->get('photos.*')" />
            </div>

            <div class="mt-6 flex items-center gap-3">
                <x-primary-button>Save</x-primary-button>
                <a href="{{ route('bills.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            </div>
        </form>
    </div>
</x-admin-layout>
