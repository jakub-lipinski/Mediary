<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;

class AppController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $user = $request->user();
        $files = $user->files()->latest()->limit(5)->get();
        $weightsData = $user->weights()->latest('date')->limit(5)->get()->sortBy('date')->values();
        $weights = $weightsData->pluck('weight');
        $dates = $weightsData->pluck('date')->map(fn ($date): string => $date->format('d.m'));

        $bloodData = $user->bloodPressures()->latest('date')->limit(5)->get()->sortBy('date')->values();
        $systolics = $bloodData->pluck('systolic');
        $diastolics = $bloodData->pluck('diastolic');
        $bloodDates = $bloodData->pluck('date')->map(fn ($date): string => $date->format('d.m'));
        $lastPressure = $user->bloodPressures()->latest('date')->first();

        return Inertia('Dashboard', [
            'weights' => $weights,
            'dates' => $dates,
            'systolics' => $systolics,
            'diastolics' => $diastolics,
            'blood_dates' => $bloodDates,
            'last_pressure' => $lastPressure,
            'files' => $files,
        ]);
    }
}
