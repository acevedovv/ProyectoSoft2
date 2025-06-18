<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification as NotificationModel;
use App\Notifications\TwilioNotification;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class NotificationController extends Controller
{
    public function sendNotification(Request $request) {

        $request->validate([
            'message' => 'required|string',
            'phone_number' => 'required|string'
        ]);

        try {
            // Enviar el mensaje con Twilio
            
            $twilio = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
            $twilio->messages->create(
                $request->phone_number,
                [
                    'from' => env('TWILIO_PHONE_NUMBER'),
                    'body' => $request->message
                ]
            );

            // Registrar la notificación en la base de datos
            NotificationModel::create([
                'message' => $request->message,
                'phone_number' => $request->phone_number,
                'sent_at' => now()
            ]);

            return response()->json(['message' => 'Notification sent successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error sending notification: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send notification', 'details' => $e->getMessage()], 500);
        }
    }
    public function getNotifications()
    {
        $notifications = NotificationModel::all();
        return response()->json($notifications, 200);
    }

    public function getNotification($id)
    {
        $notification = NotificationModel::find($id);
        if ($notification) {
            return response()->json($notification, 200);
        } else {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }
    }

    public function updateNotification(Request $request, $id)
    {
        $notification = NotificationModel::find($id);
        if ($notification) {
            $notification->update($request->all());
            return response()->json(['message' => 'Notificación actualizada correctamente'], 200);
        } else {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }
    }

    public function deleteNotification($id)
    {
        $deleted = NotificationModel::destroy($id);
        if ($deleted) {
            return response()->json(['message' => 'Notificación eliminada correctamente'], 200);
        } else {
            return response()->json(['message' => 'Notificación no encontrada'], 404);
        }
    }
}



