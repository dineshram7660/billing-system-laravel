<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quotation — {{ $quotation->subject }}</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            .no-print { display: none; }
        }
        body { font-size: 13px; }
    </style>
</head>
<body class="bg-white p-6 text-gray-900" onload="window.print()">
    <div class="no-print mb-4">
        <button onclick="window.print()" class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white">Print</button>
    </div>

    <div class="mx-auto max-w-3xl border border-gray-800">
        <div class="grid grid-cols-2 border-b border-gray-800 p-4 text-xs">
            <div>
                To,<br>
                <div class="whitespace-pre-line">{{ $quotation->quotation_to }}</div>
            </div>
            <div class="text-right">
                Date: {{ $quotation->bill_date?->format('d/m/Y') }}
            </div>
        </div>

        <div class="border-b border-gray-800 p-3 text-center text-sm font-bold uppercase">
            Quotation
        </div>

        <div class="border-b border-gray-800 p-3 text-xs">
            Dear Sir,<br>
            As per your Inquiry we are submitting our competitive Rates for <strong>{{ $quotation->subject }}</strong>
        </div>

        <table class="w-full border-b border-gray-800 text-xs">
            <thead>
                <tr class="border-b border-gray-800">
                    <th class="border-r border-gray-800 p-2 text-left" style="width: 60%">Particulars</th>
                    <th class="border-r border-gray-800 p-2 text-left" style="width: 20%">Unit</th>
                    <th class="p-2 text-right" style="width: 20%">Rate</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-gray-800">
                    <td class="border-r border-gray-800 p-2 align-top whitespace-pre-line">{{ $quotation->particulars }}</td>
                    <td class="border-r border-gray-800 p-2 align-top">{{ $quotation->unit }}</td>
                    <td class="p-2 text-right align-top tabular-nums">{{ number_format(round($quotation->total), 0) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="border-b border-gray-800 p-3 text-xs">
            <strong>NOTE:</strong>
            (1) Tax Extra —
            (2) Only Labour Charge In Above Rate —
            (3) Scaffolding &amp; Hydra in your scope —
            (4) Providing Of All Safety Equipment in your scope Like Hand Gloves, Goggles etc.
        </div>

        <div class="p-4 text-xs">
            <p class="mb-8">For,</p>
            <p>{{ config('company.quotation_entity_name') }}</p>
        </div>
    </div>
</body>
</html>
