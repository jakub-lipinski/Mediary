<?php

namespace App\Http\Controllers;

use App\Ai\Agents\MedicalFileClassifier;
use App\Ai\Agents\MedicalFileReviewer;
use App\Http\Requests\File\StoreMedicalFileRequest;
use App\Models\File;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        $classification = MedicalFileClassifier::make()
            ->prompt($this->medicalFileClassificationPrompt($text))['classification'] ?? null;

        if ($classification !== 'medical') {
            throw ValidationException::withMessages([
                'file' => $classification === 'sensitive'
                    ? 'Plik zawiera wrażliwe dane osobowe.'
                    : 'Plik nie zawiera treści medycznych.',
            ]);
        }

        $response = MedicalFileReviewer::make()->prompt(
            $this->medicalFileReviewPrompt($user, $text),
        );

        $review = $this->sanitizeGeneratedHtml($response['html'] ?? null, ['p', 'br', 'b', 'strong']);

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

    private function medicalFileClassificationPrompt(string $text): string
    {
        return 'Sklasyfikuj treść pliku pod kątem medyczności i danych wrażliwych. Treść pliku: '.$text;
    }

    private function medicalFileReviewPrompt(User $user, string $text): string
    {
        return implode(' ', [
            'Przygotuj podsumowanie dokumentu medycznego.',
            'Profil pacjenta: wiek '.$user->age.' lat, waga '.$user->weight.' kg, wzrost '.$user->height.' cm, płeć '.$user->gender.'.',
            'Stwierdzone choroby pacjenta: '.($user->diseases ?: 'brak danych').'.',
            'Nie dodawaj informacji, których nie ma w dokumencie lub profilu pacjenta.',
            'Treść dokumentu: '.$text,
        ]);
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
