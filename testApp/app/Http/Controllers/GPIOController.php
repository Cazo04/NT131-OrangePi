<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GPIOController extends Controller
{
    public function setGPIO($pin, $state)
    {
        $response = Http::get("http://localhost:5000/control/{$pin}/{$state}");

        return response()->json([
            'status' => $response->successful(),
            'message' => $response->json()['message'],
        ]);
    }
    public function getDelaysStatus()
    {
        $response = Http::get("http://localhost:5000/status/delays");

        return response()->json($response->json());
    }
    public function setAutoMode($state)
    {
        $response = Http::get("http://localhost:5000/setauto/{$state}");
        if ($response->successful()) {
            return response()->json([
                'message' => $response->json()['message'],
            ], 200);
        } else {
            return response()->json([
                'error' => 'Failed to set auto mode.',
            ], 500);
        }
    }
}
