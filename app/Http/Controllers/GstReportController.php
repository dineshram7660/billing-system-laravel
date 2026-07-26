<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Rebuilds gst_bill.php / gst_bill_pdf() — an aggregate GST/TDS register
 * for a date range, not a per-invoice document (see BillController::print
 * for that).
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

        return view('gst-report.show', $this->reportData($request));
    }

    public function pdf(Request $request): Response
    {
        Gate::authorize('view-gst-report');

        $data = $this->reportData($request);

        return Pdf::loadView('gst-report.pdf', $data)
            ->setPaper('letter', 'landscape')
            ->download("gst-report-{$data['startDate']}-to-{$data['endDate']}.pdf");
    }

    /**
     * @return array{rows: \Illuminate\Support\Collection, totals: array<string, float>, startDate: string, endDate: string}
     */
    private function reportData(Request $request): array
    {
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

        return [
            'rows' => $rows,
            'totals' => $totals,
            'startDate' => $validated['start_date'],
            'endDate' => $validated['end_date'],
        ];
    }
}
