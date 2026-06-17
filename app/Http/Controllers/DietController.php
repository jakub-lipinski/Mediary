<?php

namespace App\Http\Controllers;

use App\Http\Requests\Diet\StoreDietRequest;
use App\Models\Diet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

class DietController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia('Diet/Index', [
            'diets' => $request->user()->diets()->with([
                'days' => fn ($query) => $query->orderBy('id'),
            ])->latest()->get(),
        ]);
    }

    public function store(StoreDietRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $apiKey = config('services.openai.key');

        if (blank($apiKey)) {
            throw ValidationException::withMessages([
                'name' => 'Generowanie diety jest chwilowo niedostępne.',
            ]);
        }

        // Input building
        $input = 'Przyjmij rolę dietetyka. Otrzymujesz informacje od swojego klienta i musisz stworzyć dla niego plan żywieniowy, rozpisać posiłki na cały tydzień. Typ diety to: '.$data['type'].'. Ilość kalorii: '.$data['calories'].'. Ilość posiłków w ciągu dnia: '.$data['meals'].'. Klient ma '.$user->age.' lat, ma '.$user->height.'cm wzrostu i waży '.$user->weight.'kg.';

        if ($user->diseases) {
            $input .= ' Klient ma stwierdzone następujące choroby: '.$user->diseases.'.';
        }

        if (filled($data['like'] ?? null)) {
            $input .= ' Klient lubi jeść: '.$data['like'].'. Natomiast nie dodawaj tego przesadnie dużo.';
        }

        if (filled($data['dislike'] ?? null)) {
            $input .= ' Klient nie lubi jeść: '.$data['dislike'].'. Tego nie dodawaj do diety wcale.';
        }

        if (filled($data['notes'] ?? null)) {
            $input .= ' Klient podał również dodatkowe informacje: '.$data['notes'].'.';
        }

        if ($data['documents'] ?? false) {
            $reviews = $user->files()->whereNotNull('review')->pluck('review')->all();

            if (! empty($reviews)) {
                $input .= ' Klient przesłał również jego dokumentację medyczną, oto opinie specjalisty na ich temat: '.implode('; ', $reviews).'.';
            }
        }

        $input .= 'Twoja odpowiedź musi być w formie json, bez użycia ``` lub Markdown. Format
        [
        {
            "day" : Dzień tygodnia,
            "protein" : liczba,
            "fat" : liczba,
            "carbohydrates" : liczba,
            "content": Dieta w formacie html, <ul><li><b>Nazwa posiłku:</b> Treść posiłku (gramatura użytych produktów) - ilosc kalorii.</li></ul>
        }
        ]';

        $input .= ' Dni tygodnia to: Poniedziałek, Wtorek, Środa, Czwartek, Piątek, Sobota, Niedziela. Nazwy posiłku nadaj w zależności od ilości posiłków w ciągu dnia. Jeśli klient wybrał 5, to: Śniadanie, Drugie śniadanie, Obiad, Podwieczorek, Kolacja. Jeśli 4 to: Sniadanie, Obiad, Podwieczorek, Kolacja. Jeśli 3 to: Sniadanie, Obiad, Kolacja.';

        $input .= ' Nie dodawaj żadnych swoich podsumowań, zwróć tylko dietę. Posiłki powinny być zróżnicowane na przestrzeni całego tygodnia. Posiłki muszą być pełnoprawnymi daniami. Nie twórz prostych połączeń typu jabłko z masłem orzechowym. Pamiętaj o wpisaniu w nawiasie przy każdym posiłku gramatury każdego produktu potrzebnego do wykonania dania.';

        $response = Http::acceptJson()->withToken($apiKey)->timeout(60)->post('https://api.openai.com/v1/responses', [
            'model' => 'gpt-4o-mini',
            'input' => $input,
        ])->json();

        $content = data_get($response, 'output.0.content.0.text');

        if (blank($content)) {
            throw ValidationException::withMessages([
                'name' => 'Nie udało się wygenerować diety. Spróbuj ponownie później.',
            ]);
        }

        $parsed = json_decode($content, true);

        if (! is_array($parsed)) {
            throw ValidationException::withMessages([
                'name' => 'Odpowiedź generatora diety była niepoprawna. Spróbuj ponownie.',
            ]);
        }

        DB::transaction(function () use ($data, $parsed, $user): void {
            $diet = $user->diets()->create([
                'name' => $data['name'],
                'type' => $data['type'],
                'calories' => $data['calories'],
                'meals' => $data['meals'],
                'like' => $data['like'] ?? null,
                'dislike' => $data['dislike'] ?? null,
                'notes' => $data['notes'] ?? null,
                'documents' => $data['documents'] ?? false,
            ]);

            foreach ($parsed as $day) {
                if (! is_array($day) || ! isset($day['day'], $day['protein'], $day['fat'], $day['carbohydrates'], $day['content'])) {
                    throw ValidationException::withMessages([
                        'name' => 'Wygenerowana dieta ma niepełne dane. Spróbuj ponownie.',
                    ]);
                }

                $diet->days()->create([
                    'day' => $day['day'],
                    'protein' => $day['protein'],
                    'fat' => $day['fat'],
                    'carbohydrates' => $day['carbohydrates'],
                    'content' => $this->sanitizeGeneratedHtml($day['content']),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Dieta stworzona pomyślnie.');
    }

    public function destroy(Request $request, Diet $diet): RedirectResponse
    {
        abort_unless($diet->user()->is($request->user()), 403);

        $diet->delete();

        return redirect()->back()->with('success', 'Dieta usunięta pomyślnie.');
    }
}
