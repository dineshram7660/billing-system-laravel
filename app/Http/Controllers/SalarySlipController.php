<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalarySlipRequest;
use App\Models\Employee;
use App\Models\EmployeeDetail;
use App\Models\SalaryDetail;
use App\Models\SalarySlip;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Rebuilds add_edit_salary_slip.php / salary_slip.php / salary_slip_print.php
 * / ajax_get_salary_data.php. A salary slip is a monthly payslip for one
 * employee, computed from that month's `attendance` rows and the latest
 * applicable `salary_details` rate — see calculateEarnings() and the
 * README for the exact formula (matches salary_slip_print.php exactly).
 */
class SalarySlipController extends Controller
{
    private const array MONTH_NUMBERS = [
        'January' => 1, 'February' => 2, 'March' => 3, 'April' => 4,
        'May' => 5, 'June' => 6, 'July' => 7, 'August' => 8,
        'September' => 9, 'October' => 10, 'November' => 11, 'December' => 12,
    ];

    public function index(): View
    {
        $this->authorize('viewAny', SalarySlip::class);

        $salarySlips = SalarySlip::with('employee')
            ->when(request('search'), function ($query, $search) {
                $query->whereHas('employee', fn ($q) => $q->where('employee_name', 'like', "%{$search}%"));
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('salary-slips.index', compact('salarySlips'));
    }

    public function create(): View
    {
        $this->authorize('create', SalarySlip::class);

        return view('salary-slips.form', [
            'salarySlip' => new SalarySlip(['salary_slip_year' => now()->year]),
            'employees' => Employee::orderBy('employee_name')->get(),
        ]);
    }

    public function store(SalarySlipRequest $request): RedirectResponse
    {
        $this->authorize('create', SalarySlip::class);

        $salarySlip = DB::transaction(function () use ($request) {
            $salarySlip = SalarySlip::create([
                ...$request->validated(),
                'salary_slip_date' => now()->toDateString(),
            ]);

            $this->syncAdvanceLedgerEntry($salarySlip);

            return $salarySlip;
        });

        return redirect()->route('salary-slips.index')->with('status', 'Salary slip added successfully.');
    }

    public function edit(SalarySlip $salarySlip): View
    {
        $this->authorize('update', $salarySlip);

        return view('salary-slips.form', [
            'salarySlip' => $salarySlip,
            'employees' => Employee::orderBy('employee_name')->get(),
        ]);
    }

    public function update(SalarySlipRequest $request, SalarySlip $salarySlip): RedirectResponse
    {
        $this->authorize('update', $salarySlip);

        DB::transaction(function () use ($request, $salarySlip) {
            $salarySlip->update($request->validated());

            $this->syncAdvanceLedgerEntry($salarySlip);
        });

        return redirect()->route('salary-slips.index')->with('status', 'Salary slip updated successfully.');
    }

    public function destroy(SalarySlip $salarySlip): RedirectResponse
    {
        $this->authorize('delete', $salarySlip);

        DB::transaction(function () use ($salarySlip) {
            // bill_id on employee_details doubles as "the salary_slip this
            // advance-payment ledger entry belongs to" here — see
            // syncAdvanceLedgerEntry() and the README note on this dual use.
            EmployeeDetail::where('bill_id', $salarySlip->id)->delete();
            $salarySlip->delete();
        });

        return redirect()->route('salary-slips.index')->with('status', 'Salary slip deleted successfully.');
    }

    public function print(SalarySlip $salarySlip): View
    {
        $this->authorize('print', $salarySlip);

        return view('salary-slips.print', $this->printData($salarySlip));
    }

    public function pdf(SalarySlip $salarySlip): Response
    {
        $this->authorize('print', $salarySlip);

        return Pdf::loadView('salary-slips.pdf', $this->printData($salarySlip))
            ->download("salary-slip-{$salarySlip->id}.pdf");
    }

    /**
     * @return array{salarySlip: SalarySlip, earnings: array<string, float>, advancePaymentThisMonth: float, totalAdvanceOutstanding: float}
     */
    private function printData(SalarySlip $salarySlip): array
    {
        $salarySlip->load('employee.designation');

        $monthStart = "{$salarySlip->salary_slip_year}-".self::MONTH_NUMBERS[$salarySlip->salary_slip_month].'-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $rate = $this->latestRate($salarySlip->employee_id, $monthEnd);

        $earnings = $this->calculateEarnings($salarySlip, $rate);

        $advancePaymentThisMonth = (float) EmployeeDetail::where('employee_id', $salarySlip->employee_id)
            ->where('type', 'Debit')
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->sum('amount');

        $totalAdvanceOutstanding = $this->ledgerBalance($salarySlip->employee_id, $monthEnd);

        return [
            'salarySlip' => $salarySlip,
            'earnings' => $earnings,
            'advancePaymentThisMonth' => $advancePaymentThisMonth,
            'totalAdvanceOutstanding' => $totalAdvanceOutstanding,
        ];
    }

    /**
     * Rebuilds ajax_get_salary_data.php: attendance totals, running
     * advance-payment ledger balance, and the applicable pay rate for an
     * employee/month/year — used to prefill the create/edit form.
     */
    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SalarySlip::class);

        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employee,id'],
            'month' => ['required', Rule::in(array_keys(self::MONTH_NUMBERS))],
            'year' => ['required', 'integer'],
        ]);

        $monthStart = "{$validated['year']}-".self::MONTH_NUMBERS[$validated['month']].'-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $attendance = DB::table('attendance')
            ->where('employee_id', $validated['employee_id'])
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->selectRaw('COALESCE(SUM(attendance), 0) as total_days, COALESCE(SUM(over_time), 0) as total_over_time')
            ->first();

        $rate = $this->latestRate((int) $validated['employee_id'], $monthEnd);

        return response()->json([
            'total_days' => (float) $attendance->total_days,
            'total_over_time' => (float) $attendance->total_over_time,
            'ledger_balance' => $this->ledgerBalance((int) $validated['employee_id'], $monthEnd),
            'par_day_amount' => $rate?->par_day_amount,
            'per_day_extra' => $rate?->per_day_extra,
        ]);
    }

    private function latestRate(int $employeeId, string $onOrBefore): ?SalaryDetail
    {
        return SalaryDetail::where('employee_id', $employeeId)
            ->where('date', '<=', $onOrBefore)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Debit = amount advanced to the employee (increases what they owe);
     * Credit = amount repaid/deducted (decreases it) — see
     * ajax_get_salary_data.php and salary_slip_print.php.
     */
    private function ledgerBalance(int $employeeId, string $onOrBefore): float
    {
        $debit = (float) EmployeeDetail::where('employee_id', $employeeId)
            ->where('type', 'Debit')->where('date', '<=', $onOrBefore)->sum('amount');
        $credit = (float) EmployeeDetail::where('employee_id', $employeeId)
            ->where('type', 'Credit')->where('date', '<=', $onOrBefore)->sum('amount');

        return $debit - $credit;
    }

    /**
     * @return array{basic_pay: float, extra_allowance: float, total_pay: float, total_deductions: float, net_pay: float}
     */
    private function calculateEarnings(SalarySlip $salarySlip, ?SalaryDetail $rate): array
    {
        $parDayAmount = (float) ($rate?->par_day_amount ?? 0);
        $perDayExtra = (float) ($rate?->per_day_extra ?? 0);

        $basicPay = round($salarySlip->day_work * $parDayAmount) + round($salarySlip->over_time * ($parDayAmount / 8));
        $extraAllowance = round($salarySlip->day_work * $perDayExtra) + round($salarySlip->over_time * ($perDayExtra / 8));
        $totalPay = $basicPay + $extraAllowance;
        $totalDeductions = (float) $salarySlip->pf_amount + (float) $salarySlip->professional_tax;

        return [
            'basic_pay' => $basicPay,
            'extra_allowance' => $extraAllowance,
            'total_pay' => $totalPay,
            'total_deductions' => $totalDeductions,
            'net_pay' => $totalPay - $totalDeductions,
        ];
    }

    /**
     * The slip's own "Advance Payment Deductions" field is mirrored into
     * employee_details as a Credit entry (bill_id = this slip's id) so it
     * counts against the running ledger balance — see
     * add_edit_salary_slip.php. Unlike legacy (which only UPDATEs an
     * existing row and silently no-ops if one was never created because
     * the original amount was zero), this uses updateOrCreate/delete so
     * editing a slip's deduction always keeps the ledger in sync.
     */
    private function syncAdvanceLedgerEntry(SalarySlip $salarySlip): void
    {
        if ((float) $salarySlip->advance_payment <= 0) {
            EmployeeDetail::where('bill_id', $salarySlip->id)->where('type', 'Credit')->delete();

            return;
        }

        $months = self::MONTH_NUMBERS;

        EmployeeDetail::updateOrCreate(
            ['bill_id' => $salarySlip->id, 'type' => 'Credit'],
            [
                'employee_id' => $salarySlip->employee_id,
                'amount' => $salarySlip->advance_payment,
                'date' => "{$salarySlip->salary_slip_year}-{$months[$salarySlip->salary_slip_month]}-01",
                'description' => 'Advance Payment',
            ]
        );
    }
}
