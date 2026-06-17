<?php

namespace App\Http\Controllers;

use App\Http\Requests\File\StoreMedicalFileRequest;
use App\Models\File;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Response;
use Smalot\PdfParser\Parser;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileController extends Controller
{
    public function store(StoreMedicalFileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $type = $file->getClientMimeType();
        $size = round($file->getSize() / 1024 / 1024, 2);

        $text = $this->extractText($file);

        if (blank(trim($text))) {
            throw ValidationException::withMessages([
                'file' => 'Nie udało się odczytać treści pliku.',
            ]);
        }

        if (str_contains(mb_strtoupper($text), 'PESEL')) {
            throw ValidationException::withMessages([
                'file' => 'Plik zawiera wrażliwe dane osobowe.',
            ]);
        }

        $apiKey = config('services.openai.key');
        $this->ensureOpenAiKey($apiKey);

        $checkText = Http::acceptJson()->withToken($apiKey)->timeout(60)->post('https://api.openai.com/v1/responses', [
            'model' => 'gpt-4o-mini',
            'input' => 'Oceń, czy przesłana treść pliku ma jakikolwiek związek z medycyną. Jeśli tak - zwróć true, jeśli nie - false. Jeśli w pliku znajdują się jakieś dane typu: PESEL czy inne dane bardzo wrażliwe, zwróć - rodo. Czyli twoja odpowiedź może składać się tylko z jednego z tych trzech słów (true, false, rodo), absolutnie nic więcej. Treść pliku: '.$text,
        ]);

        $checkText = trim(data_get($checkText->json(), 'output.0.content.0.text', ''));

        if ($checkText !== 'true') {
            throw ValidationException::withMessages([
                'file' => $checkText === 'rodo'
                    ? 'Plik zawiera wrażliwe dane osobowe.'
                    : 'Plik nie zawiera treści medycznych.',
            ]);
        }

        $response = Http::acceptJson()->withToken($apiKey)->timeout(60)->post('https://api.openai.com/v1/responses', [
            'model' => 'gpt-4o-mini',
            'input' => 'Jesteś wykształconym lekarzem, otrzymujesz dokument od pacjenta. Pacjent ma '.$user->age.' lat, waży '.$user->weight.' kg i ma '.$user->height.' cm wzrostu. Płeć pacjenta to '.$user->gender.'. Stwierdzone choroby pacjenta to: '.$user->diseases.'. Jeśli chorób nie ma to nie bierz ich pod uwagę. Treść przesłanego dokumentu: '.$text.'. Napisz podsumowanie (około 150 słów) na podstawie treści przesłanego dokumentu. Podsumowanie zapisz w języku polskim. Do akapitów używaj tylko tagów html <p></p> (podziel swoją odpowiedź na 2/3 akapity dla lepszej czytelności). Ważne informacje, nazwy możesz zawierać w tagach <b></b>. Nie pisz swoich zaleceń. Pisz tylko suche fakty wynikające z pliku oraz profilu pacjenta. Zwracaj się do pacjenta na "ty".',
        ]);

        $review = $this->sanitizeGeneratedHtml(data_get($response->json(), 'output.0.content.0.text'), ['p', 'br', 'b', 'strong']);

        if (blank($review)) {
            throw ValidationException::withMessages([
                'file' => 'Nie udało się przeanalizować pliku. Spróbuj ponownie później.',
            ]);
        }

        if ($type === 'application/pdf') {
            $type = 'pdf';
        } elseif ($type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            $type = 'doc';
        }

        $path = $file->store('files/'.$user->id, 'public');

        $user->files()->create([
            'path' => $path,
            'filename' => $filename,
            'type' => $type,
            'size' => $size,
            'review' => $review,
        ]);

        Cache::forget('files_'.$user->id);

        return redirect()->back()->with('success', 'Plik przesłany pomyślnie.');
    }

    public function show(Request $request, File $file): Response
    {
        abort_unless($file->user()->is($request->user()), 404);

        return Inertia('File/Show', [
            'file' => $file,
        ]);
    }

    public function destroy(Request $request, File $file): RedirectResponse
    {
        $user = $request->user();
        abort_unless($file->user()->is($user), 404);

        Storage::disk('public')->delete($file->path);
        $file->delete();
        Cache::forget('files_'.$user->id);

        return redirect()->route('dashboard')->with('success', 'Plik usunięty pomyślnie.');
    }

    private function ensureOpenAiKey(?string $apiKey): void
    {
        if (blank($apiKey)) {
            throw ValidationException::withMessages([
                'file' => 'Analiza pliku jest chwilowo niedostępna.',
            ]);
        }
    }

    private function extractText(UploadedFile $file): string
    {
        return match ($file->getClientMimeType()) {
            'application/pdf' => $this->extractPdfText($file),
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => $this->extractDocxText($file),
            default => '',
        };
    }

    private function extractPdfText(UploadedFile $file): string
    {
        try {
            $parser = new Parser;

            return $parser->parseFile($file->getRealPath())->getText();
        } catch (\Throwable) {
            return '';
        }
    }

    private function extractDocxText(UploadedFile $file): string
    {
        $zip = new \ZipArchive;

        if ($zip->open($file->getRealPath()) !== true) {
            return '';
        }

        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($documentXml === false) {
            return '';
        }

        return trim(html_entity_decode(strip_tags($documentXml), ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }
}
