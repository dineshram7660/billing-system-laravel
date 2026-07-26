<?php

namespace App\Http\Controllers;

use App\Exports\SalarySheetExport;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SalaryDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Rebuilds salary_bill.php / gst_salary_sheet_pdf() — a P/A attendance +
 * pay register for a date range across all active employees (`status=1`
 * only, unlike Attendance's `employee=1 AND status=1` filter — legacy
 * genuinely uses a different eligibility rule here, confirmed by reading
 * both files side by side).
 */
class SalarySheetController extends Controller
{
    public function index(): View
    {
        Gate::authorize('view-salary-sheet');

        return view('salary-sheet.index');
    }

    public function show(Request $request): View
    {
        Gate::authorize('view-salary-sheet');

        return view('salary-sheet.show', $this->buildData($request));
    }

    public function pdf(Request $request): Response
    {
        Gate::authorize('view-salary-sheet');

        $data = $this->buildData($request);

        return Pdf::loadView('salary-sheet.pdf', $data)
            ->setPaper('letter', 'landscape')
            ->download("salary-sheet-{$data['startDate']}-to-{$data['endDate']}.pdf");
    }

    public function excel(Request $request): BinaryFileResponse
    {
        Gate::authorize('view-salary-sheet');

        $data = $this->buildData($request);

        return Excel::download(
            new SalarySheetExport($data['days'], $data['rows'], $data['grandTotal']),
            'Bhavani_Engineering_Salary_Sheet.xlsx',
        );
    }

    /**
     * @return array{days: array<int, string>, rows: array<int, array<string, mixed>>, grandTotal: float, startDate: string, endDate: string}
     */
    private function buildData(Request $request): array
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];

        $days = [];
        for ($cursor = $startDate; strtotime($cursor) <= strtotime($endDate); $cursor = date('Y-m-d', strtotime('+1 day', strtotime($cursor)))) {
            $days[] = $cursor;
        }

        $employees = Employee::where('status', 1)->orderBy('employee_name')->get();

        $attendanceByEmployee = Attendance::whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy('employee_id');

        $grandTotal = 0.0;
        $rows = [];

        foreach ($employees as $employee) {
            $records = ($attendanceByEmployee[$employee->id] ?? collect())->keyBy(fn ($r) => $r->date->toDateString());

            $marks = [];
            $overTime = [];
            $totalDays = 0;
            $totalOverTime = 0;

            foreach ($days as $day) {
                $record = $records->get($day);
                $marks[] = $record && $record->attendance > 0 ? 'P' : 'A';
                $overTime[] = (int) ($record?->over_time ?? 0);
                $totalDays += (int) ($record?->attendance ?? 0);
                $totalOverTime += (int) ($record?->over_time ?? 0);
            }

            $rate = SalaryDetail::where('employee_id', $employee->id)
                ->where('date', '<=', $endDate)
                ->orderByDesc('id')
                ->first();
            $parDayAmount = (float) ($rate?->par_day_amount ?? 0);

            $total = ($totalDays * $parDayAmount) + ($totalOverTime * ($parDayAmount / 8));
            $grandTotal += $total;

            $rows[] = [
                'employee_name' => $employee->employee_name,
                'marks' => $marks,
                'over_time' => $overTime,
                'total_days' => $totalDays,
                'total_over_time' => $totalOverTime,
                'total' => $total,
            ];
        }

        return [
            'days' => $days,
            'rows' => $rows,
            'grandTotal' => $grandTotal,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }
}
