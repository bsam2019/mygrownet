<template>
    <div v-if="visible" class="fixed bottom-6 right-6 z-50">
        <!-- Chat Button -->
        <button
            v-if="!open"
            @click="open = true"
            class="w-14 h-14 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition-all flex items-center justify-center hover:scale-105"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        </button>

        <!-- Chat Window -->
        <div v-else class="bg-white rounded-2xl shadow-2xl border border-gray-200 w-80 sm:w-96 flex flex-col overflow-hidden animate-slide-up">
            <!-- Header -->
            <div class="bg-blue-600 text-white px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <span class="text-sm font-medium">{{ siteName || 'Chat' }}</span>
                </div>
                <button @click="open = false" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Messages -->
            <div ref="messagesContainer" class="flex-1 p-4 space-y-3 overflow-y-auto max-h-96 bg-gray-50">
                <div v-for="(msg, i) in messages" :key="i" :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user'
                        ? 'bg-blue-600 text-white rounded-2xl rounded-br-sm px-3 py-2 max-w-[80%]'
                        : 'bg-white text-gray-800 rounded-2xl rounded-bl-sm px-3 py-2 max-w-[80%] shadow-sm border border-gray-100'">
                        <p class="text-sm whitespace-pre-wrap">{{ msg.content }}</p>
                    </div>
                </div>

                <!-- Typing indicator -->
                <div v-if="typing" class="flex justify-start">
                    <div class="bg-white rounded-2xl rounded-bl-sm px-4 py-3 shadow-sm border border-gray-100">
                        <div class="flex gap-1">
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms" />
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms" />
                            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms" />
                        </div>
                    </div>
                </div>

                <!-- Lead capture -->
                <div v-if="showLeadCapture" class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 space-y-2">
                    <p class="text-xs text-yellow-800 font-medium">Leave your email and we'll get back to you</p>
                    <div class="flex gap-2">
                        <input
                            v-model="leadEmail"
                            type="email"
                            placeholder="your@email.com"
                            class="flex-1 px-2 py-1.5 text-xs border border-yellow-300 rounded-lg focus:ring-1 focus:ring-yellow-500"
                            @keyup.enter="submitLead"
                        />
                        <button @click="submitLead" class="px-3 py-1.5 bg-yellow-600 text-white text-xs font-medium rounded-lg hover:bg-yellow-700">
                            Send
                        </button>
                    </div>
                </div>
            </div>

            <!-- Input -->
            <div v-if="!showLeadCapture" class="border-t border-gray-200 p-3 flex gap-2">
                <input
                    v-model="input"
                    type="text"
                    placeholder="Ask a question..."
                    class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    @keyup.enter="sendMessage"
                    :disabled="typing"
                />
                <button
                    @click="sendMessage"
                    :disabled="!input.trim() || typing"
                    class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, nextTick } from 'vue'

const props = defineProps<{
    siteId: number
    siteName?: string
    apiBase?: string
    enabled?: boolean
}>()

const baseUrl = props.apiBase || window.location.origin

const visible = ref(props.enabled ?? true)
const open = ref(false)
const input = ref('')
const typing = ref(false)
const showLeadCapture = ref(false)
const leadEmail = ref('')
const messages = ref<Array<{ role: string; content: string }>>([
    { role: 'assistant', content: 'Hi! How can I help you today?' },
])
const messagesContainer = ref<HTMLElement | null>(null)

async function sendMessage() {
    if (!input.value.trim() || typing.value) return

    const question = input.value
    messages.value.push({ role: 'user', content: question })
    input.value = ''
    typing.value = true
    showLeadCapture.value = false
    scrollToBottom()

    try {
        const res = await fetch(`${baseUrl}/gb-chatbot/${props.siteId}/ask`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ question }),
        })
        const data = await res.json()

        if (data.has_answer) {
            messages.value.push({ role: 'assistant', content: data.answer })
        } else {
            messages.value.push({ role: 'assistant', content: data.answer })
            showLeadCapture.value = data.capture_lead || false
        }
    } catch {
        messages.value.push({ role: 'assistant', content: "Sorry, I'm having trouble. Please try again later." })
    } finally {
        typing.value = false
        scrollToBottom()
    }
}

async function submitLead() {
    if (!leadEmail.value.trim()) return

    try {
        await fetch(`${baseUrl}/gb-chatbot/${props.siteId}/capture-lead`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: leadEmail.value }),
        })
        messages.value.push({ role: 'assistant', content: "Thanks! We'll get back to you soon." })
        showLeadCapture.value = false
        leadEmail.value = ''
    } catch {
        // silent fail
    }
}

function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
    })
}

onMounted(() => {
    // Check if chatbot is enabled via the settings endpoint
    // For simplicity, it's enabled by default
})
</script>

<style scoped>
.animate-slide-up {
    animation: slideUp 0.3s ease-out;
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
