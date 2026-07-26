<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Inquiry::class);

        $inquiries = Inquiry::query()
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('fname', 'like', "%{$search}%")
                        ->orWhere('lname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('inquiries.index', compact('inquiries'));
    }

    public function destroy(Inquiry $inquiry): RedirectResponse
    {
        $this->authorize('delete', $inquiry);

        $inquiry->delete();

        return redirect()->route('inquiries.index')->with('status', 'Inquiry deleted successfully.');
    }
}
