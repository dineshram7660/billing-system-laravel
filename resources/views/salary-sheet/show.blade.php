<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Salary Sheet — {{ $startDate }} to {{ $endDate }}</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            .no-print { display: none; }
        }
        body { font-size: 11px; }
    </style>
</head>
<body class="bg-white p-6 text-gray-900">
    <div class="no-print mb-4 flex gap-3">
        <button onclick="window.print()" class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white">Print</button>
        <a href="{{ route('salary-sheet.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">Download PDF</a>
        <a href="{{ route('salary-sheet.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">Download Excel</a>
    </div>

    <h1 class="mb-1 text-base font-bold">{{ config('company.name') }} — Salary Sheet</h1>
    <p class="mb-4 text-xs text-gray-600">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-800 text-xs">
            <thead>
                <tr class="border-b border-gray-800 bg-gray-100">
                    <th class="sticky left-0 border-r border-gray-800 bg-gray-100 p-1 text-left">Employee</th>
                    @foreach ($days as $day)
                        <th class="border-r border-gray-800 p-1 text-center">{{ \Carbon\Carbon::parse($day)->format('d') }}</th>
                    @endforeach
                    <th class="border-r border-gray-800 p-1 text-right">Total Days</th>
                    <th class="p-1 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b border-gray-300">
                        <td class="sticky left-0 border-r border-gray-300 bg-white p-1 font-medium">{{ $row['employee_name'] }}</td>
                        @foreach ($row['marks'] as $mark)
                            <td class="border-r border-gray-300 p-1 text-center {{ $mark === 'P' ? 'text-green-700' : 'text-gray-400' }}">{{ $mark }}</td>
                        @endforeach
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ $row['total_days'] }}</td>
                        <td class="p-1 text-right tabular-nums">{{ number_format($row['total'], 2) }}</td>
                    </tr>
                    <tr class="border-b border-gray-300 text-gray-500">
                        <td class="sticky left-0 border-r border-gray-300 bg-white p-1">Over Time</td>
                        @foreach ($row['over_time'] as $ot)
                            <td class="border-r border-gray-300 p-1 text-center tabular-nums">{{ $ot }}</td>
                        @endforeach
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ $row['total_over_time'] }}</td>
                        <td class="p-1"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($days) + 3 }}" class="p-4 text-center text-gray-500">No active employees.</td>
                    </tr>
                @endforelse
            </tbody>
            @if (count($rows) > 0)
                <tfoot>
                    <tr class="border-t-2 border-gray-800 font-bold">
                        <td class="border-r border-gray-800 p-1" colspan="{{ count($days) + 2 }}">{{ \Carbon\Carbon::parse($startDate)->format('F') }} Total</td>
                        <td class="p-1 text-right tabular-nums">{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</body>
</html>
