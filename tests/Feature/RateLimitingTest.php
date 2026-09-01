<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_medical_file_uploads_are_rate_limited_per_user(): void
    {
        $this->actingAs(User::factory()->create());

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('file.store'))->assertSessionHasErrors('file');
        }

        $this->post(route('file.store'))->assertTooManyRequests();
    }

    public function test_ai_generation_endpoints_are_rate_limited_per_user(): void
    {
        $this->actingAs(User::factory()->create());

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('diet.store'))->assertSessionHasErrors(['name', 'type']);
        }

        $this->post(route('diet.store'))->assertTooManyRequests();
    }
}
