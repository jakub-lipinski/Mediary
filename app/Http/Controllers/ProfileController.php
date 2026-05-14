<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Laravel\Fortify\Features;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $blood_pressures = Cache::rememberForever('blood_pressures_'.$user->id, function () use ($user) {
            return $user->blood_pressures()->orderBy('date', 'asc')->get();
        });

        $files = Cache::rememberForever('files_'.$user->id, function () use ($user) {
            return $user->files()->orderBy('created_at', 'desc')->get();
        });

        return Inertia('Profile/Index', [
            'blood_pressures' => $blood_pressures,
            'files' => $files,
        ]);
    }

    public function edit(Request $request)
    {
        $user = $request->user();

        return Inertia('Profile/Edit', [
            'user' => $user,
            'sessions' => $request->session()->get('auth.sessions', []),
            'confirmsTwoFactorAuthentication' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'gender' => 'nullable|string',
            'weight' => 'nullable|numeric|min:1|max:500',
            'height' => 'nullable|numeric|min:30|max:260',
            'birthday' => 'nullable|date|before:today',
            'diseases' => 'nullable|string',
        ]);

        $data['age'] = filled($data['birthday'] ?? null)
            ? Carbon::parse($data['birthday'])->age
            : null;

        $user = $request->user();
        $user->update($data);

        if (filled($data['weight'] ?? null)) {
            $user->weights()->updateOrCreate(
                ['date' => Carbon::today()],
                [
                    'weight' => $data['weight'],
                ]
            );
        }

        $user->proper_weight = null;

        if (filled($data['height'] ?? null)) {
            $height_in_meters = $data['height'] / 100;
            $min_weight = round(18.5 * ($height_in_meters * $height_in_meters), 1);
            $max_weight = round(24.9 * ($height_in_meters * $height_in_meters), 1);
            $user->proper_weight = $min_weight.'kg - '.$max_weight.'kg';
        }

        $user->save();

        return redirect()->route('profile.index');
    }

    // User logout
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
