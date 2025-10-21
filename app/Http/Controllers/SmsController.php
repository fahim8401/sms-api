<?php

namespace App\Http\Controllers;

use App\Models\Gateway;
use App\Models\SmsLog;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    /**
     * Send SMS via DigitalSquare API
     * GET /api/smsapi2?api_key=#API_KEY#&type=text&contacts=#contact#&senderid=#SENDERID#&msg=#smstext#
     */
    public function send(Request $request)
    {
        $request->validate([
            'api_key' => 'required',
            'type' => 'required|in:text,unicode',
            'contacts' => 'required',
            'senderid' => 'required',
            'msg' => 'required',
        ]);

        // Find user by API key
        $user = User::where('api_key', $request->api_key)->where('status', 'active')->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid API key',
            ], 401);
        }

        // Calculate SMS cost (count SMS parts for long messages)
        $messageLength = strlen($request->msg);
        $smsParts = $messageLength > 160 ? ceil($messageLength / 153) : 1;
        $cost = $smsParts * $user->rate;

        // Check if user has sufficient balance
        if ($user->balance < $cost) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient balance',
                'required' => $cost,
                'available' => $user->balance,
            ], 402);
        }

        // Get active gateway
        $gateway = Gateway::where('status', 'active')->first();

        if (!$gateway) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active gateway configured',
            ], 503);
        }

        try {
            // Send SMS via DigitalSquare API
            $response = Http::get($gateway->api_url . '/smsapi', [
                'api_key' => $gateway->api_key,
                'type' => $request->type,
                'contacts' => $request->contacts,
                'senderid' => $request->senderid,
                'msg' => $request->msg,
            ]);

            $result = $response->json();

            // Deduct balance
            $user->balance -= $cost;
            $user->save();

            // Log transaction
            Transaction::create([
                'user_id' => $user->id,
                'type' => 'debit',
                'amount' => $cost,
                'description' => "SMS sent to {$request->contacts}",
            ]);

            // Log SMS
            $smsLog = SmsLog::create([
                'user_id' => $user->id,
                'to' => $request->contacts,
                'senderid' => $request->senderid,
                'message' => $request->msg,
                'cost' => $cost,
                'status' => isset($result['status']) && $result['status'] == 'success' ? 'sent' : 'failed',
                'message_id' => $result['message_id'] ?? null,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'SMS sent successfully',
                'message_id' => $smsLog->message_id,
                'cost' => $cost,
                'balance' => $user->balance,
            ]);

        } catch (\Exception $e) {
            Log::error('SMS Send Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to send SMS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Delivery Report
     * GET /api/getDLR?message_id=#MESSAGE_ID#
     */
    public function getDLR(Request $request)
    {
        $request->validate([
            'message_id' => 'required',
        ]);

        $smsLog = SmsLog::where('message_id', $request->message_id)->first();

        if (!$smsLog) {
            return response()->json([
                'status' => 'error',
                'message' => 'Message not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message_id' => $smsLog->message_id,
            'delivery_status' => $smsLog->status,
            'delivery_text' => $smsLog->delivery_text,
            'to' => $smsLog->to,
            'sent_at' => $smsLog->created_at->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get Balance
     * GET /api/getBalance?api_key=#API_KEY#
     */
    public function getBalance(Request $request)
    {
        $request->validate([
            'api_key' => 'required',
        ]);

        $user = User::where('api_key', $request->api_key)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid API key',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'balance' => $user->balance,
            'rate' => $user->rate,
            'name' => $user->name,
        ]);
    }

    /**
     * DLR Webhook from DigitalSquare
     * POST /api/webhook/dlr
     */
    public function dlrWebhook(Request $request)
    {
        $request->validate([
            'apikey' => 'required',
            'secretkey' => 'required',
            'Message_ID' => 'required',
            'text' => 'required',
        ]);

        // Verify webhook is from DigitalSquare
        $gateway = Gateway::where('api_key', $request->apikey)
            ->where('secret_key', $request->secretkey)
            ->first();

        if (!$gateway) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Update SMS log with delivery status
        $smsLog = SmsLog::where('message_id', $request->Message_ID)->first();

        if ($smsLog) {
            $smsLog->delivery_text = $request->text;
            
            // Update status based on delivery text
            if (in_array(strtoupper($request->text), ['DELIVRD', 'DELIVERED'])) {
                $smsLog->status = 'delivered';
            } elseif (in_array(strtoupper($request->text), ['FAILED', 'UNDELIV'])) {
                $smsLog->status = 'failed';
            }
            
            $smsLog->save();

            Log::info("DLR Updated for Message ID: {$request->Message_ID}, Status: {$request->text}");

            return response()->json([
                'status' => 'success',
                'message' => 'DLR updated',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Message not found',
        ], 404);
    }
}
