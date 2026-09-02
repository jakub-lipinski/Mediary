<?php

namespace App\Http\Controllers;

use App\Ai\Agents\SpecialistChatAgent;
use App\Http\Requests\Chat\StoreChatMessageRequest;
use App\Support\SpecialistContextBuilder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Ai\Ai;
use Laravel\Ai\Audio;
use Laravel\Ai\Contracts\ConversationStore;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Exceptions\AiException;
use Laravel\Ai\Models\Conversation;

class ChatController extends Controller
{
    public function __construct(
        private SpecialistContextBuilder $contextBuilder,
        private ConversationStore $conversationStore,
    ) {}

    /**
     * List conversations for current user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get(['id', 'title', 'created_at', 'updated_at']);

        return response()->json([
            'conversations' => $conversations,
        ]);
    }

    /**
     * Show messages for a conversation.
     */
    public function show(Request $request, string $conversation): JsonResponse
    {
        $user = $request->user();

        $conv = Conversation::query()
            ->where('id', $conversation)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $messages = $this->conversationStore
            ->getLatestConversationMessages($conv->id, 100)
            ->map(fn ($m) => [
                'role' => $m->role ?? 'assistant',
                'content' => $m->content ?? (string) $m,
            ])
            ->values();

        // Fallback: if SDK messages are Message objects, try to normalize
        $normalized = $messages->map(function ($item) {
            if (is_array($item)) {
                return $item;
            }
            if (is_object($item) && property_exists($item, 'content')) {
                // Determine role from class
                $role = $item->role ?? (str_contains(get_class($item), 'User') ? 'user' : 'assistant');
                $content = $item->content ?? '';
                if (is_object($content) && method_exists($content, '__toString')) {
                    $content = (string) $content;
                }

                return ['role' => $role, 'content' => $content];
            }

            return ['role' => 'assistant', 'content' => (string) $item];
        });

        // Alternative: query raw table for absolute certainty
        $rawMessages = DB::table(config('ai.conversations.tables.messages', 'agent_conversation_messages'))
            ->where('conversation_id', $conv->id)
            ->orderBy('created_at')
            ->get(['role', 'content', 'created_at']);

        $payload = $rawMessages->map(fn ($row) => [
            'role' => $row->role,
            'content' => $row->content,
            'created_at' => $row->created_at,
        ]);

        return response()->json([
            'conversation' => $conv,
            'messages' => $payload->isNotEmpty() ? $payload : $normalized,
        ]);
    }

    /**
     * Stream a new message (creates conversation if needed).
     */
    public function stream(StoreChatMessageRequest $request): mixed
    {
        $user = $request->user();
        $data = $request->validated();
        $userMessage = trim($data['message']);
        $conversationId = $data['conversation_id'] ?? null;

        if ($conversationId) {
            $exists = Conversation::query()
                ->where('id', $conversationId)
                ->where('user_id', $user->id)
                ->exists();

            if (! $exists) {
                throw ValidationException::withMessages([
                    'conversation_id' => 'Wybrana rozmowa nie istnieje.',
                ]);
            }
        }

        $prompt = $this->contextBuilder->buildPrompt($user, $userMessage);

        $agent = $conversationId
            ? (new SpecialistChatAgent)->continue($conversationId, $user)
            : (new SpecialistChatAgent)->forUser($user);

        if (blank(config('ai.providers.openai.key')) && ! Ai::hasFakeGatewayFor(SpecialistChatAgent::class)) {
            Log::error('Chat stream: missing OPENAI_API_KEY', ['userId' => $user->id]);

            throw ValidationException::withMessages([
                'message' => 'Brak konfiguracji OPENAI_API_KEY. Uzupełnij .env i uruchom php artisan config:clear.',
            ]);
        }

        try {
            return $agent->stream($prompt);
        } catch (RequestException $e) {
            $status = $e->response?->status();
            $body = $e->response ? (string) $e->response->body() : $e->getMessage();
            Log::error('Chat stream RequestException', [
                'userId' => $user->id,
                'status' => $status,
                'body' => mb_substr($body, 0, 2000),
            ]);

            // Fallback for invalid model (400) – retry with gpt-4o
            if ($status === 400 && str_contains(strtolower($body), 'model')) {
                try {
                    Log::warning('Chat stream fallback to gpt-4o', ['userId' => $user->id]);

                    return $agent->stream($prompt, model: 'gpt-4o');
                } catch (\Throwable $fallback) {
                    Log::error('Chat fallback also failed', ['error' => $fallback->getMessage()]);
                }
            }

            if ($status === 401) {
                throw ValidationException::withMessages([
                    'message' => 'Nieprawidłowy klucz OPENAI_API_KEY (401). Sprawdź .env i limit konta.',
                ]);
            }

            throw ValidationException::withMessages([
                'message' => 'Usługa czatu jest chwilowo niedostępna. Spróbuj ponownie później.',
            ]);
        } catch (AiException|ConnectionException $e) {
            Log::error('Chat stream AiException', ['userId' => $user->id, 'error' => $e->getMessage()]);

            throw ValidationException::withMessages([
                'message' => 'Usługa czatu jest chwilowo niedostępna. Spróbuj ponownie później.',
            ]);
        }
    }

    /**
     * Non-streaming fallback (useful for tests and slow clients).
     */
    public function send(StoreChatMessageRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $userMessage = trim($data['message']);
        $conversationId = $data['conversation_id'] ?? null;

        if ($conversationId) {
            $exists = Conversation::query()
                ->where('id', $conversationId)
                ->where('user_id', $user->id)
                ->exists();

            if (! $exists) {
                throw ValidationException::withMessages([
                    'conversation_id' => 'Wybrana rozmowa nie istnieje.',
                ]);
            }
        }

        $prompt = $this->contextBuilder->buildPrompt($user, $userMessage);

        $agent = $conversationId
            ? (new SpecialistChatAgent)->continue($conversationId, $user)
            : (new SpecialistChatAgent)->forUser($user);

        if (blank(config('ai.providers.openai.key')) && ! Ai::hasFakeGatewayFor(SpecialistChatAgent::class)) {
            Log::error('Chat send: missing OPENAI_API_KEY', ['userId' => $user->id]);

            throw ValidationException::withMessages([
                'message' => 'Brak konfiguracji OPENAI_API_KEY. Uzupełnij .env i uruchom php artisan config:clear.',
            ]);
        }

        try {
            $response = $agent->prompt($prompt);
        } catch (RequestException $e) {
            $status = $e->response?->status();
            $body = $e->response ? (string) $e->response->body() : $e->getMessage();
            Log::error('Chat send RequestException', ['userId' => $user->id, 'status' => $status, 'body' => mb_substr($body, 0, 2000)]);

            if ($status === 400 && str_contains(strtolower($body), 'model')) {
                try {
                    Log::warning('Chat send fallback to gpt-4o', ['userId' => $user->id]);
                    $response = $agent->prompt($prompt, model: 'gpt-4o');
                } catch (\Throwable $fallback) {
                    Log::error('Chat fallback also failed', ['error' => $fallback->getMessage()]);

                    throw ValidationException::withMessages([
                        'message' => 'Usługa czatu jest chwilowo niedostępna. Spróbuj ponownie później.',
                    ]);
                }
            } elseif ($status === 401) {
                throw ValidationException::withMessages([
                    'message' => 'Nieprawidłowy klucz OPENAI_API_KEY (401). Sprawdź .env i limit konta.',
                ]);
            } else {
                throw ValidationException::withMessages([
                    'message' => 'Usługa czatu jest chwilowo niedostępna. Spróbuj ponownie później.',
                ]);
            }
        } catch (AiException|ConnectionException $e) {
            Log::error('Chat send AiException', ['userId' => $user->id, 'error' => $e->getMessage()]);

            throw ValidationException::withMessages([
                'message' => 'Usługa czatu jest chwilowo niedostępna. Spróbuj ponownie później.',
            ]);
        }

        $text = trim((string) ($response->text ?? ''));

        if (blank($text)) {
            throw ValidationException::withMessages([
                'message' => 'Nie udało się uzyskać odpowiedzi. Spróbuj ponownie.',
            ]);
        }

        return response()->json([
            'content' => $text,
            'conversation_id' => $response->conversationId ?? $agent->currentConversation(),
        ]);
    }

    /**
     * Create an empty conversation.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversationId = $this->conversationStore->storeConversation(
            $user->id,
            'Nowa rozmowa'
        );

        $conv = Conversation::query()->find($conversationId);

        return response()->json([
            'conversation' => $conv,
        ], 201);
    }

    /**
     * Delete a conversation.
     */
    public function destroy(Request $request, string $conversation): JsonResponse
    {
        $user = $request->user();

        $conv = Conversation::query()
            ->where('id', $conversation)
            ->where('user_id', $user->id)
            ->firstOrFail();

        DB::table(config('ai.conversations.tables.messages', 'agent_conversation_messages'))
            ->where('conversation_id', $conv->id)
            ->delete();

        $conv->delete();

        return response()->json([
            'message' => 'Rozmowa usunięta.',
        ]);
    }

    /**
     * Generate TTS for specialist response via ElevenLabs.
     */
    public function audio(Request $request)
    {
        $request->validate([
            'text' => ['required', 'string', 'min:1', 'max:3000'],
        ], [
            'text.required' => 'Brak tekstu do odczytania.',
            'text.max' => 'Tekst jest za długi do odczytania.',
        ]);

        if (blank(config('ai.providers.eleven.key'))) {
            return response()->json([
                'message' => 'Usługa głosowa jest chwilowo niedostępna (brak konfiguracji).',
            ], 503);
        }

        $text = trim(strip_tags((string) $request->input('text')));
        // Remove markdown artifacts for cleaner speech
        $text = preg_replace('/[*_#`>\[\]]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        $text = trim($text);

        if (blank($text)) {
            return response()->json(['message' => 'Brak treści do odczytania.'], 422);
        }

        // ElevenLabs free friendly female voice - Rachel (21m00Tcm4TlvDq8ikWAM)
        // Alternative: default-female (Matilda XrExE9yKIg1WjnnlVkGX) is also free.
        $voiceId = config('ai.providers.eleven.voice') ?? env('ELEVENLABS_VOICE_ID', '21m00Tcm4TlvDq8ikWAM');

        try {
            $audio = Audio::of($text)
                ->voice($voiceId)
                ->generate(provider: Lab::ElevenLabs);
        } catch (AiException|ConnectionException|RequestException $e) {
            return response()->json([
                'message' => 'Usługa głosowa jest chwilowo niedostępna. Spróbuj ponownie później.',
            ], 503);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Nie udało się wygenerować audio.',
            ], 500);
        }

        $binary = $audio->content();
        $mime = $audio->mimeType() ?? 'audio/mpeg';

        return response($binary, 200, [
            'Content-Type' => $mime,
            'Content-Length' => (string) strlen($binary),
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
