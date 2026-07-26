<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Estimate — {{ $estimate->subject }}</title>
    @include('pdf._styles')
</head>
<body>
    <table class="border mb">
        <tr>
            <td class="p text-center">
                <div class="text-lg font-bold uppercase">Estimate</div>
                <div class="font-bold">{{ config('company.name') }}</div>
                <div class="text-sm">GSTIN: {{ config('company.gstin') }} | Vendor Code: {{ config('company.vendor_code') }}</div>
            </td>
        </tr>
        <tr>
            <td class="p border-t text-center font-bold">{{ $estimate->subject }}</td>
        </tr>
        <tr>
            <td class="p border-t text-right text-sm">Date: {{ $estimate->bill_date?->format('d/m/Y') }}</td>
        </tr>
    </table>

    <table class="border mb">
        <thead>
            <tr class="border-b font-bold">
                <td class="p-sm border-r">#</td>
                <td class="p-sm border-r">Service No</td>
                <td class="p-sm border-r">Name of Work</td>
                <td class="p-sm border-r">HSN</td>
                <td class="p-sm border-r text-right">Qty</td>
                <td class="p-sm border-r">Unit</td>
                <td class="p-sm border-r text-right">Rate</td>
                <td class="p-sm text-right">Amount</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($estimate->items as $item)
                <tr class="row-border">
                    <td class="p-sm border-r">{{ $loop->iteration }}</td>
                    <td class="p-sm border-r">{{ $item->service_no }}</td>
                    <td class="p-sm border-r">{{ $item->product_name }}</td>
                    <td class="p-sm border-r">{{ $item->hsn_code }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($item->qty, 2) }}</td>
                    <td class="p-sm border-r">{{ $item->per_unit }}</td>
                    <td class="p-sm border-r text-right">{{ number_format($item->price, 2) }}</td>
                    <td class="p-sm text-right">{{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="border">
        <tr>
            <td class="p border-r text-sm" style="width: 50%">
                <div class="font-bold">Amount in words:</div>
                <div>{{ \App\Support\IndianCurrency::words((int) round($grandTotal)) }}</div>
            </td>
            <td class="p text-sm">
                <table>
                    <tr class="row-border">
                        <td class="p-sm">Subtotal</td>
                        <td class="p-sm text-right">{{ number_format($estimate->total, 2) }}</td>
                    </tr>
                    <tr class="row-border">
                        <td class="p-sm">CGST @ {{ config('company.cgst_rate') }}%</td>
                        <td class="p-sm text-right">{{ number_format($cgst, 2) }}</td>
                    </tr>
                    <tr class="row-border">
                        <td class="p-sm">SGST @ {{ config('company.sgst_rate') }}%</td>
                        <td class="p-sm text-right">{{ number_format($sgst, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="p-sm font-bold">Grand Total</td>
                        <td class="p-sm text-right font-bold">{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="border border-t text-center text-sm">
        <tr>
            <td class="p">for {{ config('company.name') }}</td>
        </tr>
    </table>
</body>
</html>
