<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResourceOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_cannot_view_files_owned_by_other_users(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();

        $file = $owner->files()->create([
            'filename' => 'results.pdf',
            'path' => 'files/'.$owner->id.'/results.pdf',
            'size' => '1.23',
            'type' => 'pdf',
            'review' => '<p>Opis wyników.</p>',
        ]);

        $response = $this->actingAs($visitor)->get(route('file.show', $file));

        $response->assertNotFound();
    }

    public function test_users_cannot_delete_files_owned_by_other_users(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();

        $file = $owner->files()->create([
            'filename' => 'results.pdf',
            'path' => 'files/'.$owner->id.'/results.pdf',
            'size' => '1.23',
            'type' => 'pdf',
            'review' => '<p>Opis wyników.</p>',
        ]);

        $response = $this->actingAs($visitor)->delete(route('file.destroy', $file));

        $response->assertNotFound();
        $this->assertModelExists($file);
    }

    public function test_users_cannot_delete_diets_owned_by_other_users(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();

        $diet = $owner->diets()->create([
            'name' => 'Plan tygodniowy',
            'type' => 'klasyczna',
            'calories' => '2000',
            'meals' => '4',
            'documents' => false,
        ]);

        $response = $this->actingAs($visitor)->delete(route('diet.destroy', $diet));

        $response->assertNotFound();
        $this->assertModelExists($diet);
    }

    public function test_users_cannot_delete_notes_owned_by_other_users(): void
    {
        $owner = User::factory()->create();
        $visitor = User::factory()->create();

        $note = $owner->notes()->create([
            'date' => today(),
            'mood' => 'dobry',
            'energy_level' => '7',
            'stress_level' => '3',
            'sleep_hours' => '8',
            'water_intake' => '2',
            'note' => 'Krótki wpis.',
        ]);

        $response = $this->actingAs($visitor)->delete(route('note.destroy', $note));

        $response->assertNotFound();
        $this->assertModelExists($note);
    }
}
