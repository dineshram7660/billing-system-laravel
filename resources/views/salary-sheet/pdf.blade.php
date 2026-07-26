<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Salary Sheet — {{ $startDate }} to {{ $endDate }}</title>
    @include('pdf._styles')
    <style>
        @page { size: letter landscape; margin: 20px; }
    </style>
</head>
<body>
    <div class="font-bold text-lg">{{ config('company.name') }} — Salary Sheet</div>
    <div class="muted mb text-sm">{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>

    <table class="border">
        <thead>
            <tr class="border-b font-bold">
                <td class="p-sm border-r">Employee</td>
                @foreach ($days as $day)
                    <td class="p-sm border-r text-center">{{ date('d', strtotime($day)) }}</td>
                @endforeach
                <td class="p-sm border-r text-right">Total Days</td>
                <td class="p-sm text-right">Total</td>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr class="row-border">
                    <td class="p-sm border-r">{{ $row['employee_name'] }}</td>
                    @foreach ($row['marks'] as $mark)
                        <td class="p-sm border-r text-center">{{ $mark }}</td>
                    @endforeach
                    <td class="p-sm border-r text-right">{{ $row['total_days'] }}</td>
                    <td class="p-sm text-right">{{ number_format($row['total'], 2) }}</td>
                </tr>
                <tr class="row-border">
                    <td class="p-sm border-r">Over Time</td>
                    @foreach ($row['over_time'] as $ot)
                        <td class="p-sm border-r text-center">{{ $ot }}</td>
                    @endforeach
                    <td class="p-sm border-r text-right">{{ $row['total_over_time'] }}</td>
                    <td class="p-sm"></td>
                </tr>
            @empty
                <tr>
                    <td class="p text-center" colspan="{{ count($days) + 3 }}">No active employees.</td>
                </tr>
            @endforelse
        </tbody>
        @if (count($rows) > 0)
            <tfoot>
                <tr class="border-t font-bold">
                    <td class="p-sm border-r" colspan="{{ count($days) + 2 }}">{{ date('F', strtotime($startDate)) }} Total</td>
                    <td class="p-sm text-right">{{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>
