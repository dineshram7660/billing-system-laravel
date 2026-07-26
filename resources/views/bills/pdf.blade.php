<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $bill->invoice_no }}</title>
    @include('pdf._styles')
</head>
<body>
    <table class="border mb">
        <tr>
            <td class="p text-center" colspan="2">
                <div class="text-lg font-bold uppercase">Tax Invoice</div>
                <div class="text-sm">(Under section 31 &amp; rule 46 of CGST Act)</div>
                <div class="font-bold">{{ config('company.name') }}</div>
                <div class="text-sm">GSTIN: {{ config('company.gstin') }} | PAN: {{ config('company.pan') }}</div>
            </td>
        </tr>
        <tr>
            <td class="p border-t border-r text-sm" style="width: 50%">
                {!! $bill->address !!}
                <div class="mt">{{ $bill->gst_no }}</div>
                <div>{{ $bill->bill_state }}</div>
            </td>
            <td class="p border-t text-sm">
                <div><strong>Invoice No.:</strong> {{ str_pad((string) $bill->invoice_no, 3, '0', STR_PAD_LEFT) }}</div>
                <div><strong>Invoice Date:</strong> {{ $bill->bill_date?->format('d/m/Y') }}</div>
                @if ($bill->ref_no)
                    <div><strong>Ref No.:</strong> {{ $bill->ref_no }}</div>
                @endif
                @if ($bill->ref_date)
                    <div><strong>Ref Date:</strong> {{ $bill->ref_date->format('d/m/Y') }}</div>
                @endif
                <div><strong>Department:</strong> {{ $bill->department?->department_name }}</div>
                @if ($bill->sir_name)
                    <div><strong>Attn.:</strong> {{ $bill->sir_name }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="border mb">
        <thead>
            <tr class="border-b font-bold">
                <td class="p-sm border-r">#</td>
                <td class="p-sm border-r">Service No</td>
                <td class="p-sm border-r">Description</td>
                <td class="p-sm border-r">HSN</td>
                <td class="p-sm border-r text-right">Qty</td>
                <td class="p-sm border-r">Unit</td>
                <td class="p-sm border-r text-right">Rate</td>
                <td class="p-sm text-right">Amount</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($bill->items as $item)
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

                @if ($bill->remark)
                    <div class="font-bold mt">Remark:</div>
                    <div>{{ $bill->remark }}</div>
                @endif

                <div class="font-bold mt">Bank Details:</div>
                <div>Bank: {{ config('company.bank_name') }}</div>
                <div>A/C No.: {{ config('company.account_no') }}</div>
                <div>IFSC: {{ config('company.ifsc_code') }}</div>
                <div>Vendor Code: {{ config('company.vendor_code') }}</div>
                <div class="mt">MSME Reg.: {{ config('company.msme_certificate') }}</div>
            </td>
            <td class="p text-sm">
                <table>
                    <tr class="row-border">
                        <td class="p-sm">Subtotal</td>
                        <td class="p-sm text-right">{{ number_format($bill->total, 2) }}</td>
                    </tr>
                    @if ($bill->gst_bill)
                        <tr class="row-border">
                            <td class="p-sm">CGST @ {{ config('company.cgst_rate') }}%</td>
                            <td class="p-sm text-right">{{ number_format($cgst, 2) }}</td>
                        </tr>
                        <tr class="row-border">
                            <td class="p-sm">SGST @ {{ config('company.sgst_rate') }}%</td>
                            <td class="p-sm text-right">{{ number_format($sgst, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="p-sm font-bold">Grand Total</td>
                        <td class="p-sm text-right font-bold">{{ number_format($grandTotal, 2) }}</td>
                    </tr>
                </table>

                @if ($bill->paid)
                    <div class="mt">Paid: {{ number_format($bill->paid_amount, 2) }} on {{ $bill->paid_date?->format('d/m/Y') }}</div>
                @endif
            </td>
        </tr>
    </table>

    <table class="border border-t text-center text-sm">
        <tr>
            <td class="p">
                Declaration: Turnover below ₹5 Crore. No reverse charge is applicable on this supply.
                <div class="mt">for {{ config('company.name') }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
