<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>GST Report — {{ $startDate }} to {{ $endDate }}</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            .no-print { display: none; }
        }
        body { font-size: 12px; }
    </style>
</head>
<body class="bg-white p-6 text-gray-900">
    <div class="no-print mb-4 flex gap-3">
        <button onclick="window.print()" class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white">Print</button>
        <a href="{{ route('gst-report.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">Download PDF</a>
    </div>

    <h1 class="mb-1 text-base font-bold">{{ config('company.name') }} — GST Report</h1>
    <p class="mb-4 text-xs text-gray-600">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-800 text-xs">
            <thead>
                <tr class="border-b border-gray-800 bg-gray-100">
                    <th class="border-r border-gray-800 p-1 text-left">Sr.</th>
                    <th class="border-r border-gray-800 p-1 text-left">Bill No.</th>
                    <th class="border-r border-gray-800 p-1 text-left">Bill Date</th>
                    <th class="border-r border-gray-800 p-1 text-right">Bill Amount</th>
                    <th class="border-r border-gray-800 p-1 text-right">CGST {{ config('company.cgst_rate') }}%</th>
                    <th class="border-r border-gray-800 p-1 text-right">SGST {{ config('company.sgst_rate') }}%</th>
                    <th class="border-r border-gray-800 p-1 text-right">Total Amount</th>
                    <th class="border-r border-gray-800 p-1 text-right">(-) TDS 1%</th>
                    <th class="border-r border-gray-800 p-1 text-right">Total</th>
                    <th class="border-r border-gray-800 p-1 text-right">Amount/Bank Stmt</th>
                    <th class="border-r border-gray-800 p-1 text-left">Date/Bank Stmt</th>
                    <th class="border-r border-gray-800 p-1 text-left">Department</th>
                    <th class="p-1 text-left">Remark</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b border-gray-300">
                        <td class="border-r border-gray-300 p-1">{{ $loop->iteration }}</td>
                        <td class="border-r border-gray-300 p-1">{{ $row['bill']->invoice_no }}</td>
                        <td class="border-r border-gray-300 p-1">{{ $row['bill']->bill_date?->format('d-m-Y') }}</td>
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ number_format($row['total'], 2) }}</td>
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ number_format($row['cgst'], 2) }}</td>
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ number_format($row['sgst'], 2) }}</td>
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ number_format($row['total_amount'], 2) }}</td>
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ number_format($row['tds'], 2) }}</td>
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ number_format($row['gr_amount'], 2) }}</td>
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ number_format($row['bill']->paid_amount, 2) }}</td>
                        <td class="border-r border-gray-300 p-1">{{ $row['bill']->paid_date?->format('d-m-Y') }}</td>
                        <td class="border-r border-gray-300 p-1">{{ $row['bill']->department?->department_name }}</td>
                        <td class="p-1">{{ $row['bill']->remark }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="p-4 text-center text-gray-500">No bills in this date range.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($rows->isNotEmpty())
                <tfoot>
                    <tr class="border-t-2 border-gray-800 font-bold">
                        <td class="border-r border-gray-800 p-1" colspan="3"></td>
                        <td class="border-r border-gray-800 p-1 text-right tabular-nums">{{ number_format($totals['total'], 2) }}</td>
                        <td class="border-r border-gray-800 p-1 text-right tabular-nums">{{ number_format($totals['cgst'], 2) }}</td>
                        <td class="border-r border-gray-800 p-1 text-right tabular-nums">{{ number_format($totals['sgst'], 2) }}</td>
                        <td class="border-r border-gray-800 p-1 text-right tabular-nums">{{ number_format($totals['total_amount'], 2) }}</td>
                        <td class="border-r border-gray-800 p-1 text-right tabular-nums">{{ number_format($totals['tds'], 2) }}</td>
                        <td class="border-r border-gray-800 p-1 text-right tabular-nums">{{ number_format($totals['gr_amount'], 2) }}</td>
                        <td class="border-r border-gray-800 p-1 text-right tabular-nums">{{ number_format($totals['bank_amount'], 2) }}</td>
                        <td class="border-r border-gray-800 p-1" colspan="3"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</body>
</html>
