<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Department;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\View\View;

/**
 * Rebuilds index.php's per-department overview widget. Legacy's own
 * dashboard never actually renders this — the department-select/date
 * form and #data_show container ajax_show_overview.php's JS listens for
 * never exist in index.php's markup, so the feature was dead on arrival
 * there. The underlying calculation is sound and useful, so this is a
 * working version of it rather than a port of the broken one — see the
 * README's Phase 7 notes.
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $departments = Department::orderBy('department_name')->get();

        $departmentId = request('department_id');
        $fromDate = request('from_date', now()->startOfMonth()->toDateString());
        $toDate = request('to_date', now()->toDateString());

        $overview = null;

        if ($departmentId) {
            $income = (float) Income::where('d_id', $departmentId)
                ->whereBetween('income_date', [$fromDate, $toDate])
                ->sum('amount');

            $expenses = (float) Expense::where('d_id', $departmentId)
                ->whereBetween('expenses_date', [$fromDate, $toDate])
                ->sum('amount');

            // Legacy folds bill totals into "expense" for this overview
            // (see ajax_show_overview.php) — bills billed to a
            // department are treated as an outgoing cost here, same as
            // this widget's own Expense records.
            $billed = (float) Bill::where('d_id', $departmentId)
                ->whereBetween('bill_date', [$fromDate, $toDate])
                ->sum('total');

            $overview = [
                'department' => $departments->firstWhere('id', (int) $departmentId),
                'income' => $income,
                'expense' => $expenses + $billed,
                'total' => $income - ($expenses + $billed),
            ];
        }

        return view('dashboard', [
            'departments' => $departments,
            'departmentId' => $departmentId,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'overview' => $overview,
        ]);
    }
}
