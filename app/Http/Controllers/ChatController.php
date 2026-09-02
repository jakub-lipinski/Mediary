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
use Illuminate\Validation\ValidationException;
use Laravel\Ai\Contracts\ConversationStore;
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

        try {
            // Use streaming per requirement
            return $agent->stream($prompt);
        } catch (AiException|ConnectionException|RequestException $e) {
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

        try {
            $response = $agent->prompt($prompt);
        } catch (AiException|ConnectionException|RequestException) {
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
}
