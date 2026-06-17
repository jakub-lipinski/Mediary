<?php

namespace App\Http\Controllers;

use App\Http\Requests\Blood\StoreBloodPressureRequest;
use App\Http\Requests\Blood\UpdateBloodResultsRequest;
use Devrabiul\ToastMagic\Facades\ToastMagic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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

        $apiKey = config('services.openai.key');

        if (blank($apiKey)) {
            throw ValidationException::withMessages([
                'wbc' => 'Analiza wyników jest chwilowo niedostępna.',
            ]);
        }

        $response = Http::acceptJson()->withToken($apiKey)->timeout(60)->post('https://api.openai.com/v1/responses', [
            'model' => 'gpt-4o-mini',
            'input' => 'Jesteś wykształconym lekarzem, otrzymujesz wyniki badania krwi i parametry pacjenta i musisz mu pomóc. Mam '.$user->age.' lata, ważę '.$user->weight.' kg i mam '.$user->height.' cm wzrostu. Moja płeć to '.$user->gender.'. Oto moje badania i parametry: '.json_encode($data).'. Podaj dokładne podsumowanie (Musi być około 75 słów pierwszego akapitu podsumowania). Zacznij od pogrubionego słowa "Podsumowanie". Każde odchylenia od normy dokładnie wytłumacz dotyczące moich wyników badań krwi w kontekście mojego wieku, wzrostu, wagi i płci. Następnie wypisz lekarzy specjalistów (ich nazwy niech są pogrubione), do jakich mam się udać wraz z krótkim uzasadnieniem (każde uzasadnienie po około 25/30 słów). Nic więcej nie pisz oprócz tego. Jeśli wszystkie wyniki są w normie, zasugeruj tylko wizytę u lekarza pierwszego kontaktu. Odpowiedź musi być w formie HTML. Odpowiedź ma być w takiej dokładnie formie: <p><b>Podsumowanie</b>: Podsumowanie </p><br> <ul class="flex flex-col gap-2"><li><b>Lekarz n</b>: Uzasadnienie n</li> <li><b>Lekarz n+1</b>: Uzasadnienie n+1</li> <li></li></ul>. n to na początku 1, wypisz tyle lekarzy ile trzeba, jeśli trzeba dwóch to tylko dwóch, jeśli trzeba pięciu to wypisz pięciu, a jeśli czterech to czterech - chodzi o to, żebyś wypisał tyle lekarzy ile faktycznie trzeba. Nie dodawaj żadnych swoich styli. Wypisuj rzeczywiste nazwy lekarzy, nie pisz wymyślonych nazw oraz nie pisz "lekarz 1", "lekarz 2" itd. Jeśli wszystko jest w normie, to jako specjalistę zaproponuj chociaż lekarza pierwszego kontaktu w celu systematycznej kontroli. Odpowiedz w języku polskim.',
        ]);

        $data['blood_recommendations'] = $this->sanitizeGeneratedHtml(data_get($response->json(), 'output.0.content.0.text'));

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

        $apiKey = config('services.openai.key');

        if (blank($apiKey)) {
            throw ValidationException::withMessages([
                'systolic' => 'Analiza ciśnienia jest chwilowo niedostępna.',
            ]);
        }

        $response = Http::acceptJson()->withToken($apiKey)->timeout(60)->post('https://api.openai.com/v1/responses', [
            'model' => 'gpt-4o-mini',
            'input' => 'Jesteś wykształconym lekarzem, otrzymujesz pomiar ciśnienia krwi i na tej podstawie oraz ogólnych parametrach pacjenta musisz wystawić krótką opinię. Mam '.$user->age.' lata, ważę '.$user->weight.' kg i mam '.$user->height.' cm wzrostu. Moja płeć to '.$user->gender.'. Oto moje badania i parametry: '.json_encode($data).'. Podaj bardzo krótką opinię na temat mojego ciśnienia krwi (maksymalnie 15 słów). Moje ciśnienie krwi to: '.$data['systolic'].'/'.$data['diastolic'].'. Moje wczesniejsze pomiary to (skurczowe, rozkurczowe, data): '.json_encode($otherPressures).'. Weź pod uwagę wcześniejsze moje pomiary ciśnienia. Jeśli ciśnienie jest prawidłowe, zawsze napisz "Ciśnienie prawidłowe, brak wskazań do obaw." Jeśli coś jest nieprawidłowo, napisz dokładnie co i którego parametru dotyczy. W opinii uwzględnij inne pomiary ciśnienia. Nie pisz o konieczności konsultacji z lekarzem. W odpowiedzi nie powtarzaj wyniku.',
        ]);

        $data['review'] = strip_tags((string) data_get($response->json(), 'output.0.content.0.text'));

        if (blank($data['review'])) {
            throw ValidationException::withMessages([
                'systolic' => 'Nie udało się przeanalizować ciśnienia. Spróbuj ponownie później.',
            ]);
        }

        $user->bloodPressures()->create($data);

        Cache::forget('blood_pressures_'.$user->id);

        return redirect()->back();
    }
}
