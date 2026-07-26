<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Measurement Sheet — {{ $estimate->subject }}</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            .no-print { display: none; }
        }
        body { font-size: 12px; }
    </style>
</head>
<body class="bg-white p-6 text-gray-900" onload="window.print()">
    <div class="no-print mb-4 flex gap-3">
        <button onclick="window.print()" class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white">Print</button>
        <a href="{{ route('estimates.measurement.pdf', $estimate) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">Download PDF</a>
    </div>

    <h1 class="mb-4 text-center text-lg font-bold uppercase">Measurement Sheet</h1>

    <table class="mb-4 w-full border border-gray-800 text-xs">
        <tr>
            <td class="p-2" colspan="2">Name Of Work: {{ $estimate->subject }}</td>
            <td class="p-2 text-right" colspan="2">Date: {{ $estimate->bill_date?->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="w-full border border-gray-800 text-xs">
        <thead>
            <tr class="border-b border-gray-800 bg-gray-100">
                <th class="border-r border-gray-800 p-1 text-left">Service No</th>
                <th class="border-r border-gray-800 p-1 text-left">Description</th>
                <th class="border-r border-gray-800 p-1 text-right">No.</th>
                <th class="border-r border-gray-800 p-1 text-right">Length</th>
                <th class="border-r border-gray-800 p-1 text-right">Breath</th>
                <th class="border-r border-gray-800 p-1 text-right">Unit</th>
                <th class="border-r border-gray-800 p-1 text-right">Quantity</th>
                <th class="p-1 text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($estimate->measurementItems as $group)
                @foreach ($group->lines as $line)
                    <tr class="border-b border-gray-300">
                        <td class="border-r border-gray-300 p-1">{{ $line->service_no }}</td>
                        <td class="border-r border-gray-300 p-1">{{ $line->description }}</td>
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ $line->no }}</td>
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ $line->length }}</td>
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ $line->breath }}</td>
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ $line->unit }}</td>
                        <td class="border-r border-gray-300 p-1 text-right tabular-nums">{{ $line->quantity }}</td>
                        @if ($loop->first)
                            <td class="p-1 text-right tabular-nums" rowspan="{{ $group->lines->count() }}">
                                {{ $group->total }} {{ $group->total_unit }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="8" class="p-4 text-center text-gray-500">No measurement lines recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
