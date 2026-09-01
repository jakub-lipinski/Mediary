<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use ZipArchive;

class PrivateMedicalFileStorageTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_stream_a_private_medical_file(): void
    {
        Storage::fake('medical');

        $user = User::factory()->create();
        $file = $this->medicalFile($user);
        Storage::disk('medical')->put($file->path, 'private medical content');

        $response = $this->actingAs($user)->get(route('file.content', $file));

        $response
            ->assertOk()
            ->assertHeader('cache-control', 'max-age=0, no-store, private')
            ->assertHeader('x-content-type-options', 'nosniff');

        $this->assertSame('private medical content', $response->streamedContent());
    }

    public function test_other_users_cannot_stream_a_private_medical_file(): void
    {
        Storage::fake('medical');

        $owner = User::factory()->create();
        $visitor = User::factory()->create();
        $file = $this->medicalFile($owner);
        Storage::disk('medical')->put($file->path, 'private medical content');

        $this->actingAs($visitor)
            ->get(route('file.content', $file))
            ->assertNotFound();
    }

    public function test_file_page_does_not_expose_storage_path(): void
    {
        $user = User::factory()->create();
        $file = $this->medicalFile($user);

        $this->actingAs($user)
            ->get(route('file.show', $file))
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('File/Show')
                ->where('file.content_url', route('file.content', $file))
                ->missing('file.path'));
    }

    public function test_deleting_a_file_removes_it_from_private_storage(): void
    {
        Storage::fake('medical');

        $user = User::factory()->create();
        $file = $this->medicalFile($user);
        Storage::disk('medical')->put($file->path, 'private medical content');

        $this->actingAs($user)->delete(route('file.destroy', $file))->assertRedirect(route('dashboard'));

        Storage::disk('medical')->assertMissing($file->path);
        $this->assertModelMissing($file);
    }

    public function test_migration_command_moves_existing_public_files(): void
    {
        Storage::fake('public');
        Storage::fake('medical');

        $file = $this->medicalFile(User::factory()->create());
        Storage::disk('public')->put($file->path, 'legacy public content');

        $this->artisan('medical-files:migrate-private')
            ->expectsOutput('Migrated 1 medical file(s); 0 missing source file(s).')
            ->assertSuccessful();

        Storage::disk('public')->assertMissing($file->path);
        Storage::disk('medical')->assertExists($file->path, 'legacy public content');
    }

    public function test_docx_with_pathological_compression_is_rejected_before_ai_processing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('file.store'), [
            'file' => $this->compressedDocxUpload(),
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseCount('files', 0);
    }

    public function test_file_content_must_match_its_extension(): void
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()
            ->createWithContent('disguised.docx', "%PDF-1.4\n%%EOF")
            ->mimeType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $this->actingAs($user)
            ->post(route('file.store'), ['file' => $file])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('files', 0);
    }

    private function medicalFile(User $user): File
    {
        return $user->files()->create([
            'filename' => 'results.pdf',
            'path' => 'files/'.$user->id.'/results.pdf',
            'size' => '1.23',
            'type' => 'pdf',
            'review' => '<p>Opis wyników.</p>',
        ]);
    }

    private function compressedDocxUpload(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'mediary-docx-');
        $zip = new ZipArchive;

        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types></Types>');
        $zip->addFromString('word/document.xml', str_repeat('<w:t>result</w:t>', 400000));
        $zip->close();

        $content = file_get_contents($path);
        @unlink($path);

        return UploadedFile::fake()
            ->createWithContent('compressed.docx', $content ?: '')
            ->mimeType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }
}
