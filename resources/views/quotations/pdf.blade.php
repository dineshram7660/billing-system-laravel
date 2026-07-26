<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quotation — {{ $quotation->subject }}</title>
    @include('pdf._styles')
</head>
<body>
    <table class="border mb">
        <tr>
            <td class="p text-sm" style="width: 50%">
                To,<br>
                <div>{!! nl2br(e($quotation->quotation_to)) !!}</div>
            </td>
            <td class="p text-sm text-right">
                Date: {{ $quotation->bill_date?->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td class="p border-t text-center font-bold uppercase" colspan="2">Quotation</td>
        </tr>
        <tr>
            <td class="p border-t text-sm" colspan="2">
                Dear Sir,<br>
                As per your Inquiry we are submitting our competitive Rates for <strong>{{ $quotation->subject }}</strong>
            </td>
        </tr>
    </table>

    <table class="border mb">
        <thead>
            <tr class="border-b font-bold">
                <td class="p-sm border-r" style="width: 60%">Particulars</td>
                <td class="p-sm border-r" style="width: 20%">Unit</td>
                <td class="p-sm text-right" style="width: 20%">Rate</td>
            </tr>
        </thead>
        <tbody>
            <tr class="border-b">
                <td class="p-sm border-r">{!! nl2br(e($quotation->particulars)) !!}</td>
                <td class="p-sm border-r">{{ $quotation->unit }}</td>
                <td class="p-sm text-right">{{ number_format(round($quotation->total), 0) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="border mb text-sm">
        <tr>
            <td class="p">
                <strong>NOTE:</strong>
                (1) Tax Extra —
                (2) Only Labour Charge In Above Rate —
                (3) Scaffolding &amp; Hydra in your scope —
                (4) Providing Of All Safety Equipment in your scope Like Hand Gloves, Goggles etc.
            </td>
        </tr>
    </table>

    <table class="text-sm">
        <tr>
            <td class="p" style="width: 60%">&nbsp;</td>
            <td class="p">
                <div class="mt">For,</div>
                <div>{{ config('company.quotation_entity_name') }}</div>
            </td>
        </tr>
    </table>
</body>
</html>
