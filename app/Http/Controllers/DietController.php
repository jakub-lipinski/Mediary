<?php

namespace App\Http\Controllers;

use App\Ai\Agents\DietPlanGenerator;
use App\Http\Requests\Diet\StoreDietRequest;
use App\Models\Diet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
        $response = DietPlanGenerator::make()->prompt(
            $this->dietPlanPrompt($user, $data),
        );

        $days = $response['days'] ?? null;

        if (! is_array($days) || $days === []) {
            throw ValidationException::withMessages([
                'name' => 'Odpowiedź generatora diety była niepoprawna. Spróbuj ponownie.',
            ]);
        }

        DB::transaction(function () use ($data, $days, $user): void {
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

            foreach ($days as $day) {
                if (! is_array($day) || ! isset($day['day'], $day['protein'], $day['fat'], $day['carbohydrates'], $day['content'])) {
                    throw ValidationException::withMessages([
                        'name' => 'Wygenerowana dieta ma niepełne dane. Spróbuj ponownie.',
                    ]);
                }

                $diet->days()->create([
                    'day' => (string) $day['day'],
                    'protein' => (int) $day['protein'],
                    'fat' => (int) $day['fat'],
                    'carbohydrates' => (int) $day['carbohydrates'],
                    'content' => $this->sanitizeGeneratedHtml((string) $day['content']),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Dieta stworzona pomyślnie.');
    }

    public function destroy(Diet $diet): RedirectResponse
    {
        Gate::authorize('delete', $diet);

        $diet->delete();

        return redirect()->back()->with('success', 'Dieta usunięta pomyślnie.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function dietPlanPrompt(User $user, array $data): string
    {
        $parts = [
            'Utwórz tygodniowy plan żywieniowy dla klienta.',
            'Typ diety: '.$data['type'].'.',
            'Kalorie dziennie: '.$data['calories'].'.',
            'Liczba posiłków dziennie: '.$data['meals'].'.',
            'Profil klienta: wiek '.$user->age.' lat, wzrost '.$user->height.' cm, waga '.$user->weight.' kg.',
        ];

        if ($user->diseases) {
            $parts[] = 'Stwierdzone choroby: '.$user->diseases.'.';
        }

        if (filled($data['like'] ?? null)) {
            $parts[] = 'Klient lubi jeść: '.$data['like'].'. Uwzględnij to z umiarem.';
        }

        if (filled($data['dislike'] ?? null)) {
            $parts[] = 'Klient nie lubi jeść: '.$data['dislike'].'. Nie dodawaj tych produktów.';
        }

        if (filled($data['notes'] ?? null)) {
            $parts[] = 'Dodatkowe informacje od klienta: '.$data['notes'].'.';
        }

        if ($data['documents'] ?? false) {
            $reviews = $user->files()->whereNotNull('review')->pluck('review')->all();

            if ($reviews !== []) {
                $parts[] = 'Kontekst z dokumentacji medycznej: '.implode('; ', $reviews).'.';
            }
        }

        $parts[] = 'Nazwy posiłków zależą od liczby posiłków. Dla 5: Śniadanie, Drugie śniadanie, Obiad, Podwieczorek, Kolacja. Dla 4: Śniadanie, Obiad, Podwieczorek, Kolacja. Dla 3: Śniadanie, Obiad, Kolacja.';
        $parts[] = 'W każdym dniu zaplanuj pełne dania możliwe do przygotowania w domu. Makroskładniki mają być spójne z kalorycznością, ale mogą być rozsądnym przybliżeniem.';

        return implode(' ', $parts);
    }
}
