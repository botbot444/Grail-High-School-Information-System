<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $payment->payment_id }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Figtree', sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 24px;
            color: #111827;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 32px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #111827;
            padding-bottom: 16px;
            margin-bottom: 16px;
        }
        .school h1 { margin: 0; font-size: 22px; }
        .school p { margin: 4px 0 0; color: #6b7280; font-size: 13px; }
        .meta { text-align: right; }
        .meta p { margin: 2px 0; font-size: 13px; }
        .section { margin-top: 20px; }
        .section h2 { font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 6px; text-align: left; font-size: 14px; border-bottom: 1px solid #f3f4f6; }
        th { color: #6b7280; font-weight: 600; }
        .text-right { text-align: right; }
        .total { font-weight: 700; }
        .actions { margin-top: 24px; display: flex; gap: 8px; }
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px; border-radius: 8px; font-size: 14px;
            text-decoration: none; border: 1px solid #e5e7eb;
            background: #fff; color: #111827;
        }
        .btn-primary { background: #111827; color: #fff; border-color: #111827; }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt { border: none; border-radius: 0; padding: 0; }
            .actions { display: none; }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="school">
                <h1>Grail School Information System</h1>
                <p>123 Academic Avenue, Nairobi, Kenya</p>
                <p>Tel: +254 700 000 000 | Email: finance@grail.school</p>
            </div>
            <div class="meta">
                <p><strong>Receipt #:</strong> {{ str_pad($payment->payment_id, 6, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Date:</strong> {{ $payment->payment_date->format('d M Y H:i') }}</p>
                <p><strong>Method:</strong> {{ $payment->method_label }}</p>
                @if($payment->reference_number)
                    <p><strong>Ref:</strong> {{ $payment->reference_number }}</p>
                @endif
            </div>
        </div>

        <div class="section">
            <h2>Student Information</h2>
            <p><strong>Name:</strong> {{ $payment->fee->student->full_name }}</p>
            <p><strong>Student No:</strong> {{ $payment->fee->student->student_number }}</p>
            <p><strong>Class:</strong> {{ $payment->fee->student->schoolClass->class_name ?? 'N/A' }}</p>
        </div>

        <div class="section">
            <h2>Fee Breakdown</h2>
            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payment->fee->feeItems as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->category }}</td>
                            <td class="text-right">ZMW {{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total">
                        <td colspan="2">Total Due</td>
                        <td class="text-right">ZMW {{ number_format($payment->fee->amount_due, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Total Paid</td>
                        <td class="text-right">ZMW {{ number_format($payment->fee->amount_paid, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">Balance</td>
                        <td class="text-right">ZMW {{ number_format($payment->fee->balance, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="section">
            <h2>Payment Details</h2>
            <table>
                <tr>
                    <th style="width: 200px;">Amount Paid</th>
                    <td class="total">ZMW {{ number_format($payment->amount, 2) }}</td>
                </tr>
                <tr>
                    <th>Payment Method</th>
                    <td>{{ $payment->method_label }}</td>
                </tr>
                <tr>
                    <th>Recorded By</th>
                    <td>{{ $payment->recordedBy?->name ?? 'N/A' }}</td>
                </tr>
                @if($payment->notes)
                    <tr>
                        <th>Notes</th>
                        <td>{{ $payment->notes }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <div class="actions">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fa-solid fa-print"></i> Print Receipt
            </button>
            <a href="{{ route('admin.fees.show', $payment->fee->fee_id) }}" class="btn">
                <i class="fa-solid fa-arrow-left"></i> Back to Fee
            </a>
        </div>
    </div>
</body>
</html>
