<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Rebuilds add_edit_photo.php — multiple photo attachments per bill,
 * stored comma-separated in bill.photo (kept as-is; not worth a new
 * table for what's always been a flat list). Uses Laravel's storage
 * disk instead of a hand-rolled random-filename generator
 * (upload_image() in the legacy app), so `photo` here holds storage
 * paths (`bill-photos/xyz.jpg`), not the bare filenames legacy used.
 * Pre-existing legacy photo values reference files that only exist on
 * the legacy server's disk, not this app's storage — this app only ever
 * had a copy of the database, never the upload directory, so those
 * thumbnails will 404 (broken image icon) rather than resolve; nothing
 * to fix here since the underlying files were never in scope to migrate.
 */
class BillPhotoController extends Controller
{
    public function edit(Bill $bill): View
    {
        $this->authorize('update', $bill);

        return view('bills.photos', ['bill' => $bill, 'photos' => $this->photos($bill)]);
    }

    public function update(Request $request, Bill $bill): RedirectResponse
    {
        $this->authorize('update', $bill);

        $validated = $request->validate([
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,gif', 'max:5120'],
            'keep' => ['nullable', 'array'],
            'keep.*' => ['string'],
        ]);

        $kept = $validated['keep'] ?? [];

        foreach (array_diff($this->photos($bill), $kept) as $removed) {
            Storage::disk('public')->delete($removed);
        }

        $uploaded = collect($validated['photos'] ?? [])
            ->map(fn ($file) => $file->store('bill-photos', 'public'))
            ->all();

        $bill->update(['photo' => implode(',', [...$kept, ...$uploaded])]);

        return redirect()->route('bills.photos.edit', $bill)->with('status', 'Photos updated successfully.');
    }

    /**
     * @return array<int, string>
     */
    private function photos(Bill $bill): array
    {
        return array_filter(explode(',', (string) $bill->photo));
    }
}
