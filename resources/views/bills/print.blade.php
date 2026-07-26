<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $bill->invoice_no }}</title>
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
        <div class="border-b border-gray-800 p-4 text-center">
            <h1 class="text-lg font-bold uppercase">Tax Invoice</h1>
            <p class="text-xs">(Under section 31 &amp; rule 46 of CGST Act)</p>
            <h2 class="mt-1 text-base font-semibold">{{ config('company.name') }}</h2>
            <p class="text-xs">GSTIN: {{ config('company.gstin') }} | PAN: {{ config('company.pan') }}</p>
        </div>

        <div class="grid grid-cols-2 border-b border-gray-800 text-xs">
            <div class="border-r border-gray-800 p-3">
                {!! $bill->address !!}
                <p class="mt-2">{{ $bill->gst_no }}</p>
                <p>{{ $bill->bill_state }}</p>
            </div>
            <div class="p-3">
                <p><strong>Invoice No.:</strong> {{ str_pad((string) $bill->invoice_no, 3, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Invoice Date:</strong> {{ $bill->bill_date?->format('d/m/Y') }}</p>
                @if ($bill->ref_no)
                    <p><strong>Ref No.:</strong> {{ $bill->ref_no }}</p>
                @endif
                @if ($bill->ref_date)
                    <p><strong>Ref Date:</strong> {{ $bill->ref_date->format('d/m/Y') }}</p>
                @endif
                <p><strong>Department:</strong> {{ $bill->department?->department_name }}</p>
                @if ($bill->sir_name)
                    <p><strong>Attn.:</strong> {{ $bill->sir_name }}</p>
                @endif
            </div>
        </div>

        <table class="w-full border-b border-gray-800 text-xs">
            <thead>
                <tr class="border-b border-gray-800">
                    <th class="border-r border-gray-800 p-2 text-left">#</th>
                    <th class="border-r border-gray-800 p-2 text-left">Service No</th>
                    <th class="border-r border-gray-800 p-2 text-left">Description</th>
                    <th class="border-r border-gray-800 p-2 text-left">HSN</th>
                    <th class="border-r border-gray-800 p-2 text-right">Qty</th>
                    <th class="border-r border-gray-800 p-2 text-left">Unit</th>
                    <th class="border-r border-gray-800 p-2 text-right">Rate</th>
                    <th class="p-2 text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bill->items as $item)
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

                @if ($bill->remark)
                    <p class="mt-2 font-semibold">Remark:</p>
                    <p>{{ $bill->remark }}</p>
                @endif

                <p class="mt-3 font-semibold">Bank Details:</p>
                <p>Bank: {{ config('company.bank_name') }}</p>
                <p>A/C No.: {{ config('company.account_no') }}</p>
                <p>IFSC: {{ config('company.ifsc_code') }}</p>
                <p>Vendor Code: {{ config('company.vendor_code') }}</p>
                <p class="mt-2">MSME Reg.: {{ config('company.msme_certificate') }}</p>
            </div>
            <div class="p-3">
                <div class="flex justify-between border-b border-gray-200 py-1">
                    <span>Subtotal</span>
                    <span class="tabular-nums">{{ number_format($bill->total, 2) }}</span>
                </div>
                @if ($bill->gst_bill)
                    <div class="flex justify-between border-b border-gray-200 py-1">
                        <span>CGST @ {{ config('company.cgst_rate') }}%</span>
                        <span class="tabular-nums">{{ number_format($cgst, 2) }}</span>
                    </div>
                    <div class="flex justify-between border-b border-gray-200 py-1">
                        <span>SGST @ {{ config('company.sgst_rate') }}%</span>
                        <span class="tabular-nums">{{ number_format($sgst, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between py-2 text-sm font-bold">
                    <span>Grand Total</span>
                    <span class="tabular-nums">{{ number_format($grandTotal, 2) }}</span>
                </div>

                @if ($bill->paid)
                    <p class="mt-2 text-green-700">Paid: {{ number_format($bill->paid_amount, 2) }} on {{ $bill->paid_date?->format('d/m/Y') }}</p>
                @endif
            </div>
        </div>

        <div class="border-t border-gray-800 p-3 text-center text-xs text-gray-600">
            <p>Declaration: Turnover below ₹5 Crore. No reverse charge is applicable on this supply.</p>
            <p class="mt-4">for {{ config('company.name') }}</p>
        </div>
    </div>
</body>
</html>
