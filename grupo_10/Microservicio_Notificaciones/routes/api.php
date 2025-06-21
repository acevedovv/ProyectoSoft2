<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;

Route::middleware(['gateway.auth'])->group(function () {
    Route::post('/send-notification', [NotificationController::class, 'sendNotification']);
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);
    Route::get('/notifications/{id}', [NotificationController::class, 'getNotification']);
    Route::put('/notifications/{id}', [NotificationController::class, 'updateNotification']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'deleteNotification']);
});