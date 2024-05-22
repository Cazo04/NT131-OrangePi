<?php

namespace App\Http\Controllers;

use App\Models\RoomStatus;
use App\Models\SystemStatus;
use Carbon\Carbon;
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
    public function setSleepModeTime($time)
    {
        $response = Http::get("http://localhost:5000/setsleeptime/{$time}");
        if ($response->successful()) {
            return response()->json([
                'message' => $response->json()['message'],
            ], 200);
        } else {
            return response()->json([
                'error' => 'Failed to set sleep mode time.',
            ], 500);
        }
    }
    public function storeStatus($timestamp, $status)
    {
        RoomStatus::create([
            'timestamp' => $timestamp,
            'status' => $status
        ]);
    }
    public function updateSystemStatus($sleepModeTime, $automode)
    {
        $status = SystemStatus::first();

        if (!$status) {
            $status = new SystemStatus();
        }

        $status->sleep_mode_time = $sleepModeTime;
        $status->automode = $automode;
        $status->save();
    }
    public function getSystemStatus()
    {
        return SystemStatus::first();
    }
    public function setSleepModeTimeDatabase($timestamp)
    {
        $time = Carbon::createFromFormat('H:i', $timestamp);
        $status = SystemStatus::firstOrCreate([]);
        $status->sleep_mode_time = $time;
        $status->save();
        GPIOController::setSleepModeTime($timestamp);
    }

    public function setAutomodeDatabase($automode)
    {
        $status = SystemStatus::firstOrCreate([]);
        $status->automode = $automode;
        $status->save();
        GPIOController::setAutoMode($automode);
    }

}
