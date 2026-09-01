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
        $days = collect(['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela'])
            ->map(fn (string $day): array => [
                'day' => $day,
                'protein' => 120,
                'fat' => 70,
                'carbohydrates' => 220,
                'content' => '<ul onclick="alert(1)"><li><script>alert(1)</script><b style="color:red">Śniadanie:</b> Owsianka</li></ul>',
            ])
            ->all();

        DietPlanGenerator::fake([[
            'days' => $days,
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

        $content = DietDay::query()->where('day', 'Poniedziałek')->value('content');

        $this->assertSame('<ul><li><b>Śniadanie:</b> Owsianka</li></ul>', $content);

        DietPlanGenerator::assertPrompted(
            fn ($prompt): bool => str_contains($prompt->prompt, '"diet_type":"klasyczna"')
                && str_contains($prompt->prompt, '<untrusted_user_data>')
        );
    }
}
