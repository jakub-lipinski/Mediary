<?php

namespace Tests\Feature;

use App\Ai\Agents\BloodResultsReviewer;
use App\Ai\Agents\DietPlanGenerator;
use App\Ai\Agents\MedicalFileClassifier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Exceptions\AiException;
use Tests\TestCase;
use ZipArchive;

class AiReliabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_provider_failure_returns_a_safe_validation_error(): void
    {
        BloodResultsReviewer::fake(fn () => throw new AiException('provider failure with internal details'))
            ->preventStrayPrompts();

        $user = User::factory()->create(['wbc' => '4.8']);

        $response = $this->actingAs($user)
            ->from(route('blood.index'))
            ->patch(route('blood.update'), ['wbc' => 5.5]);

        $response
            ->assertRedirect(route('blood.index'))
            ->assertSessionHasErrors([
                'wbc' => 'Usługa analizy wyników jest chwilowo niedostępna. Spróbuj ponownie później.',
            ]);

        $this->assertSame('4.800', $user->fresh()->wbc);
    }

    public function test_malformed_diet_response_is_not_persisted(): void
    {
        DietPlanGenerator::fake([[
            'days' => [[
                'day' => 'Poniedziałek',
                'protein' => 100,
                'fat' => 70,
                'carbohydrates' => 200,
                'content' => '<ul><li><b>Śniadanie:</b> Owsianka</li></ul>',
            ]],
        ]])->preventStrayPrompts();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('diet.store'), [
                'name' => 'Plan testowy',
                'type' => 'klasyczna',
                'calories' => 2000,
                'meals' => 4,
            ])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('diets', 0);
        $this->assertDatabaseCount('diet_days', 0);
    }

    public function test_file_is_not_stored_when_ai_provider_fails(): void
    {
        Storage::fake('medical');

        MedicalFileClassifier::fake(fn () => throw new AiException('provider unavailable'))
            ->preventStrayPrompts();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('file.store'), ['file' => $this->docxUpload('Morfologia krwi: WBC 5.5')])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('files', 0);
        Storage::disk('medical')->assertDirectoryEmpty('/');
    }

    public function test_ai_provider_storage_is_disabled_by_default(): void
    {
        $this->assertFalse(config('ai.providers.openai.store'));
    }

    private function docxUpload(string $text): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'mediary-docx-');
        $zip = new ZipArchive;

        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>'.e($text).'</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();

        $content = file_get_contents($path);
        @unlink($path);

        return UploadedFile::fake()
            ->createWithContent('results.docx', $content ?: '')
            ->mimeType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }
}
