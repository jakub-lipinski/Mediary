<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InertiaDataPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_shared_authenticated_user_excludes_health_and_authentication_secrets(): void
    {
        $user = User::factory()->create([
            'weight' => '72.5',
            'diseases' => 'Private medical history',
            'blood_recommendations' => '<p>Private recommendation</p>',
            'google_id' => 'google-secret',
        ]);

        $this->actingAs($user)
            ->get(route('diet.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('auth.user.id', $user->id)
                ->where('auth.user.email', $user->email)
                ->missing('auth.user.weight')
                ->missing('auth.user.diseases')
                ->missing('auth.user.blood_recommendations')
                ->missing('auth.user.password')
                ->missing('auth.user.google_id')
                ->missing('user'));
    }

    public function test_dashboard_exposes_only_fields_required_by_the_page(): void
    {
        $user = User::factory()->create([
            'weight' => '72.5',
            'diseases' => 'Private medical history',
        ]);

        File::query()->create([
            'user_id' => $user->id,
            'filename' => 'private.pdf',
            'path' => 'files/'.$user->id.'/private.pdf',
            'size' => '1.25',
            'type' => 'pdf',
            'review' => '<p>Private review</p>',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('user', fn (Assert $userProps) => $userProps
                    ->where('name', $user->name)
                    ->where('weight', '72.50')
                    ->has('proper_weight')
                    ->has('blood_recommendations')
                    ->missing('email')
                    ->missing('diseases'))
                ->has('files.0', fn (Assert $fileProps) => $fileProps
                    ->has('id')
                    ->has('filename')
                    ->has('created_at')
                    ->missing('path')
                    ->missing('review')
                    ->missing('user_id')));
    }
}
