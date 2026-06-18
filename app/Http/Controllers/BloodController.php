<?php

namespace App\Http\Controllers;

use App\Ai\Agents\BloodPressureReviewer;
use App\Ai\Agents\BloodResultsReviewer;
use App\Http\Requests\Blood\StoreBloodPressureRequest;
use App\Http\Requests\Blood\UpdateBloodResultsRequest;
use App\Models\User;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class BloodController extends Controller
{
    public function index(): Response
    {
        return Inertia('Blood/Index');
    }

    public function update(UpdateBloodResultsRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $response = BloodResultsReviewer::make()->prompt(
            $this->bloodResultsPrompt($user, $data),
        );

        $data['blood_recommendations'] = $this->sanitizeGeneratedHtml($response['html'] ?? null);

        if (blank($data['blood_recommendations'])) {
            throw ValidationException::withMessages([
                'wbc' => 'Nie udało się przeanalizować wyników. Spróbuj ponownie później.',
            ]);
        }

        $user->update($data);

        ToastMagic::success('Successfully Created');

        return back();
    }

    public function pressure(StoreBloodPressureRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $otherPressures = $user->bloodPressures()->where('date', '!=', $data['date'])->get(['systolic', 'diastolic', 'date']);

        $response = BloodPressureReviewer::make()->prompt(
            $this->bloodPressurePrompt($user, $data, $otherPressures->toArray()),
        );

        $data['review'] = trim(strip_tags((string) ($response['review'] ?? '')));

        if (blank($data['review'])) {
            throw ValidationException::withMessages([
                'systolic' => 'Nie udało się przeanalizować ciśnienia. Spróbuj ponownie później.',
            ]);
        }

        $user->bloodPressures()->create($data);

        Cache::forget('blood_pressures_'.$user->id);

        return redirect()->back();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function bloodResultsPrompt(User $user, array $data): string
    {
        return implode(' ', [
            'Przygotuj analizę wyników badań krwi dla pacjenta.',
            'Profil pacjenta: wiek '.$user->age.' lat, waga '.$user->weight.' kg, wzrost '.$user->height.' cm, płeć '.$user->gender.'.',
            'Wyniki badań i parametry: '.json_encode($data, JSON_UNESCAPED_UNICODE).'.',
            'Analizuj tylko wartości obecne w danych wejściowych. Nie zakładaj jednostek ani norm, jeśli nie wynikają z danych.',
            'Pierwszy akapit podsumowania powinien mieć około 75 słów. Uzasadnienia specjalistów powinny mieć około 25-30 słów każde.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $otherPressures
     */
    private function bloodPressurePrompt(User $user, array $data, array $otherPressures): string
    {
        return implode(' ', [
            'Przygotuj krótką opinię o pomiarze ciśnienia krwi.',
            'Profil pacjenta: wiek '.$user->age.' lat, waga '.$user->weight.' kg, wzrost '.$user->height.' cm, płeć '.$user->gender.'.',
            'Aktualny pomiar: '.json_encode($data, JSON_UNESCAPED_UNICODE).'.',
            'Poprzednie pomiary: '.json_encode($otherPressures, JSON_UNESCAPED_UNICODE).'.',
            'Jeśli poprzednie pomiary nie zmieniają oceny, skup się tylko na aktualnym pomiarze.',
        ]);
    }
}
