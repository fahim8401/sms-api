<?php

namespace App\Http\Controllers;

use App\Models\SmsLog;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ResellerController extends Controller
{
    /**
     * Check if user is reseller or admin
     */
    protected function checkAccess()
    {
        if (!in_array(auth()->user()->role, ['reseller', 'admin'])) {
            abort(403, 'Unauthorized access');
        }
    }

    /**
     * Show reseller dashboard
     */
    public function dashboard()
    {
        $this->checkAccess();
        $user = auth()->user();

        $stats = [
            'balance' => $user->balance,
            'rate' => $user->rate,
            'total_sub_users' => $user->subUsers()->count(),
            'total_sms' => $user->smsLogs()->count(),
            'this_month_sms' => $user->smsLogs()->whereMonth('created_at', now()->month)->count(),
            'this_month_cost' => $user->transactions()->whereMonth('created_at', now()->month)->where('type', 'debit')->sum('amount'),
        ];

        $recentSms = $user->smsLogs()->latest()->take(10)->get();
        $subUsers = $user->subUsers()->withCount('smsLogs')->get();

        return view('reseller.dashboard', compact('stats', 'recentSms', 'subUsers'));
    }

    /**
     * Show sub-users
     */
    public function subUsers()
    {
        $this->checkAccess();
        $subUsers = auth()->user()->subUsers()->withCount('smsLogs')->paginate(20);

        return view('reseller.sub-users', compact('subUsers'));
    }

    /**
     * Create sub-user
     */
    public function createSubUser(Request $request)
    {
        $this->checkAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'balance' => 'required|numeric|min:0',
            'rate' => 'required|numeric|min:0',
        ]);

        $reseller = auth()->user();

        // Check if reseller has enough balance to transfer
        if ($reseller->balance < $request->balance) {
            return back()->withErrors(['balance' => 'Insufficient balance to transfer']);
        }

        // Create sub-user
        $subUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'api_key' => User::generateApiKey(),
            'balance' => $request->balance,
            'rate' => $request->rate,
            'role' => 'user',
            'parent_id' => $reseller->id,
            'status' => 'active',
        ]);

        // Deduct from reseller balance
        $reseller->balance -= $request->balance;
        $reseller->save();

        // Log transactions
        Transaction::create([
            'user_id' => $reseller->id,
            'type' => 'transfer_out',
            'amount' => $request->balance,
            'description' => "Credit transferred to {$subUser->name}",
        ]);

        Transaction::create([
            'user_id' => $subUser->id,
            'type' => 'transfer_in',
            'amount' => $request->balance,
            'description' => "Credit received from {$reseller->name}",
        ]);

        return back()->with('success', 'Sub-user created successfully');
    }

    /**
     * Transfer credit to sub-user
     */
    public function transferCredit(Request $request, $id)
    {
        $this->checkAccess();
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $reseller = auth()->user();
        $subUser = User::where('parent_id', $reseller->id)->findOrFail($id);

        if ($reseller->balance < $request->amount) {
            return back()->withErrors(['amount' => 'Insufficient balance']);
        }

        // Transfer credit
        $reseller->balance -= $request->amount;
        $reseller->save();

        $subUser->balance += $request->amount;
        $subUser->save();

        // Log transactions
        Transaction::create([
            'user_id' => $reseller->id,
            'type' => 'transfer_out',
            'amount' => $request->amount,
            'description' => "Credit transferred to {$subUser->name}",
        ]);

        Transaction::create([
            'user_id' => $subUser->id,
            'type' => 'transfer_in',
            'amount' => $request->amount,
            'description' => "Credit received from {$reseller->name}",
        ]);

        return back()->with('success', 'Credit transferred successfully');
    }

    /**
     * Show invoices page
     */
    public function invoices()
    {
        $this->checkAccess();
        return view('reseller.invoices');
    }

    /**
     * Generate PDF invoice
     */
    public function generateInvoice(Request $request)
    {
        $this->checkAccess();
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $user = auth()->user();
        $date = \Carbon\Carbon::createFromFormat('Y-m', $request->month);

        $transactions = Transaction::where('user_id', $user->id)
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->orderBy('created_at', 'desc')
            ->get();

        $smsLogs = SmsLog::where('user_id', $user->id)
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->get();

        $summary = [
            'total_sms' => $smsLogs->count(),
            'total_cost' => $smsLogs->sum('cost'),
            'total_credits' => $transactions->where('type', 'credit')->sum('amount'),
            'total_debits' => $transactions->where('type', 'debit')->sum('amount'),
        ];

        $pdf = Pdf::loadView('reseller.invoice', [
            'user' => $user,
            'month' => $date->format('F Y'),
            'transactions' => $transactions,
            'smsLogs' => $smsLogs,
            'summary' => $summary,
        ]);

        return $pdf->download('invoice_' . $request->month . '.pdf');
    }
}
