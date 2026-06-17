<?php

namespace Tests\Feature;

use App\Models\DietDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeneratedHtmlSanitizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_generated_diet_html_is_sanitized_before_storage(): void
    {
        Http::fake([
            'api.openai.com/v1/responses' => Http::response([
                'output' => [[
                    'content' => [[
                        'text' => json_encode([[
                            'day' => 'Poniedziałek',
                            'protein' => 120,
                            'fat' => 70,
                            'carbohydrates' => 220,
                            'content' => '<ul onclick="alert(1)"><li><script>alert(1)</script><b style="color:red">Śniadanie:</b> Owsianka</li></ul>',
                        ]]),
                    ]],
                ]],
            ]),
        ]);

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
    }
}
