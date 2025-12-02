<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import { QrCodeIcon, CheckCircleIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline';

interface Props {
    isLoggedIn: boolean;
}

defineProps<Props>();

const page = usePage();
const codeInputs = ref(['', '', '', '', '', '']);
const inputRefs = ref<HTMLInputElement[]>([]);

const form = useForm({
    code: '',
});

// Get flash error message
const flashError = computed(() => (page.props as any).flash?.error);

const handleInput = (index: number, event: Event) => {
    const input = event.target as HTMLInputElement;
    const value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    
    codeInputs.value[index] = value.charAt(0) || '';
    
    // Auto-advance to next input
    if (value && index < 5) {
        inputRefs.value[index + 1]?.focus();
    }
    
    // Update form code
    form.code = codeInputs.value.join('');
};

const handleKeydown = (index: number, event: KeyboardEvent) => {
    if (event.key === 'Backspace' && !codeInputs.value[index] && index > 0) {
        inputRefs.value[index - 1]?.focus();
    }
};

const handlePaste = (event: ClipboardEvent) => {
    event.preventDefault();
    const pastedText = event.clipboardData?.getData('text').toUpperCase().replace(/[^A-Z0-9]/g, '') || '';
    
    for (let i = 0; i < 6 && i < pastedText.length; i++) {
        codeInputs.value[i] = pastedText[i];
    }
    
    form.code = codeInputs.value.join('');
    
    // Focus last filled input or first empty
    const lastIndex = Math.min(pastedText.length, 5);
    inputRefs.value[lastIndex]?.focus();
};

const submitCode = () => {
    form.post(route('growbiz.invitation.code.submit'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Enter Invitation Code" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-8 text-center text-white">
                <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                    <QrCodeIcon class="w-8 h-8" aria-hidden="true" />
                </div>
                <h1 class="text-2xl font-bold mb-2">Enter Invitation Code</h1>
                <p class="text-blue-100">Join your team on GrowBiz</p>
            </div>

            <!-- Content -->
            <div class="p-6">
                <p class="text-gray-600 text-center mb-6">
                    Enter the 6-character code provided by your employer.
                </p>

                <form @submit.prevent="submitCode">
                    <!-- Code Input -->
                    <div class="flex justify-center gap-2 mb-6" @paste="handlePaste">
                        <input
                            v-for="(_, index) in 6"
                            :key="index"
                            :ref="el => inputRefs[index] = el as HTMLInputElement"
                            v-model="codeInputs[index]"
                            type="text"
                            maxlength="1"
                            class="w-12 h-14 text-center text-2xl font-mono font-bold border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 uppercase"
                            @input="handleInput(index, $event)"
                            @keydown="handleKeydown(index, $event)"
                        />
                    </div>

                    <!-- Flash error message -->
                    <div v-if="flashError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-center gap-2">
                        <ExclamationCircleIcon class="w-5 h-5 text-red-500 flex-shrink-0" aria-hidden="true" />
                        <p class="text-sm text-red-600">{{ flashError }}</p>
                    </div>

                    <p v-if="form.errors.code" class="text-sm text-red-600 text-center mb-4">
                        {{ form.errors.code }}
                    </p>

                    <div v-if="isLoggedIn">
                        <button
                            type="submit"
                            :disabled="form.processing || form.code.length !== 6"
                            class="w-full py-3 px-4 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        >
                            <CheckCircleIcon class="w-5 h-5" aria-hidden="true" />
                            {{ form.processing ? 'Verifying...' : 'Join Team' }}
                        </button>
                    </div>
                    <div v-else class="space-y-3">
                        <p class="text-sm text-gray-500 text-center">
                            Please log in or create an account to continue.
                        </p>
                        <Link
                            :href="route('login')"
                            class="block w-full py-3 px-4 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 text-center"
                        >
                            Log In
                        </Link>
                        <Link
                            :href="route('register')"
                            class="block w-full py-3 px-4 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 text-center"
                        >
                            Create Account
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
