<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Response;

class AppController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $user = $request->user();
        $files = $user->files()->latest()->limit(5)->get(['id', 'filename', 'size', 'type', 'created_at']);
        $weightsData = $user->weights()->latest('date')->limit(5)->get(['weight', 'date'])->sortBy('date')->values();
        $weights = $weightsData->pluck('weight');
        $dates = $weightsData->pluck('date')->map(fn ($date): string => $date->format('d.m'));

        $bloodData = $user->bloodPressures()
            ->latest('date')
            ->limit(5)
            ->get(['systolic', 'diastolic', 'date'])
            ->sortBy('date')
            ->values();
        $systolics = $bloodData->pluck('systolic');
        $diastolics = $bloodData->pluck('diastolic');
        $bloodDates = $bloodData->pluck('date')->map(fn ($date): string => $date->format('d.m'));
        $lastPressure = $bloodData->last();

        return Inertia('Dashboard', [
            'user' => $user->only(['name', 'weight', 'proper_weight', 'blood_recommendations']),
            'weights' => $weights,
            'dates' => $dates,
            'systolics' => $systolics,
            'diastolics' => $diastolics,
            'blood_dates' => $bloodDates,
            'last_pressure' => $lastPressure?->only(['systolic', 'diastolic']),
            'files' => $files,
        ]);
    }
}
