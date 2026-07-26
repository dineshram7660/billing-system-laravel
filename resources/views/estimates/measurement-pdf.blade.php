<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Measurement Sheet — {{ $estimate->subject }}</title>
    @include('pdf._styles')
</head>
<body>
    <div class="text-center font-bold text-lg mb">MEASUREMENT SHEET</div>

    <table class="border mb">
        <tr>
            <td class="p-sm" colspan="2">Name Of Work: {{ $estimate->subject }}</td>
            <td class="p-sm text-right" colspan="2">Date: {{ $estimate->bill_date?->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="border">
        <thead>
            <tr class="border-b font-bold">
                <td class="p-sm border-r">Service No</td>
                <td class="p-sm border-r">Description</td>
                <td class="p-sm border-r text-right">No.</td>
                <td class="p-sm border-r text-right">Length</td>
                <td class="p-sm border-r text-right">Breath</td>
                <td class="p-sm border-r text-right">Unit</td>
                <td class="p-sm border-r text-right">Quantity</td>
                <td class="p-sm text-right">Total</td>
            </tr>
        </thead>
        <tbody>
            @forelse ($estimate->measurementItems as $group)
                @foreach ($group->lines as $line)
                    <tr class="row-border">
                        <td class="p-sm border-r">{{ $line->service_no }}</td>
                        <td class="p-sm border-r">{{ $line->description }}</td>
                        <td class="p-sm border-r text-right">{{ $line->no }}</td>
                        <td class="p-sm border-r text-right">{{ $line->length }}</td>
                        <td class="p-sm border-r text-right">{{ $line->breath }}</td>
                        <td class="p-sm border-r text-right">{{ $line->unit }}</td>
                        <td class="p-sm border-r text-right">{{ $line->quantity }}</td>
                        @if ($loop->first)
                            <td class="p-sm text-right" rowspan="{{ $group->lines->count() }}">
                                {{ $group->total }} {{ $group->total_unit }}
                            </td>
                        @endif
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td class="p text-center" colspan="8">No measurement lines recorded.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
