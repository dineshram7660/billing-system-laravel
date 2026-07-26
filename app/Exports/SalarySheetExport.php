<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Rebuilds salary_bill.php's Excel branch. Takes the same array shape
 * SalarySheetController::buildData() produces — see that method for the
 * exact P/A + pay calculation this reproduces.
 */
class SalarySheetExport implements FromArray, WithHeadings
{
    /**
     * @param  array<int, string>  $days
     * @param  array<int, array{employee_name: string, marks: array<int, string>, over_time: array<int, int>, total_days: int, total_over_time: int, total: float}>  $rows
     */
    public function __construct(
        private readonly array $days,
        private readonly array $rows,
        private readonly float $grandTotal,
    ) {}

    public function headings(): array
    {
        return [
            'Employee Name',
            ...array_map(fn ($day) => date('d', strtotime($day)), $this->days),
            'Total Days',
            'Total',
        ];
    }

    public function array(): array
    {
        $lines = [];

        foreach ($this->rows as $row) {
            $lines[] = [$row['employee_name'], ...$row['marks'], $row['total_days'], $row['total']];
            $lines[] = ['Over Time', ...$row['over_time'], $row['total_over_time'], ''];
        }

        $monthLabel = $this->days === [] ? '' : date('F', strtotime($this->days[0])).' Total';
        $lines[] = [$monthLabel, ...array_fill(0, count($this->days), ''), '', $this->grandTotal];

        return $lines;
    }
}
