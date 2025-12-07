<script setup lang="ts">
import { ref, computed, nextTick } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    SparklesIcon,
    PaperAirplaneIcon,
    LightBulbIcon,
    ArrowPathIcon,
    ChartBarIcon,
    UserGroupIcon,
    MegaphoneIcon,
    CogIcon,
} from '@heroicons/vue/24/outline';

interface ChatMessage {
    id: number;
    type: string;
    prompt?: string;
    response?: string;
    created_at: string;
}

interface Insight {
    total_products: number;
    total_customers: number;
    total_sales_30d: number;
    sales_count_30d: number;
    posts_30d: number;
    published_posts_30d: number;
    active_campaigns: number;
    top_product: string | null;
    new_customers_30d: number;
    has_integrations: boolean;
}

interface Recommendation {
    type: string;
    priority: string;
    title: string;
    description: string;
    action: string;
    action_route: string;
}

interface Props {
    chatHistory: ChatMessage[];
    insights: Insight;
    recommendations: Recommendation[];
}

const props = defineProps<Props>();

const message = ref('');
const isLoading = ref(false);
const chatMessages = ref<Array<{ role: 'user' | 'assistant'; content: string; timestamp: string }>>([]);
const chatContainer = ref<HTMLElement | null>(null);

// Initialize chat from history
props.chatHistory?.forEach(chat => {
    if (chat.prompt) {
        chatMessages.value.push({ role: 'user', content: chat.prompt, timestamp: chat.created_at });
    }
    if (chat.response) {
        chatMessages.value.push({ role: 'assistant', content: chat.response, timestamp: chat.created_at });
    }
});

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
};

const sendMessage = async () => {
    if (!message.value.trim() || isLoading.value) return;

    const userMessage = message.value.trim();
    chatMessages.value.push({ role: 'user', content: userMessage, timestamp: new Date().toISOString() });
    message.value = '';
    isLoading.value = true;
    scrollToBottom();

    try {
        const response = await fetch(route('bizboost.advisor.chat'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ message: userMessage }),
        });

        const data = await response.json();
        
        if (data.error) {
            chatMessages.value.push({ 
                role: 'assistant', 
                content: data.error, 
                timestamp: new Date().toISOString() 
            });
        } else {
            chatMessages.value.push({ 
                role: 'assistant', 
                content: data.message, 
                timestamp: data.timestamp 
            });
        }
    } catch (error) {
        chatMessages.value.push({ 
            role: 'assistant', 
            content: 'Sorry, I encountered an error. Please try again.', 
            timestamp: new Date().toISOString() 
        });
    } finally {
        isLoading.value = false;
        scrollToBottom();
    }
};

const quickPrompts = [
    { text: 'How can I increase sales?', icon: ChartBarIcon },
    { text: 'Marketing tips for my business', icon: MegaphoneIcon },
    { text: 'How to get more customers?', icon: UserGroupIcon },
    { text: 'What can you help me with?', icon: LightBulbIcon },
];

const priorityColors: Record<string, string> = {
    high: 'bg-red-100 text-red-700 border-red-200',
    medium: 'bg-amber-100 text-amber-700 border-amber-200',
    low: 'bg-blue-100 text-blue-700 border-blue-200',
};

const typeIcons: Record<string, any> = {
    marketing: MegaphoneIcon,
    growth: ChartBarIcon,
    setup: CogIcon,
};
</script>

<template>
    <Head title="AI Business Advisor - BizBoost" />
    <BizBoostLayout title="AI Business Advisor">
        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Chat Section -->
            <div class="lg:col-span-2 flex flex-col bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden" style="height: 600px;">
                <!-- Chat Header -->
                <div class="flex items-center gap-3 p-4 border-b border-gray-200 bg-gradient-to-r from-violet-600 to-violet-700">
                    <div class="p-2 bg-white/20 rounded-lg">
                        <SparklesIcon class="h-6 w-6 text-white" aria-hidden="true" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-white">AI Business Advisor</h2>
                        <p class="text-sm text-violet-200">Ask me anything about growing your business</p>
                    </div>
                </div>

                <!-- Chat Messages -->
                <div ref="chatContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <!-- Welcome message if no history -->
                    <div v-if="chatMessages.length === 0" class="text-center py-8">
                        <SparklesIcon class="h-12 w-12 text-violet-300 mx-auto mb-4" aria-hidden="true" />
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Welcome to your AI Advisor!</h3>
                        <p class="text-sm text-gray-500 mb-6">I can help you with sales strategies, marketing tips, customer engagement, and more.</p>
                        
                        <!-- Quick Prompts -->
                        <div class="grid grid-cols-2 gap-2 max-w-md mx-auto">
                            <button
                                v-for="prompt in quickPrompts"
                                :key="prompt.text"
                                @click="message = prompt.text; sendMessage()"
                                class="flex items-center gap-2 p-3 text-left text-sm bg-violet-50 hover:bg-violet-100 rounded-lg transition-colors"
                            >
                                <component :is="prompt.icon" class="h-4 w-4 text-violet-600" aria-hidden="true" />
                                <span class="text-gray-700">{{ prompt.text }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div
                        v-for="(msg, index) in chatMessages"
                        :key="index"
                        :class="[
                            'flex',
                            msg.role === 'user' ? 'justify-end' : 'justify-start'
                        ]"
                    >
                        <div
                            :class="[
                                'max-w-[80%] rounded-2xl px-4 py-3',
                                msg.role === 'user' 
                                    ? 'bg-violet-600 text-white rounded-br-md' 
                                    : 'bg-gray-100 text-gray-900 rounded-bl-md'
                            ]"
                        >
                            <p class="text-sm whitespace-pre-wrap">{{ msg.content }}</p>
                        </div>
                    </div>

                    <!-- Loading indicator -->
                    <div v-if="isLoading" class="flex justify-start">
                        <div class="bg-gray-100 rounded-2xl rounded-bl-md px-4 py-3">
                            <div class="flex items-center gap-2">
                                <ArrowPathIcon class="h-4 w-4 text-violet-600 animate-spin" aria-hidden="true" />
                                <span class="text-sm text-gray-500">Thinking...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="p-4 border-t border-gray-200">
                    <form @submit.prevent="sendMessage" class="flex gap-2">
                        <input
                            v-model="message"
                            type="text"
                            placeholder="Ask me anything about your business..."
                            class="flex-1 rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 text-sm"
                            :disabled="isLoading"
                        />
                        <button
                            type="submit"
                            :disabled="!message.trim() || isLoading"
                            class="px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <PaperAirplaneIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Business Insights -->
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Your Business Snapshot</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Products</span>
                            <span class="font-medium">{{ insights.total_products }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Customers</span>
                            <span class="font-medium">{{ insights.total_customers }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Sales (30 days)</span>
                            <span class="font-medium">K{{ insights.total_sales_30d?.toLocaleString() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Posts (30 days)</span>
                            <span class="font-medium">{{ insights.posts_30d }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Active Campaigns</span>
                            <span class="font-medium">{{ insights.active_campaigns }}</span>
                        </div>
                        <div v-if="insights.top_product" class="flex justify-between">
                            <span class="text-gray-500">Top Product</span>
                            <span class="font-medium truncate max-w-[120px]">{{ insights.top_product }}</span>
                        </div>
                    </div>
                </div>

                <!-- Recommendations -->
                <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 p-4">
                    <div class="flex items-center gap-2 mb-3">
                        <LightBulbIcon class="h-5 w-5 text-amber-500" aria-hidden="true" />
                        <h3 class="text-sm font-semibold text-gray-900">Recommendations</h3>
                    </div>
                    <div v-if="recommendations?.length" class="space-y-3">
                        <div
                            v-for="rec in recommendations.slice(0, 4)"
                            :key="rec.title"
                            :class="[
                                'p-3 rounded-lg border',
                                priorityColors[rec.priority] || 'bg-gray-50 border-gray-200'
                            ]"
                        >
                            <div class="flex items-start gap-2">
                                <component 
                                    :is="typeIcons[rec.type] || LightBulbIcon" 
                                    class="h-4 w-4 mt-0.5 flex-shrink-0" 
                                    aria-hidden="true" 
                                />
                                <div>
                                    <p class="text-sm font-medium">{{ rec.title }}</p>
                                    <p class="text-xs mt-1 opacity-80">{{ rec.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-gray-500">No recommendations at this time.</p>
                </div>
            </div>
        </div>
    </BizBoostLayout>
</template>
