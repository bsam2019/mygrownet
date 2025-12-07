<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import BizBoostLayout from '@/Layouts/BizBoostLayout.vue';
import {
    KeyIcon,
    PlusIcon,
    TrashIcon,
    ClipboardDocumentIcon,
    EyeIcon,
    EyeSlashIcon,
} from '@heroicons/vue/24/outline';

interface ApiToken {
    id: number;
    name: string;
    token_preview: string;
    abilities: string[];
    last_used_at: string | null;
    created_at: string;
}

interface Props {
    tokens: ApiToken[];
    availableAbilities: string[];
}

const props = defineProps<Props>();
const showCreateModal = ref(false);
const newToken = ref<string | null>(null);
const showToken = ref(false);

const form = useForm({
    name: '',
    abilities: [] as string[],
});

const createToken = () => {
    form.post('/bizboost/api/tokens', {
        preserveScroll: true,
        onSuccess: (page: any) => {
            newToken.value = page.props.flash?.token || null;
            form.reset();
            showCreateModal.value = false;
        },
    });
};

const deleteToken = (id: number) => {
    if (confirm('Are you sure you want to revoke this token?')) {
        useForm({}).delete(`/bizboost/api/tokens/${id}`, { preserveScroll: true });
    }
};

const copyToken = () => {
    if (newToken.value) {
        navigator.clipboard.writeText(newToken.value);
    }
};
</script>

<template>
    <Head title="API Tokens - BizBoost" />
    <BizBoostLayout title="API Tokens">
        <div class="space-y-6">
            <!-- New Token Alert -->
            <div v-if="newToken" class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <KeyIcon class="h-5 w-5 text-emerald-600 mt-0.5" aria-hidden="true" />
                    <div class="flex-1">
                        <h3 class="font-medium text-emerald-800">Token Created Successfully</h3>
                        <p class="text-sm text-emerald-700 mt-1">
                            Copy this token now. You won't be able to see it again.
                        </p>
                        <div class="mt-3 flex items-center gap-2">
                            <code class="flex-1 bg-white px-3 py-2 rounded border text-sm font-mono">
                                {{ showToken ? newToken : '••••••••••••••••••••••••' }}
                            </code>
                            <button
                                @click="showToken = !showToken"
                                class="p-2 text-gray-500 hover:text-gray-700"
                                :aria-label="showToken ? 'Hide token' : 'Show token'"
                            >
                                <EyeSlashIcon v-if="showToken" class="h-5 w-5" aria-hidden="true" />
                                <EyeIcon v-else class="h-5 w-5" aria-hidden="true" />
                            </button>
                            <button
                                @click="copyToken"
                                class="p-2 text-gray-500 hover:text-gray-700"
                                aria-label="Copy token"
                            >
                                <ClipboardDocumentIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">API Tokens</h2>
                    <p class="text-sm text-gray-500">Manage API tokens for external integrations</p>
                </div>
                <button
                    @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 text-white rounded-lg text-sm font-medium hover:bg-violet-700"
                >
                    <PlusIcon class="h-4 w-4" aria-hidden="true" />
                    Create Token
                </button>
            </div>

            <!-- Tokens List -->
            <div class="bg-white rounded-xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
                <div v-if="tokens.length === 0" class="p-8 text-center">
                    <KeyIcon class="h-12 w-12 text-gray-300 mx-auto mb-3" aria-hidden="true" />
                    <p class="text-gray-500">No API tokens created yet</p>
                </div>
                <ul v-else class="divide-y divide-gray-200">
                    <li v-for="token in tokens" :key="token.id" class="p-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ token.name }}</p>
                            <p class="text-sm text-gray-500">{{ token.token_preview }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span
                                    v-for="ability in token.abilities"
                                    :key="ability"
                                    class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded"
                                >
                                    {{ ability }}
                                </span>
                            </div>
                        </div>
                        <button
                            @click="deleteToken(token.id)"
                            class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg"
                            aria-label="Delete token"
                        >
                            <TrashIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Create Modal -->
        <Teleport to="body">
            <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Create API Token</h3>
                    <form @submit.prevent="createToken" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Token Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500"
                                placeholder="My Integration"
                                required
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Permissions</label>
                            <div class="space-y-2">
                                <label
                                    v-for="ability in availableAbilities"
                                    :key="ability"
                                    class="flex items-center gap-2"
                                >
                                    <input
                                        type="checkbox"
                                        :value="ability"
                                        v-model="form.abilities"
                                        class="rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                                    />
                                    <span class="text-sm text-gray-700">{{ ability }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-4">
                            <button
                                type="button"
                                @click="showCreateModal = false"
                                class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 disabled:opacity-50"
                            >
                                Create Token
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </BizBoostLayout>
</template>
