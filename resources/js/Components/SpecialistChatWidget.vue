<script setup>
import { ref, nextTick, onMounted, watch } from "vue";

const isOpen = ref(false);
const conversations = ref([]);
const activeConversationId = ref(null);
const messages = ref([]);
const input = ref("");
const isStreaming = ref(false);
const isLoadingHistory = ref(false);
const isLoadingConversations = ref(false);
const error = ref("");
const messagesContainer = ref(null);

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    }
};

const fetchConversations = async () => {
    isLoadingConversations.value = true;
    try {
        const res = await fetch(route('chat.index'), {
            headers: { Accept: 'application/json' },
        });
        if (res.ok) {
            const data = await res.json();
            conversations.value = data.conversations || [];
            if (!activeConversationId.value && conversations.value.length > 0) {
                activeConversationId.value = conversations.value[0].id;
                await fetchMessages(activeConversationId.value);
            }
        }
    } catch (e) {
        // silent
    } finally {
        isLoadingConversations.value = false;
    }
};

const fetchMessages = async (conversationId) => {
    if (!conversationId) {
        messages.value = [];
        return;
    }
    isLoadingHistory.value = true;
    error.value = "";
    try {
        const res = await fetch(route('chat.show', conversationId), {
            headers: { Accept: 'application/json' },
        });
        if (res.ok) {
            const data = await res.json();
            messages.value = (data.messages || []).map((m) => ({
                role: m.role,
                content: m.content,
            }));
            await scrollToBottom();
        } else {
            messages.value = [];
        }
    } catch (e) {
        error.value = "Nie udało się wczytać historii.";
    } finally {
        isLoadingHistory.value = false;
    }
};

const createConversation = async () => {
    try {
        const res = await fetch(route('chat.store'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        if (res.ok) {
            const data = await res.json();
            const conv = data.conversation;
            conversations.value.unshift(conv);
            activeConversationId.value = conv.id;
            messages.value = [];
            error.value = "";
        }
    } catch (e) {
        error.value = "Nie udało się utworzyć rozmowy.";
    }
};

const selectConversation = async (id) => {
    activeConversationId.value = id;
    await fetchMessages(id);
};

const ensureConversation = async () => {
    if (activeConversationId.value) return activeConversationId.value;
    // create new
    const res = await fetch(route('chat.store'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
    });
    if (!res.ok) throw new Error('create failed');
    const data = await res.json();
    const conv = data.conversation;
    conversations.value.unshift(conv);
    activeConversationId.value = conv.id;
    return conv.id;
};

const sendMessage = async () => {
    const text = input.value.trim();
    if (!text || isStreaming.value) return;
    if (text.length > 2000) {
        error.value = "Wiadomość za długa (max 2000 znaków).";
        return;
    }
    error.value = "";
    const userMsg = { role: 'user', content: text };
    messages.value.push(userMsg);
    input.value = "";
    await scrollToBottom();

    // streaming placeholder
    const assistantMsg = { role: 'assistant', content: '' };
    messages.value.push(assistantMsg);
    isStreaming.value = true;

    try {
        const convId = await ensureConversation();

        // Try streaming endpoint first
        const res = await fetch(route('chat.stream'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'text/event-stream',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                message: text,
                conversation_id: convId,
            }),
        });

        if (!res.ok) {
            // Try to parse validation error
            let errMsg = "Usługa czatu jest chwilowo niedostępna.";
            try {
                const errData = await res.json();
                errMsg = errData.message || errData.errors?.message?.[0] || errMsg;
            } catch {}
            throw new Error(errMsg);
        }

        // If response is JSON (fallback sync), handle
        const contentType = res.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            const data = await res.json();
            assistantMsg.content = data.content || '';
            // update active id if returned
            if (data.conversation_id && data.conversation_id !== activeConversationId.value) {
                activeConversationId.value = data.conversation_id;
                await fetchConversations();
            }
            await scrollToBottom();
            return;
        }

        // SSE streaming
        const reader = res.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';
        let fullText = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            buffer += decoder.decode(value, { stream: true });

            const parts = buffer.split('\n\n');
            buffer = parts.pop() || '';

            for (const part of parts) {
                const lines = part.split('\n');
                for (const line of lines) {
                    if (!line.startsWith('data:')) continue;
                    const payload = line.slice(5).trim();
                    if (!payload || payload === '[DONE]') continue;
                    try {
                        const evt = JSON.parse(payload);
                        if (evt.type === 'text_delta' && evt.delta) {
                            fullText += evt.delta;
                            assistantMsg.content = fullText;
                            await scrollToBottom();
                        } else if (evt.type === 'error') {
                            throw new Error(evt.message || 'Błąd strumienia');
                        }
                    } catch (e) {
                        // if not json, treat as plain delta
                        if (payload && !payload.startsWith('{')) {
                            fullText += payload;
                            assistantMsg.content = fullText;
                        }
                    }
                }
            }
        }

        // Final buffer flush
        if (buffer.trim().startsWith('data:')) {
            const payload = buffer.trim().slice(5).trim();
            if (payload && payload !== '[DONE]') {
                try {
                    const evt = JSON.parse(payload);
                    if (evt.type === 'text_delta' && evt.delta) {
                        fullText += evt.delta;
                        assistantMsg.content = fullText;
                    }
                } catch {}
            }
        }

        if (!fullText && !assistantMsg.content) {
            // fallback: fetch history again (maybe stored via non-stream path)
            await fetchMessages(convId);
        } else {
            // ensure final content set
            assistantMsg.content = fullText || assistantMsg.content;
        }

        // refresh conversations list (updated_at, title)
        await fetchConversations();
        // keep active
        if (convId) activeConversationId.value = convId;

    } catch (e) {
        // remove empty assistant placeholder and show error
        messages.value.pop();
        error.value = e.message || "Nie udało się wysłać wiadomości. Spróbuj ponownie.";
        // fallback to sync endpoint
        if (e.message.includes('niedostępna') || e.message.includes('Błąd')) {
            // try sync
            try {
                const convId = activeConversationId.value;
                const syncRes = await fetch(route('chat.send'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        message: text,
                        conversation_id: convId,
                    }),
                });
                if (syncRes.ok) {
                    const data = await syncRes.json();
                    messages.value.push({ role: 'assistant', content: data.content });
                    error.value = "";
                    await scrollToBottom();
                    await fetchConversations();
                    return;
                }
            } catch {}
        }
    } finally {
        isStreaming.value = false;
        await scrollToBottom();
    }
};

const handleKeydown = (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
};

const deleteConversation = async () => {
    if (!activeConversationId.value) return;
    if (!confirm('Usunąć tę rozmowę?')) return;
    try {
        const res = await fetch(route('chat.destroy', activeConversationId.value), {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });
        if (res.ok) {
            conversations.value = conversations.value.filter(c => c.id !== activeConversationId.value);
            activeConversationId.value = conversations.value[0]?.id || null;
            if (activeConversationId.value) {
                await fetchMessages(activeConversationId.value);
            } else {
                messages.value = [];
            }
        }
    } catch {}
};

watch(isOpen, async (open) => {
    if (open) {
        await fetchConversations();
        if (activeConversationId.value) {
            await fetchMessages(activeConversationId.value);
        }
        await scrollToBottom();
    }
});

onMounted(() => {
    // prefetch without opening
    fetchConversations();
});

const toggle = () => {
    isOpen.value = !isOpen.value;
};
</script>

<template>
    <div class="fixed bottom-0 right-0 z-50 flex flex-col items-end pointer-events-none">
        <!-- Panel -->
        <div
            v-show="isOpen"
            class="pointer-events-auto mr-4 mb-20 w-[92vw] max-w-[380px] h-[520px] bg-white rounded-2xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden sm:mr-6"
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="flex size-9 items-center justify-center rounded-full bg-blue-600 text-white shadow-sm">
                        <i class="fa-solid fa-user-doctor text-sm"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-slate-900 leading-4">Wirtualny specjalista</span>
                        <span class="text-[11px] text-slate-500 leading-3">Asystent AI • Mediary</span>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        @click="createConversation"
                        title="Nowa rozmowa"
                        class="size-8 rounded-full flex items-center justify-center text-slate-500 hover:bg-white hover:text-blue-600 border border-transparent hover:border-slate-200 transition"
                    >
                        <i class="fa-solid fa-plus text-xs"></i>
                    </button>
                    <button
                        v-if="activeConversationId"
                        @click="deleteConversation"
                        title="Usuń rozmowę"
                        class="size-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-white hover:text-red-500 border border-transparent hover:border-slate-200 transition"
                    >
                        <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                    <button
                        @click="isOpen = false"
                        class="size-8 rounded-full flex items-center justify-center text-slate-500 hover:bg-white hover:text-slate-700 border border-transparent hover:border-slate-200 transition"
                    >
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Disclaimer -->
            <div class="px-3 py-2 bg-amber-50 border-b border-amber-100 flex gap-2 items-start">
                <i class="fa-solid fa-circle-info text-amber-500 text-[11px] mt-1 shrink-0"></i>
                <p class="text-[11px] leading-4 text-amber-900">
                    <span class="font-semibold">To jest asystent AI.</span>
                    Informacje mają charakter edukacyjny i nie zastępują konsultacji ze specjalistą. W razie wątpliwości skontaktuj się z lekarzem.
                </p>
            </div>

            <!-- Conversations tabs -->
            <div v-if="conversations.length > 1" class="px-3 py-2 border-b border-slate-100 bg-white flex gap-1.5 overflow-x-auto">
                <button
                    v-for="conv in conversations.slice(0, 6)"
                    :key="conv.id"
                    @click="selectConversation(conv.id)"
                    :class="[
                        'shrink-0 px-3 py-1.5 rounded-full text-xs font-medium border transition truncate max-w-[120px]',
                        activeConversationId === conv.id
                            ? 'bg-blue-600 text-white border-blue-600 shadow-sm'
                            : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-50'
                    ]"
                    :title="conv.title"
                >
                    {{ conv.title || 'Rozmowa' }}
                </button>
            </div>

            <!-- Messages -->
            <div ref="messagesContainer" class="flex-1 overflow-y-auto px-3 py-4 space-y-3 bg-[#F9FAFC]">
                <div v-if="isLoadingHistory" class="flex justify-center py-6">
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <i class="fa-solid fa-spinner fa-spin"></i> Ładowanie historii...
                    </div>
                </div>

                <div v-else-if="messages.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
                    <div class="size-12 rounded-2xl bg-blue-50 border border-blue-100 flex items-center justify-center mb-3">
                        <i class="fa-solid fa-comments text-blue-500 text-lg"></i>
                    </div>
                    <p class="text-sm font-medium text-slate-700">Cześć! Jestem Twoim wirtualnym specjalistą.</p>
                    <p class="text-xs text-slate-500 mt-1 max-w-[260px] leading-4">
                        Mogę pomóc z interpretacją wyników, diety, dokumentów i dziennika. Zapytaj o cokolwiek — mam dostęp do Twojego profilu i badań.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-1.5 justify-center max-w-[300px]">
                        <button @click="input = 'Jak interpretować moje ostatnie wyniki krwi?'; $nextTick(() => sendMessage())" class="px-3 py-1.5 bg-white border border-slate-200 rounded-full text-xs text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition">Wyniki krwi</button>
                        <button @click="input = 'Podsumuj moje ciśnienie z ostatniego tygodnia.'; $nextTick(() => sendMessage())" class="px-3 py-1.5 bg-white border border-slate-200 rounded-full text-xs text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition">Ciśnienie</button>
                        <button @click="input = 'Co możesz powiedzieć o mojej diecie?'; $nextTick(() => sendMessage())" class="px-3 py-1.5 bg-white border border-slate-200 rounded-full text-xs text-slate-600 hover:bg-slate-50 hover:border-slate-300 transition">Dieta</button>
                    </div>
                </div>

                <template v-else>
                    <div v-for="(msg, idx) in messages" :key="idx" :class="['flex', msg.role === 'user' ? 'justify-end' : 'justify-start']">
                        <div
                            :class="[
                                'max-w-[82%] rounded-2xl px-4 py-2.5 text-sm leading-6 shadow-sm',
                                msg.role === 'user'
                                    ? 'bg-blue-600 text-white rounded-br-sm'
                                    : 'bg-white text-slate-800 border border-slate-200 rounded-bl-sm'
                            ]"
                        >
                            <p v-if="msg.role === 'assistant'" class="whitespace-pre-wrap break-words">{{ msg.content }}<span v-if="isStreaming && idx === messages.length - 1" class="inline-block w-2 h-4 bg-blue-500 ml-1 animate-pulse translate-y-1"></span></p>
                            <p v-else class="whitespace-pre-wrap break-words">{{ msg.content }}</p>
                        </div>
                    </div>
                    <div v-if="isStreaming && messages[messages.length-1]?.role === 'assistant' && !messages[messages.length-1].content" class="flex justify-start">
                        <div class="bg-white border border-slate-200 rounded-2xl rounded-bl-sm px-4 py-3 flex items-center gap-1.5">
                            <span class="size-2 bg-slate-300 rounded-full animate-bounce [animation-delay:-0.3s]"></span>
                            <span class="size-2 bg-slate-300 rounded-full animate-bounce [animation-delay:-0.15s]"></span>
                            <span class="size-2 bg-slate-300 rounded-full animate-bounce"></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Error -->
            <div v-if="error" class="px-3 py-2 bg-red-50 border-t border-red-100">
                <p class="text-xs text-red-600 flex gap-1.5 items-center">
                    <i class="fa-solid fa-triangle-exclamation text-[11px]"></i>
                    {{ error }}
                </p>
            </div>

            <!-- Input -->
            <div class="p-3 border-t border-slate-200 bg-white">
                <div class="flex items-end gap-2">
                    <div class="flex-1 relative">
                        <textarea
                            v-model="input"
                            @keydown="handleKeydown"
                            :disabled="isStreaming"
                            placeholder="Napisz wiadomość..."
                            rows="1"
                            class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 pr-10 text-sm text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-blue-300 focus:ring-4 focus:ring-blue-50 outline-none transition disabled:opacity-50 max-h-24"
                            @input="(e) => { e.target.style.height = 'auto'; e.target.style.height = Math.min(e.target.scrollHeight, 96) + 'px'; }"
                        ></textarea>
                        <span class="absolute right-3 bottom-3 text-[10px] text-slate-400 select-none">{{ input.length }}/2000</span>
                    </div>
                    <button
                        @click="sendMessage"
                        :disabled="!input.trim() || isStreaming"
                        class="shrink-0 size-11 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-sm hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition"
                    >
                        <i v-if="!isStreaming" class="fa-solid fa-paper-plane text-sm"></i>
                        <i v-else class="fa-solid fa-spinner fa-spin text-sm"></i>
                    </button>
                </div>
                <p class="mt-2 text-[10px] leading-3 text-slate-400 flex gap-1.5 items-start">
                    <i class="fa-solid fa-shield-halved mt-0.5 shrink-0 text-[10px]"></i>
                    <span>Rozmowa jest prywatna. Asystent korzysta z Twoich danych medycznych w aplikacji, aby lepiej pomóc — nigdy nie udostępnia ich poza czat.</span>
                </p>
            </div>
        </div>

        <!-- Floating button -->
        <button
            @click="toggle"
            class="pointer-events-auto mr-4 mb-4 sm:mr-6 sm:mb-6 size-14 rounded-full bg-blue-600 text-white shadow-xl shadow-blue-600/20 flex items-center justify-center hover:bg-blue-700 hover:scale-105 active:scale-95 transition-all border-4 border-white"
            aria-label="Otwórz czat ze specjalistą"
        >
            <i v-if="!isOpen" class="fa-solid fa-comments text-xl"></i>
            <i v-else class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>
</template>
