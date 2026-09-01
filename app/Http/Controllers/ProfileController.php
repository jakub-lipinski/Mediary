<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateHealthProfileRequest;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;
use Laravel\Fortify\Features;

class ProfileController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia('Profile/Index', [
            'user' => $user->only(['birthday', 'gender', 'weight', 'height', 'diseases']),
            'blood_pressures' => $user->bloodPressures()
                ->oldest('date')
                ->get(['id', 'systolic', 'diastolic', 'date', 'review'])
                ->values(),
            'files' => $user->files()
                ->latest()
                ->get(['id', 'filename', 'size', 'type', 'created_at'])
                ->values(),
        ]);
    }

    public function edit(Request $request): Response
    {
        return Inertia('Profile/Edit', [
            'sessions' => $request->session()->get('auth.sessions', []),
            'confirmsTwoFactorAuthentication' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
        ]);
    }

    public function update(UpdateHealthProfileRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['age'] = filled($data['birthday'] ?? null)
            ? Carbon::parse($data['birthday'])->age
            : null;

        DB::transaction(function () use ($data, $request): void {
            $user = $request->user();
            $user->update($data);

            if (filled($data['weight'] ?? null)) {
                $user->weights()->updateOrCreate(
                    ['date' => today()],
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
        });

        return redirect()->route('profile.index');
    }
}
