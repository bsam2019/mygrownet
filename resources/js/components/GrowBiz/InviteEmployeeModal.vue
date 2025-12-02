<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { 
    EnvelopeIcon, 
    QrCodeIcon, 
    ClipboardDocumentIcon,
    CheckIcon,
    XMarkIcon 
} from '@heroicons/vue/24/outline';

interface Props {
    show: boolean;
    employeeId: number;
    employeeName: string;
    employeeEmail?: string | null;
}

const props = defineProps<Props>();
const emit = defineEmits(['close', 'invited']);

const page = usePage();
const activeTab = ref<'email' | 'code'>('email');
const generatedCode = ref<string | null>(null);
const codeCopied = ref(false);
const isGenerating = ref(false);

// Watch for flash data changes (code comes back via flash)
watch(() => (page.props as any).flash?.invitation_code, (code) => {
    if (code) {
        generatedCode.value = code;
        isGenerating.value = false;
    }
}, { immediate: true });

// Reset state when modal closes
watch(() => props.show, (isOpen) => {
    if (!isOpen) {
        generatedCode.value = null;
        codeCopied.value = false;
        isGenerating.value = false;
    }
});

const emailForm = useForm({
    email: props.employeeEmail || '',
});

const sendEmailInvitation = () => {
    emailForm.post(route('growbiz.employees.invite.email', props.employeeId), {
        preserveScroll: true,
        onSuccess: () => {
            emit('invited', 'email');
            emit('close');
        },
    });
};

const generateCode = () => {
    isGenerating.value = true;
    const form = useForm({});
    form.post(route('growbiz.employees.invite.code', props.employeeId), {
        preserveScroll: true,
        onSuccess: (response: any) => {
            // Code will come via flash data, watched above
            // Also check direct response in case flash is in response
            const flash = response.props?.flash;
            if (flash?.invitation_code) {
                generatedCode.value = flash.invitation_code;
            }
            isGenerating.value = false;
        },
        onError: () => {
            isGenerating.value = false;
        },
    });
};

const copyCode = async () => {
    if (generatedCode.value) {
        await navigator.clipboard.writeText(generatedCode.value);
        codeCopied.value = true;
        setTimeout(() => codeCopied.value = false, 2000);
    }
};

const formatCode = (code: string) => {
    // Format as XXX-XXX for readability
    return code.slice(0, 3) + '-' + code.slice(3);
};
</script>

<template>
    <Teleport to="body">
        <div 
            v-if="show" 
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
            @click.self="$emit('close')"
        >
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden">
                <!-- Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        Invite {{ employeeName }}
                    </h3>
                    <button 
                        @click="$emit('close')"
                        class="p-2 hover:bg-gray-100 rounded-full"
                        aria-label="Close modal"
                    >
                        <XMarkIcon class="w-5 h-5 text-gray-500" aria-hidden="true" />
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex border-b">
                    <button
                        @click="activeTab = 'email'"
                        :class="[
                            'flex-1 py-3 px-4 text-sm font-medium flex items-center justify-center gap-2',
                            activeTab === 'email' 
                                ? 'text-blue-600 border-b-2 border-blue-600' 
                                : 'text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        <EnvelopeIcon class="w-5 h-5" aria-hidden="true" />
                        Email Invite
                    </button>
                    <button
                        @click="activeTab = 'code'"
                        :class="[
                            'flex-1 py-3 px-4 text-sm font-medium flex items-center justify-center gap-2',
                            activeTab === 'code' 
                                ? 'text-blue-600 border-b-2 border-blue-600' 
                                : 'text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        <QrCodeIcon class="w-5 h-5" aria-hidden="true" />
                        Invite Code
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <!-- Email Tab -->
                    <div v-if="activeTab === 'email'">
                        <p class="text-sm text-gray-600 mb-4">
                            Send an email invitation with a unique link. The employee can click the link to join your team.
                        </p>
                        <form @submit.prevent="sendEmailInvitation">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Email Address
                                </label>
                                <input
                                    v-model="emailForm.email"
                                    type="email"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="employee@example.com"
                                />
                                <p v-if="emailForm.errors.email" class="mt-1 text-sm text-red-600">
                                    {{ emailForm.errors.email }}
                                </p>
                            </div>
                            <button
                                type="submit"
                                :disabled="emailForm.processing"
                                class="w-full py-2.5 px-4 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2"
                            >
                                <EnvelopeIcon class="w-5 h-5" aria-hidden="true" />
                                {{ emailForm.processing ? 'Sending...' : 'Send Invitation' }}
                            </button>
                        </form>
                    </div>

                    <!-- Code Tab -->
                    <div v-else>
                        <p class="text-sm text-gray-600 mb-4">
                            Generate a 6-character code that the employee can enter after registering on the platform.
                        </p>
                        
                        <div v-if="!generatedCode" class="text-center">
                            <button
                                @click="generateCode"
                                :disabled="isGenerating"
                                class="py-2.5 px-6 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2 mx-auto"
                            >
                                <QrCodeIcon v-if="!isGenerating" class="w-5 h-5" aria-hidden="true" />
                                <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ isGenerating ? 'Generating...' : 'Generate Code' }}
                            </button>
                        </div>

                        <div v-else class="text-center">
                            <div class="bg-gray-50 rounded-xl p-6 mb-4">
                                <p class="text-sm text-gray-500 mb-2">Invitation Code</p>
                                <p class="text-3xl font-mono font-bold text-gray-900 tracking-wider">
                                    {{ formatCode(generatedCode) }}
                                </p>
                            </div>
                            
                            <button
                                @click="copyCode"
                                class="py-2 px-4 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 flex items-center justify-center gap-2 mx-auto"
                            >
                                <component 
                                    :is="codeCopied ? CheckIcon : ClipboardDocumentIcon" 
                                    class="w-4 h-4" 
                                    aria-hidden="true"
                                />
                                {{ codeCopied ? 'Copied!' : 'Copy Code' }}
                            </button>

                            <p class="text-xs text-gray-500 mt-4">
                                Share this code with {{ employeeName }}. They can enter it at 
                                <span class="font-medium">growbiz/invitation/code</span> after logging in.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
