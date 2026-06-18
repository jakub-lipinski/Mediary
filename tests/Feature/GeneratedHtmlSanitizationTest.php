<?php

namespace Tests\Feature;

use App\Ai\Agents\DietPlanGenerator;
use App\Models\DietDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneratedHtmlSanitizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_diet_html_is_sanitized_before_storage(): void
    {
        DietPlanGenerator::fake([[
            'days' => [[
                'day' => 'Poniedziałek',
                'protein' => 120,
                'fat' => 70,
                'carbohydrates' => 220,
                'content' => '<ul onclick="alert(1)"><li><script>alert(1)</script><b style="color:red">Śniadanie:</b> Owsianka</li></ul>',
            ]],
        ]])->preventStrayPrompts();

        $user = User::factory()->create([
            'age' => '35',
            'height' => '180',
            'weight' => '80',
        ]);

        $response = $this->actingAs($user)->post(route('diet.store'), [
            'name' => 'Plan testowy',
            'type' => 'klasyczna',
            'calories' => 2000,
            'meals' => 4,
            'documents' => false,
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $content = DietDay::query()->sole()->content;

        $this->assertSame('<ul><li><b>Śniadanie:</b> Owsianka</li></ul>', $content);

        DietPlanGenerator::assertPrompted(
            fn ($prompt): bool => str_contains($prompt->prompt, 'Typ diety: klasyczna.')
                && str_contains($prompt->prompt, 'pełne dania')
        );
    }
}
