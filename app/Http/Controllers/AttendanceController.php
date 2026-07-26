<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Rebuilds attendance.php/edit_attendance.php/add_attendance.php/
 * add_admin_attendance.php/add_attendance_month.php. add_attendance.php
 * (today only) and add_admin_attendance.php (any date, otherwise
 * identical — both gated by the same "Add Attendance" permission) are
 * merged into one create()/store() pair with a date field that defaults
 * to today but is editable, rather than porting two near-duplicate pages.
 */
class AttendanceController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Attendance::class);

        $attendances = Attendance::with('employee')
            ->whereHas('employee')
            ->when(request('search'), function ($query, $search) {
                $query->whereHas('employee', fn ($q) => $q->where('employee_name', 'like', "%{$search}%"));
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('attendance.index', compact('attendances'));
    }

    public function edit(Attendance $attendance): View
    {
        $this->authorize('update', Attendance::class);

        return view('attendance.edit', compact('attendance'));
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->authorize('update', Attendance::class);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'attendance' => ['required', 'boolean'],
            'over_time' => ['required', 'integer', 'min:0'],
        ]);

        $attendance->update($validated);

        return redirect()->route('attendance.index')->with('status', 'Attendance updated successfully.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $this->authorize('delete', Attendance::class);

        $attendance->delete();

        return redirect()->route('attendance.index')->with('status', 'Attendance deleted successfully.');
    }

    public function create(): View
    {
        $this->authorize('create', Attendance::class);

        $date = request('date', now()->toDateString());
        $employees = $this->eligibleEmployees();
        $existing = Attendance::whereIn('employee_id', $employees->pluck('id'))
            ->where('date', $date)
            ->get()
            ->keyBy('employee_id');

        return view('attendance.create', compact('employees', 'existing', 'date'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Attendance::class);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'attendance' => ['array'],
            'attendance.*' => ['boolean'],
            'over_time' => ['array'],
            'over_time.*' => ['integer', 'min:0'],
        ]);

        $employees = $this->eligibleEmployees();

        DB::transaction(function () use ($validated, $employees) {
            foreach ($employees as $employee) {
                Attendance::updateOrCreate(
                    ['employee_id' => $employee->id, 'date' => $validated['date']],
                    [
                        'attendance' => (int) ($validated['attendance'][$employee->id] ?? 0),
                        'over_time' => (int) ($validated['over_time'][$employee->id] ?? 0),
                    ]
                );
            }
        });

        return redirect()->route('attendance.create', ['date' => $validated['date']])->with('status', 'Attendance saved successfully.');
    }

    public function month(): View
    {
        $this->authorize('create', Attendance::class);

        $year = (int) request('year', now()->year);
        $month = (int) request('month', 0);

        $employees = $this->eligibleEmployees();
        $days = [];
        $grid = [];

        if ($month > 0) {
            $start = Carbon::create($year, $month, 1);
            $end = $start->copy()->endOfMonth();
            $days = collect(Carbon::parse($start)->daysUntil($end->copy()->addDay()))->map->toDateString()->all();

            $records = Attendance::whereIn('employee_id', $employees->pluck('id'))
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->get();

            foreach ($employees as $employee) {
                foreach ($days as $day) {
                    $grid[$employee->id][$day] = ['attendance' => 0, 'over_time' => 0];
                }
            }

            foreach ($records as $record) {
                $grid[$record->employee_id][$record->date->toDateString()] = [
                    'attendance' => $record->attendance,
                    'over_time' => $record->over_time,
                ];
            }
        }

        return view('attendance.month', [
            'employees' => $employees,
            'days' => $days,
            'grid' => $grid,
            'year' => $year,
            'month' => $month,
            'years' => range(2017, now()->year + 1),
        ]);
    }

    public function storeMonth(Request $request): RedirectResponse
    {
        $this->authorize('create', Attendance::class);

        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'attendance' => ['array'],
            'over_time' => ['array'],
        ]);

        $employees = $this->eligibleEmployees();
        $start = Carbon::create($validated['year'], $validated['month'], 1);
        $end = $start->copy()->endOfMonth();
        $days = collect(Carbon::parse($start)->daysUntil($end->copy()->addDay()))->map->toDateString()->all();

        DB::transaction(function () use ($validated, $employees, $days) {
            foreach ($employees as $employee) {
                foreach ($days as $day) {
                    Attendance::updateOrCreate(
                        ['employee_id' => $employee->id, 'date' => $day],
                        [
                            'attendance' => (int) ($validated['attendance'][$employee->id][$day] ?? 0),
                            'over_time' => (int) ($validated['over_time'][$employee->id][$day] ?? 0),
                        ]
                    );
                }
            }
        });

        return redirect()->route('attendance.month', ['year' => $validated['year'], 'month' => $validated['month']])
            ->with('status', 'Attendance saved successfully.');
    }

    private function eligibleEmployees()
    {
        return Employee::eligibleForAttendance()->orderBy('employee_name')->get();
    }
}
