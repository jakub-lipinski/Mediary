<?php

namespace Tests\Feature;

use App\Ai\Agents\BloodPressureReviewer;
use App\Ai\Agents\BloodResultsReviewer;
use App\Ai\Agents\MedicalFileClassifier;
use App\Ai\Agents\MedicalFileReviewer;
use App\Models\File as MedicalFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class AiCommunicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_blood_results_are_reviewed_with_ai_sdk_agent(): void
    {
        BloodResultsReviewer::fake([[
            'html' => '<p onclick="alert(1)"><b>Podsumowanie</b>: Wyniki są stabilne.</p><ul class="gap"><li><b>Lekarz rodzinny</b>: Kontrola.</li></ul>',
        ]])->preventStrayPrompts();

        $user = User::factory()->create([
            'age' => '35',
            'height' => '180',
            'weight' => '80',
            'gender' => 'male',
        ]);

        $response = $this->actingAs($user)->patch(route('blood.update'), [
            'wbc' => 5.5,
            'rbc' => 4.7,
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(
            '<p><b>Podsumowanie</b>: Wyniki są stabilne.</p><ul><li><b>Lekarz rodzinny</b>: Kontrola.</li></ul>',
            $user->fresh()->blood_recommendations,
        );

        BloodResultsReviewer::assertPrompted(
            fn ($prompt): bool => str_contains($prompt->prompt, '"wbc":5.5')
                && str_contains($prompt->prompt, '"age":35')
                && str_contains($prompt->prompt, '<untrusted_user_data>')
        );
    }

    public function test_blood_pressure_is_reviewed_with_ai_sdk_agent(): void
    {
        BloodPressureReviewer::fake([[
            'review' => '<b>Ciśnienie prawidłowe, brak wskazań do obaw.</b>',
        ]])->preventStrayPrompts();

        $user = User::factory()->create([
            'age' => '35',
            'height' => '180',
            'weight' => '80',
            'gender' => 'male',
        ]);

        $user->bloodPressures()->create([
            'systolic' => 118,
            'diastolic' => 76,
            'date' => '2026-06-10',
            'review' => 'Poprzedni pomiar prawidłowy.',
        ]);

        $response = $this->actingAs($user)->post(route('blood.pressure'), [
            'systolic' => 120,
            'diastolic' => 80,
            'date' => '2026-06-18',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseHas('blood_pressures', [
            'user_id' => $user->id,
            'systolic' => 120,
            'diastolic' => 80,
            'review' => 'Ciśnienie prawidłowe, brak wskazań do obaw.',
        ]);

        BloodPressureReviewer::assertPrompted(
            fn ($prompt): bool => str_contains($prompt->prompt, '"systolic":120')
                && str_contains($prompt->prompt, '2026-06-10')
                && str_contains($prompt->prompt, '"previous_measurements"')
        );
    }

    public function test_medical_file_upload_is_classified_and_reviewed_with_ai_sdk_agents(): void
    {
        Storage::fake('medical');

        MedicalFileClassifier::fake([[
            'classification' => 'medical',
        ]])->preventStrayPrompts();

        MedicalFileReviewer::fake([[
            'html' => '<p style="color:red">Wynik <b onclick="alert(1)">morfologii</b> jest opisany.</p><script>alert(1)</script>',
        ]])->preventStrayPrompts();

        $user = User::factory()->create([
            'age' => '35',
            'height' => '180',
            'weight' => '80',
            'gender' => 'male',
            'diseases' => 'nadciśnienie',
        ]);

        $response = $this->actingAs($user)->post(route('file.store'), [
            'file' => $this->docxUpload('Morfologia krwi: WBC 5.5, RBC 4.7.'),
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $medicalFile = MedicalFile::query()->sole();

        $this->assertSame(
            '<p>Wynik <b>morfologii</b> jest opisany.</p>',
            $medicalFile->review,
        );

        Storage::disk('medical')->assertExists($medicalFile->path);
        $this->assertSame('docx', $medicalFile->type);

        MedicalFileClassifier::assertPrompted(
            fn ($prompt): bool => str_contains($prompt->prompt, 'Morfologia krwi')
                && str_contains($prompt->prompt, 'danych wrażliwych')
                && str_contains($prompt->prompt, '<untrusted_document>')
        );

        MedicalFileReviewer::assertPrompted(
            fn ($prompt): bool => str_contains($prompt->prompt, 'nadciśnienie')
                && str_contains($prompt->prompt, 'Morfologia krwi')
                && str_contains($prompt->prompt, '<untrusted_profile>')
        );
    }

    public function test_medical_file_upload_rejects_non_medical_classification_without_reviewing(): void
    {
        MedicalFileClassifier::fake([[
            'classification' => 'non_medical',
        ]])->preventStrayPrompts();

        MedicalFileReviewer::fake()->preventStrayPrompts();

        $user = User::factory()->create([
            'age' => '35',
            'height' => '180',
            'weight' => '80',
            'gender' => 'male',
        ]);

        $response = $this->actingAs($user)->from(route('dashboard'))->post(route('file.store'), [
            'file' => $this->docxUpload('To jest zwykła notatka bez danych medycznych.'),
        ]);

        $response
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('files', 0);

        MedicalFileReviewer::assertNeverPrompted();
    }

    private function docxUpload(string $text): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'mediary-docx-');
        $zip = new ZipArchive;

        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>'.e($text).'</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();

        $content = file_get_contents($path);
        @unlink($path);

        return UploadedFile::fake()
            ->createWithContent('results.docx', $content ?: '')
            ->mimeType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }
}
