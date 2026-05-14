<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,docx',
        ], [
            'file.required' => 'Plik jest wymagany.',
            'file.file' => 'Plik jest wymagany.',
            'file.mimes' => 'Plik musi być w formacie .pdf lub .docx.',
            'file.max' => 'Plik nie może być większy niż 10MB,',
        ]);

        $user = $request->user();

        if ($request->hasFile('file')) {
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
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'file' => 'Plik zawiera wrażliwe dane osobowe.',
                ]);
            }

            // OpenAI
            $api_key = config('services.openai.key');
            $this->ensureOpenAiKey($api_key);

            $check_text = Http::withHeaders([
                'Authorization' => 'Bearer '.$api_key,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-4o-mini',
                'input' => 'Oceń, czy przesłana treść pliku ma jakikolwiek związek z medycyną. Jeśli tak - zwróć true, jeśli nie - false. Jeśli w pliku znajdują się jakieś dane typu: PESEL czy inne dane bardzo wrażliwe, zwróć - rodo. Czyli twoja odpowiedź może składać się tylko z jednego z tych trzech słów (true, false, rodo), absolutnie nic więcej. Treść pliku: '.$text,
            ]);

            $check_text = trim(data_get($check_text->json(), 'output.0.content.0.text', ''));

            if ($check_text !== 'true') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'file' => $check_text === 'rodo'
                        ? 'Plik zawiera wrażliwe dane osobowe.'
                        : 'Plik nie zawiera treści medycznych.',
                ]);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$api_key,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-4o-mini',
                'input' => 'Jesteś wykształconym lekarzem, otrzymujesz dokument od pacjenta. Pacjent ma '.$user->age.' lat, waży '.$user->weight.' kg i ma '.$user->height.' cm wzrostu. Płeć pacjenta to '.$user->gender.'. Stwierdzone choroby pacjenta to: '.$user->diseases.'. Jeśli chorób nie ma to nie bierz ich pod uwagę. Treść przesłanego dokumentu: '.$text.'. Napisz podsumowanie (około 150 słów) na podstawie treści przesłanego dokumentu. Podsumowanie zapisz w języku polskim. Do akapitów używaj tylko tagów html <p></p> (podziel swoją odpowiedź na 2/3 akapity dla lepszej czytelności). Ważne informacje, nazwy możesz zawierać w tagach <b></b>. Nie pisz swoich zaleceń. Pisz tylko suche fakty wynikające z pliku oraz profilu pacjenta. Zwracaj się do pacjenta na "ty".',
            ]);

            $review = data_get($response->json(), 'output.0.content.0.text');

            if (blank($review)) {
                throw ValidationException::withMessages([
                    'file' => 'Nie udało się przeanalizować pliku. Spróbuj ponownie później.',
                ]);
            }

            if ($type == 'application/pdf') {
                $type = 'pdf';
            } elseif ($type == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
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
        }

        return redirect()->back()->with('success', 'Plik przesłany pomyślnie.');
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $file = $user->files()->find($id);
        abort_if(is_null($file), 404);

        return Inertia('File/Show', [
            'file' => $file,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $file = $user->files()->find($id);
        abort_if(is_null($file), 404);

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
            $parser = new \Smalot\PdfParser\Parser;

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
