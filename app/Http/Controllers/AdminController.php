<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use App\Models\SmsLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user()->role !== 'admin') {
                abort(403, 'Unauthorized access');
            }
            return $next($request);
        });
    }

    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_sms' => SmsLog::count(),
            'total_revenue' => Transaction::where('type', 'debit')->sum('amount'),
            'pending_sms' => SmsLog::where('status', 'pending')->count(),
            'sent_sms' => SmsLog::where('status', 'sent')->count(),
            'delivered_sms' => SmsLog::where('status', 'delivered')->count(),
        ];

        $recentSms = SmsLog::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact('stats', 'recentSms'));
    }

    /**
     * Show SMS logs
     */
    public function logs(Request $request)
    {
        $query = SmsLog::with('user');

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('date') && $request->date) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);
        $users = User::where('role', '!=', 'admin')->get();

        return view('admin.logs', compact('logs', 'users'));
    }

    /**
     * Show users list
     */
    public function users()
    {
        $users = User::withCount('smsLogs', 'transactions')->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * Show user edit form
     */
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        $resellers = User::where('role', 'reseller')->get();

        return view('admin.edit-user', compact('user', 'resellers'));
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'balance' => 'required|numeric|min:0',
            'rate' => 'required|numeric|min:0',
            'role' => 'required|in:admin,reseller,user',
            'status' => 'required|in:active,inactive',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'balance' => $request->balance,
            'rate' => $request->rate,
            'role' => $request->role,
            'parent_id' => $request->parent_id,
            'status' => $request->status,
        ]);

        if ($request->password) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        return redirect()->route('admin.users')->with('success', 'User updated successfully');
    }

    /**
     * Create user
     */
    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'balance' => 'required|numeric|min:0',
            'rate' => 'required|numeric|min:0',
            'role' => 'required|in:admin,reseller,user',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'api_key' => User::generateApiKey(),
            'balance' => $request->balance,
            'rate' => $request->rate,
            'role' => $request->role,
            'parent_id' => $request->parent_id,
            'status' => 'active',
        ]);

        return redirect()->route('admin.users')->with('success', 'User created successfully');
    }

    /**
     * Add credit to user
     */
    public function addCredit(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
        ]);

        $user = User::findOrFail($id);
        $user->balance += $request->amount;
        $user->save();

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'credit',
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Credit added successfully');
    }

    /**
     * Show settings
     */
    public function settings()
    {
        $gateways = Gateway::all();

        return view('admin.settings', compact('gateways'));
    }

    /**
     * Update gateway settings
     */
    public function updateGateway(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'api_url' => 'required|url',
            'api_key' => 'required|string',
            'secret_key' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        $gateway = Gateway::findOrFail($id);
        $gateway->update($request->all());

        return back()->with('success', 'Gateway updated successfully');
    }

    /**
     * Create gateway
     */
    public function createGateway(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'api_url' => 'required|url',
            'api_key' => 'required|string',
            'secret_key' => 'required|string',
        ]);

        Gateway::create([
            'name' => $request->name,
            'api_url' => $request->api_url,
            'api_key' => $request->api_key,
            'secret_key' => $request->secret_key,
            'status' => 'active',
        ]);

        return back()->with('success', 'Gateway created successfully');
    }
}
