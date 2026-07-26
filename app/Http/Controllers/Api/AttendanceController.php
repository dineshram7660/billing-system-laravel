<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Rebuilds `get_attendance`/`save_attendance` from the legacy mobile-
 * attendance API — same eligibility rule (Employee::eligibleForAttendance,
 * shared with the admin panel's AttendanceController) and upsert
 * semantics, behind Sanctum auth instead of a trusted client-supplied
 * user_id. See AuthController's docblock for why this isn't a byte-
 * compatible port.
 */
class AttendanceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate(['date' => ['required', 'date']]);

        $employees = Employee::eligibleForAttendance()->orderBy('employee_name')->get();

        $records = Attendance::whereIn('employee_id', $employees->pluck('id'))
            ->where('date', $validated['date'])
            ->get()
            ->keyBy('employee_id');

        $attendance = $employees->map(function (Employee $employee) use ($records) {
            $record = $records->get($employee->id);

            return [
                'employee_id' => $employee->id,
                'employee_name' => $employee->employee_name,
                'attendance' => $record?->attendance,
                'over_time' => $record?->over_time,
            ];
        });

        return response()->json([
            'status' => 'success',
            'message' => '',
            'data' => ['attendance' => $attendance],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employee,id'],
            'date' => ['required', 'date'],
            'attendance' => ['required', 'boolean'],
            'over_time' => ['required', 'integer', 'min:0'],
        ]);

        Attendance::updateOrCreate(
            ['employee_id' => $validated['employee_id'], 'date' => $validated['date']],
            ['attendance' => $validated['attendance'], 'over_time' => $validated['over_time']],
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance saved successfully.',
            'data' => '',
        ]);
    }
}
