<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Salary Slip — {{ $salarySlip->employee?->employee_name }} ({{ $salarySlip->salary_slip_month }} {{ $salarySlip->salary_slip_year }})</title>
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

    <div class="mx-auto max-w-2xl border border-gray-800">
        <div class="border-b border-gray-800 p-3 text-center text-base font-bold">
            Salary Slip — {{ $salarySlip->salary_slip_month }} {{ $salarySlip->salary_slip_year }}
        </div>

        <div class="grid grid-cols-2 gap-2 border-b border-gray-800 p-3 text-xs">
            <div>Name: {{ $salarySlip->employee?->employee_name }}</div>
            <div>Days Worked: {{ $salarySlip->day_work }}</div>
            <div>Designation: {{ $salarySlip->employee?->designation?->designation_name }}</div>
            <div>Overtime Hours: {{ $salarySlip->over_time }}</div>
        </div>

        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-gray-800 font-semibold">
                    <td class="border-r border-b border-gray-800 p-2">Earnings</td>
                    <td class="border-r border-b border-gray-800 p-2 text-right">Amount</td>
                    <td class="border-r border-b border-gray-800 p-2">Deductions</td>
                    <td class="border-b border-gray-800 p-2 text-right">Amount</td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border-r border-gray-300 p-2">Basic Pay</td>
                    <td class="border-r border-gray-300 p-2 text-right tabular-nums">{{ number_format($earnings['basic_pay'], 2) }}</td>
                    <td class="border-r border-gray-300 p-2">PF Amount</td>
                    <td class="p-2 text-right tabular-nums">{{ number_format($salarySlip->pf_amount, 2) }}</td>
                </tr>
                <tr class="border-b border-gray-800">
                    <td class="border-r border-gray-300 p-2">Extra Allowance</td>
                    <td class="border-r border-gray-300 p-2 text-right tabular-nums">{{ number_format($earnings['extra_allowance'], 2) }}</td>
                    <td class="border-r border-gray-300 p-2">Professional Tax</td>
                    <td class="p-2 text-right tabular-nums">{{ number_format($salarySlip->professional_tax, 2) }}</td>
                </tr>
                <tr class="border-b border-gray-800 font-semibold">
                    <td class="border-r border-gray-300 p-2">Total Earnings</td>
                    <td class="border-r border-gray-300 p-2 text-right tabular-nums">{{ number_format($earnings['total_pay'], 2) }}</td>
                    <td class="border-r border-gray-300 p-2">Total Deductions</td>
                    <td class="p-2 text-right tabular-nums">{{ number_format($earnings['total_deductions'], 2) }}</td>
                </tr>
                <tr class="font-semibold">
                    <td class="p-2" colspan="2"></td>
                    <td class="border-r border-gray-300 p-2">Net Pay</td>
                    <td class="p-2 text-right tabular-nums">{{ number_format($earnings['net_pay'], 2) }}</td>
                </tr>
                <tr>
                    <td class="p-2" colspan="2"></td>
                    <td class="border-r border-gray-300 p-2">Advance Payment This Month</td>
                    <td class="p-2 text-right tabular-nums">{{ number_format($advancePaymentThisMonth, 2) }}</td>
                </tr>
                <tr>
                    <td class="p-2" colspan="2"></td>
                    <td class="border-r border-gray-300 p-2">Advance Deduction This Month</td>
                    <td class="p-2 text-right tabular-nums">{{ number_format($salarySlip->advance_payment, 2) }}</td>
                </tr>
                <tr>
                    <td class="p-2" colspan="2"></td>
                    <td class="border-r border-gray-300 p-2">Total Advance Payment Outstanding</td>
                    <td class="p-2 text-right tabular-nums">{{ number_format($totalAdvanceOutstanding, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <table class="w-full border-t border-gray-800 text-xs">
            <tr>
                <td class="p-3 align-top" style="width: 50%">
                    <br><br>
                    __________________________________<br>
                    Employer's Signature
                </td>
                <td class="p-3 text-right align-top">
                    For, {{ config('company.name') }}<br><br><br>
                    Authorised Signatory
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
