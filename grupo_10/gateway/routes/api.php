<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SentimentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


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




Route::middleware(['auth:sanctum'])->post('/send-notification', [NotificationController::class, 'sendNotification']);
Route::middleware(['auth:sanctum','role:admin'])->post('/sentiment', [SentimentController::class, 'flask']);


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::middleware('auth:sanctum')->get('/validate-token', [AuthController::class, 'validateToken']);


 Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
     return $request->user();
 });


 //Route::get('/admin-only', function () {
  //  return response()->json(['message' => 'Acceso concedido']);
//})->middleware(['auth:sanctum', 'role:admin']);





