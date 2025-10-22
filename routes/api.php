<?php

use App\Http\Controllers\SmsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public SMS API Endpoints (Maestro-style)
Route::get('/smsapi2', [SmsController::class, 'send'])->name('api.sms.send');
Route::get('/getDLR', [SmsController::class, 'getDLR'])->name('api.sms.dlr');
Route::get('/getBalance', [SmsController::class, 'getBalance'])->name('api.sms.balance');

// DLR Webhook from DigitalSquare
Route::post('/webhook/dlr', [SmsController::class, 'dlrWebhook'])->name('api.webhook.dlr');
