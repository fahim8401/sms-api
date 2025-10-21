<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $month }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #333; }
        .info { margin-bottom: 20px; }
        .info table { width: 100%; }
        .info td { padding: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .summary { background-color: #f9f9f9; padding: 15px; margin-top: 20px; }
        .summary h3 { margin-top: 0; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; text-align: center; color: #666; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>HPLink SMS Server</h1>
        <p>Invoice for {{ $month }}</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td><strong>User:</strong></td>
                <td>{{ $user->name }}</td>
                <td><strong>Email:</strong></td>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <td><strong>Role:</strong></td>
                <td>{{ ucfirst($user->role) }}</td>
                <td><strong>Rate:</strong></td>
                <td>৳{{ number_format($user->rate, 2) }} per SMS</td>
            </tr>
            <tr>
                <td><strong>Generated:</strong></td>
                <td>{{ now()->format('Y-m-d H:i:s') }}</td>
                <td><strong>Current Balance:</strong></td>
                <td>৳{{ number_format($user->balance, 2) }}</td>
            </tr>
        </table>
    </div>

    <h3>Transactions</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</td>
                    <td>{{ $transaction->description }}</td>
                    <td class="text-right">
                        @if($transaction->type == 'credit' || $transaction->type == 'transfer_in')
                            +৳{{ number_format($transaction->amount, 2) }}
                        @else
                            -৳{{ number_format($transaction->amount, 2) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No transactions found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>SMS Details</h3>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>To</th>
                <th>Sender ID</th>
                <th>Status</th>
                <th class="text-right">Cost</th>
            </tr>
        </thead>
        <tbody>
            @forelse($smsLogs->take(50) as $sms)
                <tr>
                    <td>{{ $sms->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $sms->to }}</td>
                    <td>{{ $sms->senderid }}</td>
                    <td>{{ ucfirst($sms->status) }}</td>
                    <td class="text-right">৳{{ number_format($sms->cost, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No SMS sent this month</td>
                </tr>
            @endforelse
            @if($smsLogs->count() > 50)
                <tr>
                    <td colspan="5" style="text-align: center; font-style: italic;">
                        Showing 50 of {{ $smsLogs->count() }} SMS. Download full report for complete details.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="summary">
        <h3>Summary</h3>
        <table>
            <tr>
                <td><strong>Total SMS Sent:</strong></td>
                <td class="text-right">{{ number_format($summary['total_sms']) }}</td>
            </tr>
            <tr>
                <td><strong>Total SMS Cost:</strong></td>
                <td class="text-right">৳{{ number_format($summary['total_cost'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Credits Received:</strong></td>
                <td class="text-right">৳{{ number_format($summary['total_credits'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Total Debits:</strong></td>
                <td class="text-right">৳{{ number_format($summary['total_debits'], 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This is a computer-generated invoice and does not require a signature.</p>
        <p>HPLink SMS Server PRO - {{ config('app.url') }}</p>
    </div>
</body>
</html>
