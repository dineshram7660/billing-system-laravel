<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>GST Report — {{ $startDate }} to {{ $endDate }}</title>
    @include('pdf._styles')
    <style>
        @page { size: letter landscape; margin: 20px; }
    </style>
</head>
<body>
    <div class="font-bold text-lg">{{ config('company.name') }} — GST Report</div>
    <div class="muted mb text-sm">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>

    <table class="border">
        <thead>
            <tr class="border-b font-bold">
                <td class="p-sm border-r">Sr.</td>
                <td class="p-sm border-r">Bill No.</td>
                <td class="p-sm border-r">Bill Date</td>
                <td class="p-sm border-r text-right">Bill Amount</td>
                <td class="p-sm border-r text-right">CGST {{ config('company.cgst_rate') }}%</td>
                <td class="p-sm border-r text-right">SGST {{ config('company.sgst_rate') }}%</td>
                <td class="p-sm border-r text-right">Total Amount</td>
                <td class="p-sm border-r text-right">(-) TDS 1%</td>
                <td class="p-sm border-r text-right">Total</td>
                <td class="p-sm border-r text-right">Amount/Bank Stmt</td>
                <td class="p-sm border-r">Date/Bank Stmt</td>
                <td class="p-sm border-r">Department</td>
                <td class="p-sm">Remark</td>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="row-border">
                    <td class="p-sm border-r">{{ $loop->iteration }}</td>
                    <td class="p-sm border-r">{{ $row['bill']->invoice_no }}</td>
                    <td class="p-sm border-r">{{ $row['bill']->bill_date?->format('d-m-Y') }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($row['total'], 2) }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($row['cgst'], 2) }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($row['sgst'], 2) }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($row['total_amount'], 2) }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($row['tds'], 2) }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($row['gr_amount'], 2) }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($row['bill']->paid_amount, 2) }}</td>
                    <td class="p-sm border-r">{{ $row['bill']->paid_date?->format('d-m-Y') }}</td>
                    <td class="p-sm border-r">{{ $row['bill']->department?->department_name }}</td>
                    <td class="p-sm">{{ $row['bill']->remark }}</td>
                </tr>
            @empty
                <tr>
                    <td class="p text-center" colspan="13">No bills in this date range.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($rows->isNotEmpty())
            <tfoot>
                <tr class="border-t font-bold">
                    <td class="p-sm border-r" colspan="3"></td>
                    <td class="p-sm border-r text-right">{{ number_format($totals['total'], 2) }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($totals['cgst'], 2) }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($totals['sgst'], 2) }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($totals['total_amount'], 2) }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($totals['tds'], 2) }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($totals['gr_amount'], 2) }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($totals['bank_amount'], 2) }}</td>
                    <td class="p-sm border-r" colspan="3"></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
