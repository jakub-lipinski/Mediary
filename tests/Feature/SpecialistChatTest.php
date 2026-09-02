<?php

namespace Tests\Feature;

use App\Ai\Agents\SpecialistChatAgent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Attributes\Model;
use Tests\TestCase;

class SpecialistChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_chat(): void
    {
        $this->get(route('chat.index'))->assertRedirect(route('login'));
        $this->post(route('chat.store'))->assertRedirect(route('login'));
        $this->post(route('chat.stream'), ['message' => 'hej'])->assertRedirect(route('login'));
    }

    public function test_user_can_list_and_create_conversations(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->getJson(route('chat.index'))
            ->assertOk()
            ->assertJsonStructure(['conversations']);

        $res = $this->actingAs($user)->postJson(route('chat.store'))
            ->assertCreated()
            ->assertJsonStructure(['conversation' => ['id', 'title']]);

        $id = $res->json('conversation.id');
        $this->assertDatabaseHas('agent_conversations', ['id' => $id, 'user_id' => $user->id]);
    }

    public function test_user_can_send_message_sync_and_context_includes_profile(): void
    {
        config()->set('ai.conversations.generate_title', false);

        SpecialistChatAgent::fake(['Odpowiedź testowa sync'])
            ->preventStrayPrompts();

        $user = User::factory()->create([
            'age' => 42,
            'height' => '178',
            'weight' => '82',
            'gender' => 'male',
            'diseases' => 'nadciśnienie',
            'wbc' => '5.5',
        ]);

        // Create a file and diet to ensure context builder pulls them
        $user->files()->create([
            'filename' => 'wynik.pdf',
            'path' => 'files/1/test.pdf',
            'type' => 'pdf',
            'size' => 1.2,
            'review' => '<p>Przegląd pliku</p>',
        ]);
        $diet = $user->diets()->create([
            'name' => 'Dieta test',
            'type' => 'klasyczna',
            'calories' => 2000,
            'meals' => 3,
        ]);
        $diet->days()->create([
            'day' => 'Poniedziałek',
            'protein' => 100,
            'fat' => 50,
            'carbohydrates' => 200,
            'content' => '<ul><li><b>Śniadanie:</b> owsianka</li></ul>',
        ]);

        $response = $this->actingAs($user)->postJson(route('chat.send'), [
            'message' => 'Jak moje wyniki?',
        ])->assertOk()->assertJsonStructure(['content', 'conversation_id']);

        $this->assertSame('Odpowiedź testowa sync', $response->json('content'));

        SpecialistChatAgent::assertPrompted(function ($prompt) {
            return str_contains($prompt->prompt, 'Jak moje wyniki?')
                && str_contains($prompt->prompt, '"age":42')
                && str_contains($prompt->prompt, '"diseases"')
                && str_contains($prompt->prompt, '<trusted_patient_context>')
                && str_contains($prompt->prompt, '<untrusted_user_message>')
                && str_contains($prompt->prompt, 'nadciśnienie');
        });

        $convId = $response->json('conversation_id');
        $this->assertDatabaseHas('agent_conversation_messages', [
            'conversation_id' => $convId,
            'role' => 'user',
        ]);
        $this->assertDatabaseHas('agent_conversation_messages', [
            'conversation_id' => $convId,
            'role' => 'assistant',
        ]);
    }

    public function test_user_can_continue_conversation(): void
    {
        config()->set('ai.conversations.generate_title', false);

        SpecialistChatAgent::fake(['Pierwsza', 'Druga odpowiedź'])
            ->preventStrayPrompts();

        $user = User::factory()->create();

        $first = $this->actingAs($user)->postJson(route('chat.send'), [
            'message' => 'Cześć',
        ])->assertOk();

        $convId = $first->json('conversation_id');

        $second = $this->actingAs($user)->postJson(route('chat.send'), [
            'message' => 'Kontynuacja',
            'conversation_id' => $convId,
        ])->assertOk();

        $this->assertSame($convId, $second->json('conversation_id'));
        $this->assertSame('Druga odpowiedź', $second->json('content'));

        // History should contain 4 messages (2 user + 2 assistant)
        $this->actingAs($user)->getJson(route('chat.show', $convId))
            ->assertOk()
            ->assertJsonPath('messages.0.role', 'user')
            ->assertJsonPath('messages.1.role', 'assistant');
    }

    public function test_streaming_endpoint_returns_sse(): void
    {
        config()->set('ai.conversations.generate_title', false);

        SpecialistChatAgent::fake(['Streamowana odpowiedź testowa'])
            ->preventStrayPrompts();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('chat.stream'), [
            'message' => 'Test stream',
        ], ['Accept' => 'text/event-stream']);

        $response->assertOk();
        $this->assertStringContainsString('text/event-stream', $response->headers->get('Content-Type'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('text_delta', $content);
        $this->assertStringContainsString('Streamowana', $content);
    }

    public function test_chat_validates_message(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson(route('chat.send'), [
            'message' => '',
        ])->assertUnprocessable()->assertJsonValidationErrors('message');

        $this->actingAs($user)->postJson(route('chat.send'), [
            'message' => str_repeat('a', 2001),
        ])->assertUnprocessable()->assertJsonValidationErrors('message');
    }

    public function test_user_cannot_access_other_users_conversation(): void
    {
        config()->set('ai.conversations.generate_title', false);

        $owner = User::factory()->create();
        $other = User::factory()->create();

        SpecialistChatAgent::fake(['hej'])->preventStrayPrompts();
        $res = $this->actingAs($owner)->postJson(route('chat.send'), ['message' => 'hej'])->assertOk();
        $convId = $res->json('conversation_id');

        $this->actingAs($other)->getJson(route('chat.show', $convId))->assertNotFound();
        $this->actingAs($other)->postJson(route('chat.send'), [
            'message' => 'probe',
            'conversation_id' => $convId,
        ])->assertUnprocessable();
    }

    public function test_chat_can_be_deleted(): void
    {
        config()->set('ai.conversations.generate_title', false);

        $user = User::factory()->create();
        SpecialistChatAgent::fake(['hej'])->preventStrayPrompts();

        $res = $this->actingAs($user)->postJson(route('chat.send'), ['message' => 'hej'])->assertOk();
        $convId = $res->json('conversation_id');

        $this->actingAs($user)->deleteJson(route('chat.destroy', $convId))->assertOk();
        $this->assertDatabaseMissing('agent_conversations', ['id' => $convId]);
    }

    public function test_specialist_agent_uses_correct_model(): void
    {
        $reflection = new \ReflectionClass(SpecialistChatAgent::class);
        $attrs = $reflection->getAttributes(Model::class);
        $this->assertNotEmpty($attrs);
        $this->assertSame('gpt-5.6-luna', $attrs[0]->newInstance()->value);
    }
}
