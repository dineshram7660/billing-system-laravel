<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Estimate — {{ $estimate->subject }}</title>
    @vite('resources/css/app.css')
    <style>
        @media print {
            .no-print { display: none; }
        }
        body { font-size: 13px; }
    </style>
</head>
<body class="bg-white p-6 text-gray-900" onload="window.print()">
    <div class="no-print mb-4 flex gap-3">
        <button onclick="window.print()" class="rounded-md bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white">Print</button>
        <a href="{{ route('estimates.pdf', $estimate) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">Download PDF</a>
        <a href="{{ route('estimates.excel', $estimate) }}" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 hover:bg-gray-50">Download Excel</a>
    </div>

    <div class="mx-auto max-w-3xl border border-gray-800">
        <div class="border-b border-gray-800 p-4 text-center">
            <h1 class="text-lg font-bold uppercase">Estimate</h1>
            <h2 class="mt-1 text-base font-semibold">{{ config('company.name') }}</h2>
            <p class="text-xs">GSTIN: {{ config('company.gstin') }} | Vendor Code: {{ config('company.vendor_code') }}</p>
        </div>

        <div class="border-b border-gray-800 p-3 text-center text-sm font-semibold">
            {{ $estimate->subject }}
        </div>

        <div class="border-b border-gray-800 p-3 text-right text-xs">
            Date: {{ $estimate->bill_date?->format('d/m/Y') }}
        </div>

        <table class="w-full border-b border-gray-800 text-xs">
            <thead>
                <tr class="border-b border-gray-800">
                    <th class="border-r border-gray-800 p-2 text-left">#</th>
                    <th class="border-r border-gray-800 p-2 text-left">Service No</th>
                    <th class="border-r border-gray-800 p-2 text-left">Name of Work</th>
                    <th class="border-r border-gray-800 p-2 text-left">HSN</th>
                    <th class="border-r border-gray-800 p-2 text-right">Qty</th>
                    <th class="border-r border-gray-800 p-2 text-left">Unit</th>
                    <th class="border-r border-gray-800 p-2 text-right">Rate</th>
                    <th class="p-2 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($estimate->items as $item)
                    <tr class="border-b border-gray-200">
                        <td class="border-r border-gray-200 p-2">{{ $loop->iteration }}</td>
                        <td class="border-r border-gray-200 p-2">{{ $item->service_no }}</td>
                        <td class="border-r border-gray-200 p-2">{{ $item->product_name }}</td>
                        <td class="border-r border-gray-200 p-2">{{ $item->hsn_code }}</td>
                        <td class="border-r border-gray-200 p-2 text-right tabular-nums">{{ number_format($item->qty, 2) }}</td>
                        <td class="border-r border-gray-200 p-2">{{ $item->per_unit }}</td>
                        <td class="border-r border-gray-200 p-2 text-right tabular-nums">{{ number_format($item->price, 2) }}</td>
                        <td class="p-2 text-right tabular-nums">{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="grid grid-cols-2 text-xs">
            <div class="border-r border-gray-800 p-3">
                <p class="font-semibold">Amount in words:</p>
                <p>{{ \App\Support\IndianCurrency::words((int) round($grandTotal)) }}</p>
            </div>
            <div class="p-3">
                <div class="flex justify-between border-b border-gray-200 py-1">
                    <span>Subtotal</span>
                    <span class="tabular-nums">{{ number_format($estimate->total, 2) }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-200 py-1">
                    <span>CGST @ {{ config('company.cgst_rate') }}%</span>
                    <span class="tabular-nums">{{ number_format($cgst, 2) }}</span>
                </div>
                <div class="flex justify-between border-b border-gray-200 py-1">
                    <span>SGST @ {{ config('company.sgst_rate') }}%</span>
                    <span class="tabular-nums">{{ number_format($sgst, 2) }}</span>
                </div>
                <div class="flex justify-between py-2 text-sm font-bold">
                    <span>Grand Total</span>
                    <span class="tabular-nums">{{ number_format($grandTotal, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="border-t border-gray-800 p-3 text-center text-xs text-gray-600">
            <p>for {{ config('company.name') }}</p>
        </div>
    </div>
</body>
</html>
