<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiUserPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_user_response_exposes_only_identity_fields(): void
    {
        $user = User::factory()->create([
            'diseases' => 'sensitive condition',
            'blood_recommendations' => '<p>sensitive analysis</p>',
        ]);

        Sanctum::actingAs($user, ['read']);

        $response = $this->getJson('/api/user');

        $response
            ->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'email_verified_at',
                'profile_photo_url',
            ])
            ->assertJsonMissingPath('diseases')
            ->assertJsonMissingPath('blood_recommendations')
            ->assertJsonMissingPath('google_id');

        $this->assertCount(5, $response->json());
    }

    public function test_api_user_requires_read_ability(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['update']);

        $this->getJson('/api/user')->assertForbidden();
    }
}
