<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes untuk Integrasi Node.js (Baileys)
|--------------------------------------------------------------------------
|
| Rute di bawah ini akan diakses oleh server Node.js untuk menyinkronkan
| status bot (active/inactive) dan mengirimkan pesan WhatsApp masuk ke Laravel.
|
*/

Route::prefix('webhook/whatsapp')->group(function () {
    Route::post('/status', [WebhookController::class, 'updateStatus']);
    Route::post('/message', [WebhookController::class, 'receiveMessage']);
});