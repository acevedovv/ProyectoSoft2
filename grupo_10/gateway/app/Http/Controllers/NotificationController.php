<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    //
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
    $this->apiUrl = env('MICROSERVICE_NOTIFICATION');
    $this->apiKey = env('X_API_Key');
    }

    public function sendNotification(Request $request) {
        echo "buenitas";

        $request->validate([
            'message' => 'required|string',
            'phone_number' => 'required|string'
        ]);
    
        try {
            // Enviar la notificación a través del microservicio
            $response = Http::withHeaders(['X-API-Key' => $this->apiKey])
                ->post($this->apiUrl . '/send-notification', [
                    'message' => $request->message,
                    'phone_number' => $request->phone_number
                ]);
    
            if ($response->successful()) {
                return response()->json(['message' => 'Notification sent successfully'], 200);
                
    
                
            } else {
                Log::error('Error sending notification: ' . $response->body());
                return response()->json(['error' => 'Failed to send notification', 'details' => $response->body()], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error sending notification: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send notification', 'details' => $e->getMessage()], 500);
        }
    }

    public function index()
    {
        $url = $this->apiUrl . '/notifications/';
        $response = Http::withHeaders(['X-API-Key' => $this->apiKey])->get($url);
        return $response->json();
    }

    public function store(Request $request)
    {
        $url = $this->apiUrl . '/notifications/';
        $response = Http::withHeaders(['X-API-Key' => $this->apiKey])->post($url, $request->all());
        return $response->json();
    }

    public function show(string $id)
    {
        $url = $this->apiUrl . '/notifications/'. $id;
        $response = Http::get($url);
        return $response->json();
    }

    public function update(Request $request, string $id)
    {
        $url = $this->apiUrl . '/notifications/'. $id;
        $response = Http::put($url, $request->all());
        return $response->json();
    }

    public function destroy(string $id)
    {
        $url = $this->apiUrl . '/notifications/'. $id;
        $response = Http::delete($url);
        return $response->json();
    }
}
