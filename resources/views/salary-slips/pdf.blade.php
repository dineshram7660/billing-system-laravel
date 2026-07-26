<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Salary Slip — {{ $salarySlip->employee?->employee_name }} ({{ $salarySlip->salary_slip_month }} {{ $salarySlip->salary_slip_year }})</title>
    @include('pdf._styles')
</head>
<body>
    <table class="border mb">
        <tr>
            <td class="p text-center font-bold">Salary Slip — {{ $salarySlip->salary_slip_month }} {{ $salarySlip->salary_slip_year }}</td>
        </tr>
        <tr>
            <td class="p border-t text-sm">
                <table>
                    <tr>
                        <td class="p-sm" style="width: 50%">Name: {{ $salarySlip->employee?->employee_name }}</td>
                        <td class="p-sm">Days Worked: {{ $salarySlip->day_work }}</td>
                    </tr>
                    <tr>
                        <td class="p-sm">Designation: {{ $salarySlip->employee?->designation?->designation_name }}</td>
                        <td class="p-sm">Overtime Hours: {{ $salarySlip->over_time }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="border mb text-sm">
        <thead>
            <tr class="border-b font-bold">
                <td class="p-sm border-r">Earnings</td>
                <td class="p-sm border-r text-right">Amount</td>
                <td class="p-sm border-r">Deductions</td>
                <td class="p-sm text-right">Amount</td>
            </tr>
        </thead>
        <tbody>
            <tr class="row-border">
                <td class="p-sm border-r">Basic Pay</td>
                <td class="p-sm border-r text-right">{{ number_format($earnings['basic_pay'], 2) }}</td>
                <td class="p-sm border-r">PF Amount</td>
                <td class="p-sm text-right">{{ number_format($salarySlip->pf_amount, 2) }}</td>
            </tr>
            <tr class="border-b">
                <td class="p-sm border-r">Extra Allowance</td>
                <td class="p-sm border-r text-right">{{ number_format($earnings['extra_allowance'], 2) }}</td>
                <td class="p-sm border-r">Professional Tax</td>
                <td class="p-sm text-right">{{ number_format($salarySlip->professional_tax, 2) }}</td>
            </tr>
            <tr class="border-b font-bold">
                <td class="p-sm border-r">Total Earnings</td>
                <td class="p-sm border-r text-right">{{ number_format($earnings['total_pay'], 2) }}</td>
                <td class="p-sm border-r">Total Deductions</td>
                <td class="p-sm text-right">{{ number_format($earnings['total_deductions'], 2) }}</td>
            </tr>
            <tr class="font-bold">
                <td class="p-sm" colspan="2"></td>
                <td class="p-sm border-r">Net Pay</td>
                <td class="p-sm text-right">{{ number_format($earnings['net_pay'], 2) }}</td>
            </tr>
            <tr>
                <td class="p-sm" colspan="2"></td>
                <td class="p-sm border-r">Advance Payment This Month</td>
                <td class="p-sm text-right">{{ number_format($advancePaymentThisMonth, 2) }}</td>
            </tr>
            <tr>
                <td class="p-sm" colspan="2"></td>
                <td class="p-sm border-r">Advance Deduction This Month</td>
                <td class="p-sm text-right">{{ number_format($salarySlip->advance_payment, 2) }}</td>
            </tr>
            <tr>
                <td class="p-sm" colspan="2"></td>
                <td class="p-sm border-r">Total Advance Payment Outstanding</td>
                <td class="p-sm text-right">{{ number_format($totalAdvanceOutstanding, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="border-t text-sm">
        <tr>
            <td class="p" style="width: 50%">
                <br><br>
                __________________________________<br>
                Employer's Signature
            </td>
            <td class="p text-right">
                For, {{ config('company.name') }}<br><br><br>
                Authorised Signatory
            </td>
        </tr>
    </table>
</body>
</html>
