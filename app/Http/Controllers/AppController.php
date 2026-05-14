<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

class AppController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $files = $user->files()->orderBy('created_at', 'desc')->limit(5)->get();
        $weightsData = $user->weights()->orderBy('date', 'desc')->limit(5)->get()->sortBy('date')->values();
        $weights = $weightsData->pluck('weight');
        $dates = $weightsData->pluck('date');

        $dates = $dates->map(function ($date) {
            return Carbon::parse($date)->format('d.m');
        });

        $bloodData = $user->blood_pressures()->orderBy('date', 'desc')->limit(5)->get()->sortBy('date')->values();
        $systolics = $bloodData->pluck('systolic');
        $diastolics = $bloodData->pluck('diastolic');
        $blood_dates = $bloodData->pluck('date');
        $last_pressure = $user->blood_pressures()->orderBy('date', 'desc')->first();

        $blood_dates = $blood_dates->map(function ($date) {
            return Carbon::parse($date)->format('d.m');
        });

        return Inertia('Dashboard', [
            'weights' => $weights,
            'dates' => $dates,
            'systolics' => $systolics,
            'diastolics' => $diastolics,
            'blood_dates' => $blood_dates,
            'last_pressure' => $last_pressure,
            'files' => $files,
        ]);
    }
}
