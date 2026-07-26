<?php

namespace App\Exports;

use App\Models\Estimate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Rebuilds create_estimate_excel.php against the normalized
 * estimate_items table instead of re-parsing the delimited product blob.
 * Legacy truncated the work-name column to 35 characters (presumably a
 * fixed-width-column workaround) — not reproduced here, real spreadsheet
 * columns don't need it.
 */
class EstimateItemsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * Not a static local: a static var would persist across every export
     * this worker process ever runs (queue workers/Octane keep the class
     * loaded), corrupting the row count for later, unrelated exports.
     */
    private int $srNo = 0;

    public function __construct(private readonly Estimate $estimate) {}

    public function collection()
    {
        return $this->estimate->items()->get();
    }

    public function headings(): array
    {
        return ['Sr. No', 'Service No.', 'Name Of Work', 'HSN Code', 'Quantity', 'Rate', 'Per Unit', 'Amount'];
    }

    public function map($item): array
    {
        $this->srNo++;

        return [
            $this->srNo,
            $item->service_no,
            $item->product_name,
            $item->hsn_code,
            (float) $item->qty,
            (float) $item->price,
            $item->per_unit,
            (float) $item->total,
        ];
    }
}
