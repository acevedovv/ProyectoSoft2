<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SentimentController extends Controller
{
    protected $apiUrl;


    public function __construct()
    {
    $this->apiUrl = env('MICROSERVICE_FLASK');
    }

    public function flask(Request $request)
    {
        $url = $this->apiUrl . '/sentiment';
        $response = Http::post($url, $request->all());
        return $response->json();
    }
}
