<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Rebuilds gst_bill.php / gst_bill_pdf() — an aggregate GST/TDS register
 * for a date range, not a per-invoice document (see BillController::print
 * for that). Legacy generated this as a downloadable dompdf PDF; this is
 * a browser-print HTML view for now, matching the bills/estimates/
 * quotations print pattern — real PDF export is Phase 5 work (see
 * README).
 */
class GstReportController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view-gst-report');

        return view('gst-report.index');
    }

    public function show(Request $request): View
    {
        Gate::authorize('view-gst-report');

        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $bills = Bill::with('department')
            ->whereBetween('bill_date', [$validated['start_date'], $validated['end_date']])
            ->orderBy('invoice_no')
            ->get();

        $rows = $bills->map(function (Bill $bill) {
            $total = (float) $bill->total;
            $cgst = $bill->gst_bill ? $total * config('company.cgst_rate') / 100 : 0;
            $sgst = $bill->gst_bill ? $total * config('company.sgst_rate') / 100 : 0;
            $totalAmount = $total + $cgst + $sgst;
            $tds = $total * 1 / 100;

            return [
                'bill' => $bill,
                'total' => $total,
                'cgst' => $cgst,
                'sgst' => $sgst,
                'total_amount' => $totalAmount,
                'tds' => $tds,
                'gr_amount' => $totalAmount - $tds,
            ];
        });

        $totals = [
            'total' => $rows->sum('total'),
            'cgst' => $rows->sum('cgst'),
            'sgst' => $rows->sum('sgst'),
            'total_amount' => $rows->sum('total_amount'),
            'tds' => $rows->sum('tds'),
            'gr_amount' => $rows->sum('gr_amount'),
            'bank_amount' => $bills->sum('paid_amount'),
        ];

        return view('gst-report.show', [
            'rows' => $rows,
            'totals' => $totals,
            'startDate' => $validated['start_date'],
            'endDate' => $validated['end_date'],
        ]);
    }
}
